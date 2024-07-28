<?php

namespace Drupal\commerce_authnet\Event;

use CommerceGuys\AuthNet\DataTypes\TransactionRequest;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Defines the create transaction request event.
 *
 * @see \Drupal\commerce_authnet\Event\AuthorizeNetEvents
 */
class TransactionRequestEvent extends Event {

  /**
   * The transaction order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * The transaction request object.
   *
   * @var \CommerceGuys\AuthNet\DataTypes\TransactionRequest
   */
  protected $transactionRequest;

  /**
   * Constructs a new CreateTransactionRequestEvent.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order to be transacted upon.
   * @param \CommerceGuys\AuthNet\DataTypes\TransactionRequest $transaction_request
   *   The transaction request object.
   */
  public function __construct(OrderInterface $order, TransactionRequest $transaction_request) {
    $this->order = $order;
    $this->transactionRequest = $transaction_request;
  }

  /**
   * Gets the order.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   The order.
   */
  public function getOrder() {
    return $this->order;
  }

  /**
   * Gets the transaction request object.
   *
   * @return \CommerceGuys\AuthNet\DataTypes\TransactionRequest
   *   The transaction request.
   */
  public function getTransactionRequest() {
    return $this->transactionRequest;
  }

}
