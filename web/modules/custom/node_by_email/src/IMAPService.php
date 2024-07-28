<?php

namespace Drupal\node_by_email;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class IMAPService.
 */
class IMAPService {

  /**
   * Config manager service.
   *
   * @var object
   */
  protected $configFactory;

  /**
   * Current user object.
   *
   * @var object
   */
  protected $currentUser;

  /**
   * Entity manager service.
   *
   * @var object
   */
  protected $entityManager;

  /**
   * Drupal\node_by_email\IMAPService definition.
   *
   * @var \Drupal\node_by_email\IMAPService
   */
  protected $imapConnection;

  /**
   * From email id.
   *
   * @var string
   */
  protected $fromEmail;

  /**
   * Constructs a new IMAPService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountProxyInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    
  }

  /**
   * Create IMAP Connection.
   */
  public function connection() {
    $nodebyemailconfig = $this->configFactory->getEditable('node_by_email.nodebyemailconfig');
    $imap_connection_string = $nodebyemailconfig->get("imap_connection_string");
    $email_username = $nodebyemailconfig->get("email_username");
    $imap_password = $nodebyemailconfig->get("imap_password");
    if ($this->imapConnection = @imap_open($imap_connection_string, $email_username, $imap_password)) {
      // $this->fromEmail = $nodebyemailconfig->get('from_email');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get IMAP Connection.
   */
  public function getImapConnection() {
    $this->connection();
    return $this->imapConnection;
  }

  /**
   * Unseen email array, which is received from configured email id.
   *
   * @return array
   *   Array list of unseen mail.
   */
  public function getUnseenEmailList() {
    $this->connection();
    if (!empty($this->imapConnection)) {
      // return imap_search($this->imapConnection, 'FROM "' . $this->fromEmail . '" UNSEEN');
      return imap_search($this->imapConnection, 'ALL UNSEEN');
    }
  }
  

  /**
   * Set Seen flag to mail.
   *
   * @param int $mid
   *   Set unseen flagged to mail.
   */
  public function setUnseenMail($mid) {
    $this->connection();
    imap_setflag_full($this->imapConnection, $mid, "\\Seen \\Flagged");
  }

  /**
   * Get email header.
   *
   * @param int $mid
   *   Email ID.
   * @param string $format
   *   MIME Type.
   *
   * @return mixed
   *   Returns mail header.
   */
  public function getEmailHeader($mid = NULL, $format = 'TEXT/PLAIN') {
    $this->connection();
    $mail_header = imap_headerinfo($this->imapConnection, $mid);
    $sender = $mail_header->from[0];
    $sender_replyto = $mail_header->reply_to[0];
    $mail_details = [];
    if (strtolower($sender->mailbox) != 'mailer-daemon' && strtolower($sender->mailbox) != 'postmaster') {
      $mail_details = [
        'datetime' => date("Y-m-d H:i:s", $mail_header->udate),
        'from' => strtolower($sender->mailbox) . '@' . $sender->host,
        'fromName' => $sender->personal,
        'replyTo' => strtolower($sender_replyto->mailbox) . '@' . $sender_replyto->host,
        'replyToName' => $sender_replyto->personal,
        'subject' => iconv_mime_decode($mail_header->subject, 0, "utf-8"),
        'to' => strtolower($mail_header->toaddress),
        'body' => $this->getEmailBody($mid, $format),
      ];
    }
    return $mail_details;
  }

  /**
   * Get body of the email from mid.
   *
   * @param int $mid
   *   Email ID.
   * @param string $format
   *   Format of email.
   *
   * @return string
   *   Gives mail body.
   */
  public function getEmailBody($mid = NULL, $format = 'html') {
    $this->connection();
    $body = "";
    if (strtolower($format) == 'html') {
      $body = $this->getPart($this->imapConnection, $mid, "TEXT/HTML");
    }
    if ($body == "") {
      $body = $this->getPart($this->imapConnection, $mid, "TEXT/PLAIN");
    }
    if ($body == "") {
      return "";
    }
    return $body;
  }

  /**
   * This function gives part of the email.
   *
   * @param Object $stream
   *   IMAP Object.
   * @param int $msg_number
   *   Mail ID.
   * @param string $mime_type
   *   MIME Type.
   * @param string $structure
   *   Structure of Email.
   * @param int $part_number
   *   Part Number.
   *
   * @return mixed
   *   Email body part or FALSE.
   */

  /**
   * Get Part Of Message Internal Private Use.
   */
  private function getPart($stream, $msg_number, $mime_type, $structure = FALSE, $part_number = FALSE) {

    if (!$structure) {
      $structure = imap_fetchstructure($stream, $msg_number);
    }
    if ($structure) {
      if ($mime_type == $this->getMimeType($structure)) {
        if (!$part_number) {
          $part_number = "1";
        }
        $text = imap_fetchbody($stream, $msg_number, $part_number, FT_PEEK);

        if ($structure->encoding == 1) {
          return imap_utf8($text);
        }
        elseif ($structure->encoding == 3) {
          return imap_base64($text);
        }
        elseif ($structure->encoding == 4) {
          return imap_qprint($text);
        }
        else {
          return $text;
        }
      }
      if ($structure->type == 1) {/* multipart */
        foreach ($structure->parts as $index => $sub_structure) {
          // While (list($index, $sub_structure) = each($structure->parts)) {.
          $prefix = '';
          if ($part_number) {
            $prefix = $part_number . '.';
          }
          $data = $this->getPart($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
          if ($data) {
            return $data;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Get Mime type Internal Private Use.
   *
   * @param string $structure
   *   Which part of email.
   *
   * @return string
   *   Gives mime type.
   */
  private function getMimeType(&$structure) {
    $primary_mime_type = [
      "TEXT",
      "MULTIPART",
      "MESSAGE",
      "APPLICATION",
      "AUDIO",
      "IMAGE",
      "VIDEO",
      "OTHER",
    ];

    if ($structure->subtype) {
      return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
    }
    return "TEXT/PLAIN";
  }

}
