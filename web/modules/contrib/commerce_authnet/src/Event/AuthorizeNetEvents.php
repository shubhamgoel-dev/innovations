<?php

namespace Drupal\commerce_authnet\Event;

/**
 * Defines events for the AuthNet module.
 */
final class AuthorizeNetEvents {

  /**
   * Name of the event fired when creating a transaction request.
   *
   * Allows alteration of the transaction request object e.g. to alter fields
   * going to the Authorize.net payload.
   *
   * @Event
   *
   * @see \Drupal\commerce_authnet\Event\TransactionRequestEvent
   */
  const CREATE_TRANSACTION_REQUEST = 'commerce_authnet.transaction_request.create';

  /**
   * Name of the event fired when an Authorize.net refunds a transaction.
   *
   * @Event
   *
   * @see \Drupal\commerce_authnet\Event\TransactionRequestEvent
   */
  const REFUND_TRANSACTION_REQUEST = 'commerce_authnet.transaction_request.refund';

  /**
   * Name of the event fired when an Authorize.net creates a payment profile.
   *
   * @Event
   *
   * @see \Drupal\commerce_authnet\Event\PaymentProfileEvent
   */
  const CREATE_PAYMENT_PROFILE = 'commerce_authnet.payment_profile.create';

  /**
   * Name of the event fired when an Authorize.net updates a payment profile.
   *
   * @Event
   *
   * @see \Drupal\commerce_authnet\Event\PaymentProfileEvent
   */
  const UPDATE_PAYMENT_PROFILE = 'commerce_authnet.payment_profile.update';

}
