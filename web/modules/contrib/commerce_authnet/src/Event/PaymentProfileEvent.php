<?php

namespace Drupal\commerce_authnet\Event;

use CommerceGuys\AuthNet\DataTypes\PaymentProfile;
use Drupal\Component\EventDispatcher\Event;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Defines the payment profile request event.
 *
 * @see \Drupal\commerce_authnet\Event\AuthorizeNetEvents
 */
class PaymentProfileEvent extends Event {

  /**
   * The billing profile.
   *
   * @var \Drupal\profile\Entity\ProfileInterface
   */
  protected $billingProfile;

  /**
   * The payment profile.
   *
   * @var \CommerceGuys\AuthNet\DataTypes\PaymentProfile
   */
  protected $paymentProfile;

  /**
   * Constructs a new PaymentProfileEvent.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $billing_profile
   *   The order to be transacted upon.
   * @param \CommerceGuys\AuthNet\DataTypes\PaymentProfile $payment_profile
   *   The payment profile.
   */
  public function __construct(ProfileInterface $billing_profile, PaymentProfile $payment_profile) {
    $this->billingProfile = $billing_profile;
    $this->paymentProfile = $payment_profile;
  }

  /**
   * Gets the billing profile.
   *
   * @return \Drupal\profile\Entity\ProfileInterface
   *   The billing profile.
   */
  public function getBillingProfile() {
    return $this->billingProfile;
  }

  /**
   * Gets the payment profile.
   *
   * @return \CommerceGuys\AuthNet\DataTypes\PaymentProfile
   *   The payment profile.
   */
  public function getPaymentProfile() {
    return $this->paymentProfile;
  }

}
