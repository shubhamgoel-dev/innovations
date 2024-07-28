<?php

namespace Drupal\node_by_email\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node_by_email\IMAPService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\Entity\User;

/**
 * Class UnseenEmailController.
 */
class UnseenEmailForm extends FormBase {

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
   * Service to create node from mid.
   *
   * @var \Drupal\node_by_email\MidToNodeService
   */
  protected $midToNodeService;

  public function __construct(
    ConfigFactoryInterface $configManager,
    IMAPService $imap
    ) {
      
      $this->nodeByEmailImapConnection = $imap;
 
    }
  
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('config.factory'),
        $container->get('node_by_email.imap_connection')
      );
    }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_by_email_unseen_email';
  }

  /**
   * Unseen email list select table.
   *
   * @return string
   *   Return Hello string.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $emailTableRows = [];
    
    if (!\Drupal::config('node_by_email.nodebyemailconfig')->get('imap_connected')) {
      return $form['not_conncted'] = [
        '#type' => 'item_list',
        '#markup' => $this->t("IMAP Connection is wrong!"),
      ];
    }
    $emailArray = $this->nodeByEmailImapConnection->getUnseenEmailList();
    if (!$this->nodeByEmailImapConnection->getImapConnection()) {
      return $form['not_conncted'] = [
        '#type' => 'item_list',
        '#markup' => $this->t("Please configure IMAP Settings."),
      ];
    }
    $tableHeader = [
      'sr_no' => $this->t("Sr. No"),
      'mailid' => $this->t("Mail ID"),
      'subject' => $this->t("Subject"),
      'datetime' => $this->t("Date Time"),
      'body' => $this->t("Body"),
    ];

    if (!empty($emailArray)) {
      foreach ($emailArray as $key => $mid) {
        $emailHeader = $this->nodeByEmailImapConnection->getEmailHeader($mid);

        $emailTableRows[$mid] = [
          'sr_no' => $key + 1,
          'mailid' => $emailHeader['from'],
          'subject' => $emailHeader['subject'],
          'datetime' => $emailHeader['datetime'],
          'body' => $emailHeader['body'],
        ];
      }
    }

    $form['mid'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('Unseen Email List'),
      '#header' => $tableHeader,
      '#options' => $emailTableRows,
      '#empty' => $this->t("No Unseen Email Found."),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create node from mail and mark it unseen'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = array_filter($form_state->getValues()['mid']);
    $batch = [
      'title' => $this->t('Creating Node...'),
      'operations' => [
        [
          '\Drupal\node_by_email\EmailToNode::createNode',
          [array_keys($values)],
        ],
      ],
      'finished' => '\Drupal\node_by_email\EmailToNode::creatNodeFinishedCallback',
    ];
    batch_set($batch);
  }

}
