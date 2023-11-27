<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Diagnostics {

   const VERSION = '2.16.3 (2023-11-27)';

   private $options     = null;
   private $text_domain = null;

   function __construct($parent, $options) {

      $this->options     = $options;

      $this->text_domain = $parent->text_domain();

      $this->section     = [
         'fields_sanitize' => [$this, 'fields_sanitize'],
         'header_callback' => [$parent, 'sections_header', '      <p>' . sprintf(__(' Send this link: <b>%s</b> to the user to get the user\'s diagnostic session.', $this->text_domain), plugin_dir_url(__FILE__) . 'diagnostics.php') . '</p>' . PHP_EOL],
         'label' => 'Diagnostics',
         'localize_labels' => 'yes',
         'localize_notes' => 3, // <p class="description">__()</p>;
         'settings' => [
            'session' => [
               'f-class' => 'regular-text', 
               'label' => 'Diagnostic Session',
               'notes' => 'Enter the diagnostic session from the user you wish to analyze.',
               'type' => 'text', 
            ],
            'code' => [
               'f-class' => 'regular-text', 
               'label' => 'Diagnostic Code',
               'notes' => 'Code used to select diagnostic operations.',
               'readonly' => true, 
               'type' => 'text', 
            ],
         ],
      ];

      foreach ($options as $key => $values) {
         if (!empty($values['skip'])) continue;
         $this->section['settings'][$key]['label'] = $key;
         $this->section['settings'][$key]['notes'] = $values['notes'];
         $this->section['settings'][$key]['type'] = 'checkbox';
         $this->section['settings'][$key]['usi-code'] = $values['value'];
      }

   } // __construct();

   function fields_sanitize($input) {
      $code = 0;
      if (!empty($input['diagnostics']['session'])) {
         foreach ($input['diagnostics'] as $key => $value) {
            if ('checkbox' == $this->section['settings'][$key]['type']) {
               if (!empty($this->options[$key]['value'])) $code |= $this->section['settings'][$key]['usi-code'];
            }
         }
      }
      $input['diagnostics']['code'] = $code;
      return $input;
   } // fields_sanitize();

   public static function get_log($options, $log_log = false) {
      $log = 0;
      if (!empty($options['diagnostics']['session'])) {
         if (!($session_id = session_id())) {
            session_start(); 
            $session_id = session_id();
         }
         if (($session_id == $options['diagnostics']['session']) || ('all' == strtolower($options['diagnostics']['session']))) {
            if (!empty($options['diagnostics']['code'])) $log = (int)$options['diagnostics']['code'];
         }
      }
      if ($log_log) usi::log2('$log=', $log);
      return $log;
   } // get_log();

} // Class USI_WordPress_Solutions_Diagnostics;

// --------------------------------------------------------------------------------------------------------------------------- // ?>