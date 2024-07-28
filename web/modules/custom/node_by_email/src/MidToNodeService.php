<?php

namespace Drupal\node_by_email;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class MidToNodeService.
 */
class MidToNodeService {

  /**
   * Drupal\node_by_email\IMAPService definition.
   *
   * @var \Drupal\node_by_email\IMAPService
   */
  protected $nodeByEmailImapConnection;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempstorePrivate;

  /**
   * Constructs a new MidToNodeService object.
   */
  public function __construct(IMAPService $node_by_email_imap_connection, AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_manager, ConfigFactoryInterface $config_factory) {
    $this->nodeByEmailImapConnection = $node_by_email_imap_connection;
    $this->currentUser = $current_user;
    $this->entityManager = $entity_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Create email from mid.
   *
   * @param string $mid
   *   Email id.
   */
  public function createNodeFromMid($mid = NULL) {
    if ($mid) {
      $nodeByEmailConfig = $this->configFactory->getEditable('node_by_email.nodebyemailconfig');
      $emailHeader = $this->nodeByEmailImapConnection->getEmailHeader($mid, "html");
      $subject = $emailHeader['subject'];
      $body = $emailHeader['body'];
      $nodeTypes = array_keys($nodeByEmailConfig->get("node_types"));

      $uid = $this->getUserForNode($emailHeader);
      if (!empty($nodeTypes)) {
        foreach ($nodeTypes as $nodeType) {
          $node = Node::create([
            'type' => $nodeType,
            'title' => $subject,
            'body' => [
              'value' => $body,
            ],
            // 'uid' => $nodeByEmailConfig->get("author_uid"),
            'uid' => $uid,
          ]);
          if ($nodeByEmailConfig->get("publishing_option")) {
            $node->setPublished();
          }
          else {
            $node->setUnpublished();
          }
          $node->save();
        }
      }
      $this->nodeByEmailImapConnection->setUnseenMail($mid);
    }
  }

  public function getUserForNode($emailHeader) {

    // Get the role storage service.
    $roleStorage = \Drupal::entityTypeManager()->getStorage('user_role');

    // Define the machine name of the role you want to check.
    $roleMachineNameToCheck = 'data_source';

    // Load the role by machine name.
    $role = $roleStorage->load($roleMachineNameToCheck);
    $uid = 0;
    // Used to check and create new User , if not already exists.
    if(count($emailHeader) > 0 && ($emailHeader['from'] !== null)) {
      $uid = \Drupal::entityQuery('user')
          ->condition('mail', $emailHeader['from'])
          ->accessCheck(false)
          ->execute();

      if(empty($uid)) {
        $new_user = User::create([
          'mail' => $emailHeader['from'],
          'name' => $emailHeader['from'],
          'pass' => rand(0,9).rand(0,9),
          'created' => time(),
          'status' => 1
        ]);
        if($role) {
          $new_user->addRole($role);
        }
        $new_user->save();
        $uid = $new_user->id();
      }
    }

    return $uid;
  }

}


