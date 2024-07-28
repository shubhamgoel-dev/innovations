<?php

namespace Drupal\node_by_email\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class NodeByEmailCommands extends DrushCommands {

  /**
   * This command generates nodes from mid.
   *
   * @command node_by_email:generate_node
   * @aliases nbe-gn
   */
  public function commandName() {
    $emailArray = \Drupal::service('node_by_email.imap_connection')->getUnseenEmailList();
    if (!empty($emailArray)) {
      foreach ($emailArray as $mid) {
        \Drupal::service('node_by_email.mid_to_node')->createNodeFromMid($mid);
        $this->output()->writeln('creating node for mid: ' . $mid);
      }
    }
    else {
      $this->output()->writeln('No unseen email found.');
    }
  }

}
