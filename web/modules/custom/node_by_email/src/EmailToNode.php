<?php

namespace Drupal\node_by_email;

/**
 * Email To node batch class.
 */
class EmailToNode {

  /**
   * Create node batch method.
   *
   * @param int $mids
   *   Mail id.
   * @param mixed $context
   *   Batch context.
   */
  public static function createNode($mids, &$context) {
    $message = 'Creating Node...';
    $results = [];
    foreach ($mids as $mid) {
      $node = \Drupal::service('node_by_email.mid_to_node')->createNodeFromMid($mid);
      $results[] = $node;
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * Finish callback to batch.
   *
   * @param bool $success
   *   Boolean value.
   * @param mixed $results
   *   Results sets.
   * @param mixed $operations
   *   Operation sets.
   */
  public static function creatNodeFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results), 'One node processed.', '@count node processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }

}
