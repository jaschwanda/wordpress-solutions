<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(ABSPATH . WPINC . '/PHPMailer/Exception.php');
require_once(ABSPATH . WPINC . '/PHPMailer/PHPMailer.php');
require_once(ABSPATH . WPINC . '/PHPMailer/SMTP.php');

class USI_WordPress_Solutions_Mailer extends PHPMailer\PHPMailer\PHPMailer {

   const VERSION = '2.14.1 (2022-08-10)';

   protected static $debug = null;

   protected static $log   = 0;

   protected static $pass  = 0;

   protected static $queue = [];

   protected static $settings  = [
      'header_callback' => [],
      'label' => 'PHP Mailer',
      'localize_labels' => 'yes',
      'localize_notes' => 3, // <p class="description">__()</p>;
      'settings' => [
         'from-email' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'From E-mail',
            'notes' => 'Your e-mails originate from this address.',
         ],
         'from-name' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'From Name',
            'notes' => 'Your e-mails originate from this name.',
         ],
         'host' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'Host',
            'notes' => 'The hostname of your SMTP server.',
         ],
         'port' => [
            'f-class' => 'regular-text', 
            'type' => 'number', 
            'label' => 'Port',
            'notes' => 'Your SMTP server port number, usually 25, 465 or 587.',
         ],
         'smtp-auth' => [
            'type' => 'checkbox', 
            'label' => 'SMTP Authentication',
            'notes' => 'Check if your SMTP server requires authentication.',
         ],
         'username' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'Username',
            'readonly' => false,
            'notes' => 'Username to use for SMTP authentication.',
         ],
         'password' => [
            'f-class' => 'regular-text', 
            'type' => 'password', 
            'label' => 'Password',
            'readonly' => false,
            'notes' => 'Password to use for SMTP authentication.',
         ],
         'secure' => [
            'f-class' => 'regular-text', 
            'label' => 'Security',
            'options' => [
               [ 'none', 'none' ], 
               [ 'ssl', 'SSL' ],
               [ 'tls', 'TLS' ],
            ],
            'type' => 'select', 
            'notes' => 'Select if your SMTP server requires SSL or TLS.',
         ],
      ],
   ]; // php-mailer;

   protected static $test  = [
      'not_tabbed' => 'mailer',
      'title' => 'Testing:',
      'settings' => [
         'target-email' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'Black Hole E-mail',
            'notes' => 'All e-mail addresses will be captured and sucked into the above black hole address if given.',
         ],
         'debug' => [
            'f-class' => 'regular-text', 
            'label' => 'Debug',
            'options' => [
               [ '0', 'none' ],
               [ '1', 'level 1: Client' ],
               [ '2', 'level 2: Client and Server' ],
               [ '3', 'level 3: Client, Server, and Connection' ],
               [ '4', 'level 4: Low-Level Information' ],
            ],
            'type' => 'select', 
            'notes' => 'Select to get dignositcs on your SMTP operations.',
         ],
         'to-email' => [
            'f-class' => 'regular-text', 
            'type' => 'text', 
            'label' => 'Test E-mail',
            'notes' => 'Send a test e-mail to the above address if given. Configurations made above must be saved before sending test e-mail.',
         ],
      ],
   ]; // php-mailer-test;

   public function __construct() {

      if (!self::$pass++) {
         self::$log = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);
         register_shutdown_function([ __CLASS__, 'dequeue' ]);
      }

      parent::__construct();

      self::action_phpmailer_init($this);

      if (USI_WordPress_Solutions::DEBUG_MAILINIT == (USI_WordPress_Solutions::DEBUG_MAILINIT & self::$log)) usi::log('mail=', $this);

   } // __construct();

   public static function action_phpmailer_init($mail) {
      $options           = USI_WordPress_Solutions::$options['php-mailer'];
      $mail->isSMTP();
      $mail->From        = $options['from-email'];
      $mail->FromName    = $options['from-name'];
      $mail->Host        = $options['host'];
      $mail->Port        = (int)$options['port'];
      $mail->SMTPDebug   = (int)(USI_WordPress_Solutions::$options['php-mailer-test']['debug'] ?? 0);
      $mail->SMTPSecure  = $options['secure'];
      if (empty($options['smtp-auth'])) {
         $mail->SMTPAuth = false;
      } else {
         $mail->SMTPAuth = true;
         $mail->Password = $options['password'];
         $mail->Username = $options['username'];
      }
      if (!self::$pass) self::$log = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);
      if (USI_WordPress_Solutions::DEBUG_SMTP == (USI_WordPress_Solutions::DEBUG_SMTP & self::$log)) usi::log('$mail=', $mail);
    } // action_phpmailer_init();

   public static function dequeue() {
      if ($log = (USI_WordPress_Solutions::DEBUG_MAILDEQU == (USI_WordPress_Solutions::DEBUG_MAILDEQU & self::$log))) usi::log('self::$queue=', self::$queue);
      foreach (self::$queue as $mail) {
         try {
            if ($mail->ErrorInfo) {
               if ($log) usi::log('$errorInfo=', $mail->ErrorInfo, ' $mail=', $mail);
            } else {
               if ($mail->SMTPDebug) {
                  self::$debug = null;
                  $mail->Debugoutput = function($message, $level) { 
                     self::$debug .= '$level=' . $level . ' $message=' . $message . PHP_EOL; 
                  };
               }
               $status = $mail->Send();
               if ($log || $mail->SMTPDebug) usi::log('$status=', $status, ' $mail=', $mail, '\n', self::$debug);
            }
         } catch (Exception $e) {
            if ($log) usi::log('exception=', $e->GetMessage());
         }
      }
   } // dequeue();
 
   public function queue() {
      self::$queue[] = $this;
   } // queue();
 
   public static function settings($that) {
      self::$settings['header_callback'] = [ $that, 'sections_header', '      <p>WordPress is not configured to send emails by default, however, this plugin provides configuration of the PHPMailer system to send messages and support advanced transport features.</p>' ];
      self::$settings['settings']['username']['readonly'] = 
      self::$settings['settings']['password']['readonly'] = empty($that->options['php-mailer']['smtp-auth']);
      return(self::$settings);
   } // settings();
 
   public static function test() {
      return(self::$test);
   } // test();

} // class USI_WordPress_Solutions_Mailer;

add_action('phpmailer_init', [ 'USI_WordPress_Solutions_Mailer', 'action_phpmailer_init' ], 10, 1);

// --------------------------------------------------------------------------------------------------------------------------- // ?>