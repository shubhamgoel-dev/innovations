<?php

namespace Drupal\commerce_authnet\Controller;

use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Verifies JWT in the CCA process.
 *
 * @see https://developer.cardinalcommerce.com/cardinal-cruise-activation.shtml#generatingServerJWTphp
 */
class CcaValidation implements ContainerInjectionInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Constructs a new CcaValidation object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Validates the JWT.
   */
  public function validateJwt() {
    $response_jwt = $this->requestStack->getCurrentRequest()->request->get('responseJwt');

    $gateway_id = $this->requestStack->getCurrentRequest()->request->get('gatewayId');
    /** @var \Drupal\commerce_payment\Entity\PaymentGateway $gateway */
    $gateway = PaymentGateway::load($gateway_id);
    $api_key = $gateway->getPlugin()->getCcaApiKey();

    $configuration = Configuration::forSymmetricSigner(
      new Sha256(),
      InMemory::plainText($api_key)
    );
    // Set validation constraints.
    $constraints = [
      new SignedWith($configuration->signer(), $configuration->signingKey()),
    ];
    $configuration->setValidationConstraints(...$constraints);
    $token = $configuration->parser()->parse($response_jwt);

    $claims = $token->claims()->all();
    $response = [
      'verified' => $configuration->validator()->validate($token, ...$configuration->validationConstraints()),
      'payload' => $claims,
    ];
    return new JsonResponse($response);
  }

}
