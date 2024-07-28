<?php

namespace Drupal\node_by_email\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node_by_email\IMAPService;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Class NodeByEmailConfigForm.
 */
class NodeByEmailConfigForm extends ConfigFormBase {

  /**
   * Config manager service.
   *
   * @var object
   */
  protected $configManager;

  /**
   * Current user object.
   *
   * @var object
   */
  protected $currentUser;

  /**
   * Drupal\node_by_email\IMAPService definition.
   *
   * @var \Drupal\node_by_email\IMAPService
   */
  protected $imapService;

  /**
   * Messanger Service.
   *
   * @var object
   */
  protected $messanger;

  /**
   * Construct method for configuration form.
   */
  public function __construct(
  ConfigFactoryInterface $configManager,
  AccountProxyInterface $current_user,
  IMAPService $imap,
  MessengerInterface $messenger
  ) {
    $this->configManager = $configManager;
    $this->currentUser = $current_user;
    $this->imapService = $imap;
    $this->messanger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'), $container->get('current_user'), $container->get('node_by_email.imap_connection'), $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'node_by_email.nodebyemailconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_by_email_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('node_by_email.nodebyemailconfig');

    $form['imap_setting'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Node by email configuration.'),
    ];
    $form['imap_connection'] = [
      '#type' => 'details',
      '#title' => $this->t('IMAP Connection'),
      '#group' => 'imap_setting',
      '#required' => TRUE,
    ];
    $form['imap_connection']['imap_connection_string'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Imap Connection String'),
      '#description' => $this->t('Example strings:  <br/> Gmail {imap.gmail.com:993/imap/ssl} <br/>
Yahoo {imap.mail.yahoo.com:993/imap/ssl} <br/>
AOL {imap.aol.com:993/imap/ssl}. <br/> Please enter string with currly brases.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('imap_connection_string'),
      '#required' => TRUE,
    ];

    $form['imap_connection']['email_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email/Username Of Imap Account'),
      '#description' => $this->t('Enter Email/Username for your IMAP email account'),
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => [
        'autocomplete' => ['off'],
      ],
      '#default_value' => $config->get('email_username'),
      '#required' => TRUE,
    ];

    $form['imap_connection']['imap_password'] = [
      '#type' => 'password',
      '#title' => $this->t('IMAP Account Password'),
      '#description' => $this->t('Enter password for Imap Email/username'),
      '#maxlength' => 64,
      '#size' => 64,
      '#attributes' => [
        'autocomplete' => 'off',
      ],
      '#default_value' => $config->get('imap_password'),
      '#required' => TRUE,
    ];

    // $form['imap_connection']['from_email'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('From Email'),
    //   '#description' => $this->t('Email which is receved from this emailid, which be create nodes.'),
    //   '#maxlength' => 64,
    //   '#size' => 64,
    //   '#attributes' => [
    //     'autocomplete' => ['off'],
    //   ],
    //   '#default_value' => $config->get('from_email'),
    //   '#required' => TRUE,
    // ];

    $form['imap_connection']['imap_connected'] = [
      '#type' => 'hidden',
    ];

    $form['imap_content_type'] = [
      '#type' => 'details',
      '#title' => $this->t('Content Type'),
      '#group' => 'imap_setting',
      '#required' => TRUE,
    ];

    $options = [];
    foreach (NodeType::loadMultiple() as $type) {
      $options[$type->id()] = $type->label();
    }
    $form['imap_content_type']['node_types'] = [
      '#title' => $this->t("Content Type."),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => array_keys($config->get('node_types')),
    ];

    $form['imap_publishing_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Publishing Options'),
      '#group' => 'imap_setting',
      '#required' => TRUE,
    ];

    // $form['imap_publishing_options']['author_uid'] = [
    //   '#title' => $this->t('Author By'),
    //   '#type' => 'entity_autocomplete',
    //   '#target_type' => 'user',
    //   '#required' => TRUE,
    //   '#default_value' => User::load($config->get('author_uid')),
    // ];

    $form['imap_publishing_options']['publishing_option'] = [
      '#title' => $this->t('Published'),
      '#type' => 'radios',
      '#options' => [
        1 => $this->t("Published"),
        0 => $this->t("Un Published"),
      ],
      '#default_value' => !empty($config->get('publishing_option')) ? $config->get('publishing_option') : "",
    ];

    $form['imap_publishing_options']['cron_interval'] = [
      '#required' => TRUE,
      '#title' => $this->t('Cron interval Seconds'),
      '#type' => 'number',
      '#step' => 1,
      '#min' => 0,
      '#max' => 99999,
      '#description' => $this->t("Cron interval seconds."),
      '#default_value' => !empty($config->get('cron_interval')) ? $config->get('cron_interval') : 3600,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $imap_connected = 0;

    if (!empty($this->imapService->connection())) {
      $imap_connected = 1;
    }

    $this->config('node_by_email.nodebyemailconfig')
      ->set('imap_connection_string', $form_state->getValue('imap_connection_string'))
      ->set('email_username', $form_state->getValue('email_username'))
      // ->set('from_email', $form_state->getValue('from_email'))
      ->set('imap_password', $form_state->getValue('imap_password'))
      ->set('imap_connected', $imap_connected)
      ->set('node_types', array_filter($form_state->getValue('node_types')))
      // ->set('author_uid', $form_state->getValue('author_uid'))
      ->set('cron_interval', $form_state->getValue('cron_interval'))
      ->set('publishing_option', $form_state->getValue('publishing_option'))
      ->save();

    if ($this->imapService->connection()) {
      $this->messanger->addStatus($this->t("IMAP connection is made successfully."));
    }
    else {
      $this->messanger->addWarning($this->t("IMAP is not connected yet. Please enter correct IMAP details."));
    }
  }

}
