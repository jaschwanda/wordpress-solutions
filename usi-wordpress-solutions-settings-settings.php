<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2023 by Jim Schwanda.
*/

require_once('usi-wordpress-solutions-capabilities.php');
require_once('usi-wordpress-solutions-diagnostics.php');
require_once('usi-wordpress-solutions-popup-iframe.php');
require_once('usi-wordpress-solutions-settings.php');
require_once('usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.15.1 (2023-06-30)';

   protected $debug     = 0;
   protected $is_tabbed = true;

   function __construct() {

      $this->debug = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);

      parent::__construct(
         [
            'name' => USI_WordPress_Solutions::NAME, 
            'prefix' => USI_WordPress_Solutions::PREFIX, 
            'text_domain' => USI_WordPress_Solutions::TEXTDOMAIN,
            'options' => USI_WordPress_Solutions::$options,
            'capabilities' => USI_WordPress_Solutions::$capabilities,
            'file' => str_replace('-settings', '', __FILE__), // Plugin main file, this initializes capabilities on plugin activation;
         ]
      );

   } // __construct();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_WordPress_Solutions::TEXTDOMAIN)) {
         $links[0] = USI_WordPress_Solutions_Versions::link(
            $links[0], // Original link text;
            USI_WordPress_Solutions::NAME, // Title;
            USI_WordPress_Solutions::VERSION, // Version;
            USI_WordPress_Solutions::TEXTDOMAIN, // Text domain;
            __DIR__ // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/wordpress-solutions" target="_blank">' . 
            __('Donate', USI_WordPress_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

   function fields_sanitize($input) {

      $transfer_label = __('Transfer', USI_WordPress_Solutions::TEXTDOMAIN);

      $input = parent::fields_sanitize($input);

      $log   = (USI_WordPress_Solutions::DEBUG_XFER == (USI_WordPress_Solutions::DEBUG_XFER & $this->debug));

      $input['preferences']['e-mail-cloak'] = sanitize_key($input['preferences']['e-mail-cloak']);

      $pcre_backtrack_limit = ini_get('pcre.backtrack_limit');

      if ($input['admin-limits']['mpdf-pcre-limit'] < $pcre_backtrack_limit) $input['admin-limits']['mpdf-pcre-limit'] = $pcre_backtrack_limit;;

      unset($input['versions']['export']);

      if (!empty($_REQUEST['submit']) && ('Execute Versions' == $_REQUEST['submit'])) {
         if (!empty($input['versions']['mode'])) {
            if ('export' == $input['versions']['mode']) {
               require_once('usi-wordpress-solutions-versions-all.php');
               $title   = sanitize_title(get_bloginfo('title'));
               $content = USI_WordPress_Solutions_Versions_All::versions($title, WP_CONTENT_DIR);
               $input['versions']['export'] = $content;
            }
         }
      }

      if (!empty($_REQUEST['submit']) && ($transfer_label == $_REQUEST['submit'])) {

         $content = $title = $type = null;

         if (!empty($input['xfer']['parent-title']) && !empty($input['xfer']['post-type'])) {

            $title = $input['xfer']['parent-title'];

            $type  = $input['xfer']['post-type'];

            $parent_post = get_posts(
               $args = [
                  'numberposts' => 1,
                  'post_status' => 'publish',
                  'post_type' => $type,
                  'title' => $title,
               ]
            );

            if ($log) usi::log('$args=', $args, '\n$post_parent=', $parent_post);

            if (!empty($parent_post[0]->ID)) {

               $posts = get_posts(
                  $args = [
                     'numberposts' => -1,
                     'post_parent' => $parent_post[0]->ID,
                     'post_status' => 'publish',
                     'post_type' => $type,
                  ]
               );

               if ($log) usi::log('$args=', $args, '\n$posts=', $posts);

               foreach ($posts as $post) {

                  $content .= '"' . $post->post_title . '":' . PHP_EOL . '{' . PHP_EOL;

                  $content .= str_replace('","', '",' . PHP_EOL . '"', trim(trim($post->post_content, '}'), '{'));

                  $content .= PHP_EOL . '}' . PHP_EOL;

               }

            }

         }

         $input['xfer']['content']      = $content;

         $input['xfer']['parent-title'] = $title;

         $input['xfer']['post-type']    = $type;

         if (!empty($input['xfer']['options'])) {

            $array = json_decode($input['xfer']['options'], true);

            if (!empty($input['xfer']['export-import'])) {

               if ('export' == $input['xfer']['export-import']) {

                  if ($log) usi::log('$array=', $array);

                  foreach ($array as $plugin => $temp1) {

                     $options = get_option($plugin);

                     if ($log) usi::log('$options=', $options);

                     foreach ($temp1 as $section => $temp2) {

                        foreach ($temp2 as $setting => $value) {

                           if ($log) usi::log($plugin . '[' . $section . '][' . $setting . ']=', $value);

                           $array[$plugin][$section][$setting] = $options[$section][$setting] ?? null;

                        }

                     }

                  }

               } else if ('import' == $input['xfer']['export-import']) {

                  if ($log) usi::log('$array=', $array);

                  foreach ($array as $plugin => $temp1) {

                     $options = get_option($plugin);

                     if ($log) usi::log('$plugin=', $plugin, '\n$options=', $options);

                     $is_this = ('usi-wordpress-options' == $plugin);

                     foreach ($temp1 as $section => $temp2) {

                        foreach ($temp2 as $setting => $value) {

                           if ($log) usi::log($plugin . '[' . $section . '][' . $setting . ']=', $value);

                           $options[$section][$setting] = $array[$plugin][$section][$setting] ?? null;

                           if ($is_this) $input[$section][$setting] = $options[$section][$setting];

                        }

                     }

                     if ($log) usi::log('$plugin=', $plugin, '\n$options=', $options);

                     update_option($plugin, $options);

                  }

               }

               add_settings_error($this->page_slug, 'notice-success', __('Transfer complete.', USI_WordPress_Solutions::TEXTDOMAIN), 'notice-success');

            }

            $input['xfer']['options'] = json_encode($array, JSON_UNESCAPED_SLASHES | (!empty($input['xfer']['pretty-print']) ? JSON_PRETTY_PRINT : 0));

         }

      }

      if (!empty($input['php-mailer-test']['to-email'])) {
         $mail = new USI_WordPress_Solutions_Mailer();
         $mail->AddAddress($input['php-mailer-test']['to-email']);
         $mail->Body      = 'This is a test e-mail from the USI WordPress Solutions Plugin version ' . USI_WordPress_Solutions::VERSION . '.';
         $mail->SMTPDebug = $input['php-mailer-test']['debug'];
         $mail->Subject   = 'USI WordPress Solutions E-mail Plugin Test';
         $mail->queue();
         unset($input['php-mailer-test']['to-email']);
      }

      if (empty($input['php-mailer']['smtp-auth'])) {
         unset($input['php-mailer']['username']);
         unset($input['php-mailer']['password']);
      }

      unset($input['xfer']['export-import']);

      return($input);

   } // fields_sanitize();

   function sections() {

      $transfer_label = __('Transfer', USI_WordPress_Solutions::TEXTDOMAIN);

      USI_WordPress_Solutions_Popup_Iframe::build(
         [
            'close'  => __('Close', USI_WordPress_Solutions::TEXTDOMAIN),
            'height' => '640px',
            'id'     => 'usi-popup-phpinfo',
            'width'  => '980px',
         ]
      );

      $phpinfo_anchor = USI_WordPress_Solutions_Popup_Iframe::link(
         [
            'id'     => 'usi-popup-phpinfo',
            'iframe' => plugins_url(null, __FILE__) . '/usi-wordpress-solutions-phpinfo-scan.php',
            'link'   => ['text' => 'phpinfo()'],
            'tip'    => __('Display PHP information', USI_WordPress_Solutions::TEXTDOMAIN),
            'title'  => 'phpinfo()',
         ]
      );

      $current         = error_reporting();

      $error_constants = [
         'E_ALL' => E_ALL,
         'E_COMPILE_ERROR' => E_COMPILE_ERROR,
         'E_COMPILE_WARNING' => E_COMPILE_WARNING,
         'E_CORE_ERROR' => E_CORE_ERROR,
         'E_CORE_WARNING' => E_CORE_WARNING,
         'E_DEPRECATED' => E_DEPRECATED,
         'E_ERROR' => E_ERROR,
         'E_NOTICE' => E_NOTICE,
         'E_PARSE' => E_PARSE,
         'E_RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
         'E_STRICT' => E_STRICT,
         'E_USER_DEPRECATED' => E_USER_DEPRECATED,
         'E_USER_ERROR' => E_USER_ERROR,
         'E_USER_NOTICE' => E_USER_NOTICE,
         'E_USER_WARNING' => E_USER_WARNING,
         'E_WARNING' => E_WARNING,
      ];

      $php_reporting   = '0x' . strtoupper(dechex($current)) . ' = ';
      $separator       = '';

      foreach ($error_constants as $key => $value) {
         if ($value == ($current & $value)) {
            $php_reporting .= $separator . $key;
            $separator      = ' | ';
         }
      }

      $php_version = phpversion();

      if ('8' == $php_version[0]) {
         $php_reporting    .= "<br/><br/>Some versions of WordPress running on PHP " . $php_version . " give a large number of deprecated errors, to surpess these errors and still enable debugging, add the following line to the wp-config.php file after the <span style=\"font-family:monospace;\">\"define( 'WP_DEBUG', false );\"</span> statement:<p style=\"font-family:monospace; padding-top:10px;\">error_reporting(E_ALL&~(E_DEPRECATED|E_USER_DEPRECATED));\$GLOBALS['wp_filter']=['enable_wp_debug_mode_checks'=>[10=>[['accepted_args'=>0,'function'=>function(){return(false);}]]]];</p>";
      }

      $pcre_backtrack_limit = ini_get('pcre.backtrack_limit');

      $mpdf_pcre_limit      = (int)($this->options['admin-limits']['mpdf-pcre-limit'] ?? $pcre_backtrack_limit);

      $skip_phpmailer       = empty($this->options['admin-options']['mailer']);

      $sections = [

         'preferences' => [
            'header_callback' => [$this, 'sections_header', '    <p>' . __('The WordPress-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
            'implementation of WordPress functionality. Additionally, you can place all of the Universal Solutions settings pages ' .
            'at the end of the Settings sub-menu, or you can sort the Settings sub-menu alphabetically or not at all.', 
             USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL],
            'label' => __('Preferences', USI_WordPress_Solutions::TEXTDOMAIN), 
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => [
               'menu-sort' => [
                  'type' => 'radio', 
                  'label' => 'Settings Menu Sort Option',
                  'choices' => [
                     [
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('No sorting', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ],
                     [
                        'value' => 'alpha', 
                        'label' => true, 
                        'notes' => __('Alphabetical sorting selection', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ],
                     [
                        'value' => 'usi', 
                        'label' => true, 
                        'notes' => __('Sort Universal Solutions settings and move to end of menu', USI_WordPress_Solutions::TEXTDOMAIN), 
                     ],
                  ],
                  'notes' => 'Defaults to <b>No sorting</b>.',
               ], // menu-sort;
               'admin-notice' => [
                  'f-class' => 'large-text', 
                  'rows' => 2,
                  'type' => 'textarea', 
                  'label' => 'Admin Notice',
               ],
               'e-mail-cloak' => [
                  'f-class' => 'regular-text', 
                  'label' => 'Cloaking Shortcode Identifier',
                  'notes' => __('Shortcode to use for e-mail cloaking, cloaking is disabled if not given.', USI_WordPress_Solutions::TEXTDOMAIN), 
               ],
            ],
         ], // preferences;

         'admin-options' => [
            'title' => __('Administrator Options', USI_WordPress_Solutions::TEXTDOMAIN),
            'not_tabbed' => 'preferences',
            'settings' => [
               'history' => [
                  'type' => 'checkbox', 
                  'label' => 'Enable Historian',
                  'notes' => 'The system historian records user, configuration and update events in the system database.',
               ],
               'mailer' => [
                  'type' => 'checkbox', 
                  'label' => 'Enable PHPMailer',
                  'notes' => 'Enables the PHPMailer functionality included with WordPress, a new tab will appear if checked.',
               ],
               'impersonate' => [
                  'type' => 'checkbox', 
                  'label' => 'Enable User Switching',
                  'notes' => 'Enables administrators to impersonate another WordPress user.',
               ],
               'pass-reset' => [
                  'type' => 'checkbox', 
                  'label' => 'Remove Password Reset',
                  'notes' => 'Remove the password reset link option from the row actions in the user display.',
               ],
               'options_php' => [
                  'type' => 'html', 
                  'html' => '<a href="options.php" title="Semi-secret settings on options.php page">options.php</a>',
                  'label' => 'Semi-Secret Settings',
               ],
            ],
         ], // admin-options;

         'admin-limits' => [
            'title' => __('Other Options and Limits', USI_WordPress_Solutions::TEXTDOMAIN),
            'not_tabbed' => 'preferences',
            'settings' => [
               'mpdf-version' => [
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'mPDF Version',
                  'notes' => '8.1.4 or null for default. Repository found at https://github.com/mpdf/mpdf .',
               ],
               'mpdf-pcre-limit' => [
                  'f-class' => 'regular-text', 
                  'label' => 'pcre.backtrack_limit',
                  'notes' => 'This option only affects the <i>pcre.backtrack_limit</i> when doing a PDF download, the current system wide limit is ' . $pcre_backtrack_limit . '.',
                  'type' => 'number', 
                  'value' => $mpdf_pcre_limit, 
               ],
            ],
         ], // admin-limits;

         'capabilities' => new USI_WordPress_Solutions_Capabilities($this),

         'diagnostics' => new USI_WordPress_Solutions_Diagnostics($this, 
            [
               'DEBUG_INIT' => [
                  'value' => USI_WordPress_Solutions::DEBUG_INIT,
                  'notes' => 'Log USI_WordPress_Solutions_Settings::action_admin_init() method.',
               ],
               'DEBUG_MAILDEQU' => [
                  'value' => USI_WordPress_Solutions::DEBUG_MAILDEQU,
                  'notes' => 'Log USI_WordPress_Solutions_Mailer::dequeu() method.',
                  'skip'  => $skip_phpmailer,
               ],
               'DEBUG_MAILINIT' => [
                  'value' => USI_WordPress_Solutions::DEBUG_MAILINIT,
                  'notes' => 'Log USI_WordPress_Solutions_Mailer::__construct() method.',
                  'skip'  => $skip_phpmailer,
               ],
               'DEBUG_OPTIONS' => [
                  'value' => USI_WordPress_Solutions::DEBUG_OPTIONS,
                  'notes' => 'Log USI_WordPress_Solutions::$options.',
               ],
               'DEBUG_PDF' => [
                  'value' => USI_WordPress_Solutions::DEBUG_PDF,
                  'notes' => 'Log USI_WordPress_Solutions_PDF operations.',
               ],
               'DEBUG_RENDER' => [
                  'value' => USI_WordPress_Solutions::DEBUG_RENDER,
                  'notes' => 'Log USI_WordPress_Solutions_Settings::fields_render() method.',
               ],
               'DEBUG_SMTP' => [
                  'value' => USI_WordPress_Solutions::DEBUG_SMTP,
                  'notes' => 'Log DEBUG_SMTP smtp operations.',
                  'skip'  => $skip_phpmailer,
               ],
               'DEBUG_XFER' => [
                  'value' => USI_WordPress_Solutions::DEBUG_XFER,
                  'notes' => 'Log USI_WordPress_Solutions xport functionality.',
               ],
            ]
         ),

         'illumination' => [
            'title' => 'Illumination',
            'not_tabbed' => 'diagnostics',
            'settings' => [
               'info-php' => [
                  'type' => 'html', 
                  'html' => $phpinfo_anchor,
                  'label' => 'Information - PHP',
               ],
               'info-site' => [
                  'type' => 'html', 
                  'html' => '<a href="' . admin_url('site-health.php?tab=debug') . '">Site Health - Info</a>',
                  'label' => 'Information - Site',
               ],
               'active-users' => [
                  'type' => 'html', 
                  'html' => '<a href="admin.php?page=usi-wordpress-solutions-user-sessions">Users Logged In</a>',
                  'label' => 'Users Currently Logged In',
               ],
               'visible-grid' => [
                  'type' => 'checkbox', 
                  'label' => 'Visable Grid Borders',
               ],
            ],
         ], // illumination;

         'limits-values' => [
            'title' => 'Constants, Limits and Values',
            'not_tabbed' => 'diagnostics',
            'settings' => [
               'php-memory-limit' => [
                  'html' => ini_get('memory_limit'),
                  'label' => 'PHP memory_limit',
                  'notes' => 'The maximum amount of memory in bytes that an individual script is allowed to allocate.',
                  'type' => 'html', 
               ],
               'disable-wp-cron' => [
                  'html' => defined('DISABLE_WP_CRON') ? (DISABLE_WP_CRON ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'DISABLE_WP_CRON',
                  'notes' => 'When true the standard WordPress cron handling is disabled.',
                  'type' => 'html', 
               ],
               'wp-memory-limit' => [
                  'html' => defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 'undefined',
                  'label' => 'WP_MEMORY_LIMIT',
                  'notes' => 'The maximum amount of memory that can be consumed by PHP.',
                  'type' => 'html', 
               ],
               'ABSPATH' => [
                  'html' => defined('ABSPATH') ? ABSPATH : 'undefined',
                  'label' => 'ABSPATH',
                  'type' => 'html', 
               ],
               'UPLOADS' => [
                  'html' => defined('UPLOADS') ? UPLOADS : 'undefined',
                  'label' => 'UPLOADS',
                  'type' => 'html', 
               ],
               'WP_CONTENT_URL' => [
                  'html' => defined('WP_CONTENT_URL') ? WP_CONTENT_URL : 'undefined',
                  'label' => 'WP_CONTENT_URL',
                  'type' => 'html', 
               ],
            ],
         ], // limits-values;

         'debug-values' => [
            'title' => 'Debug Options',
            'not_tabbed' => 'diagnostics',
            'settings' => [
               'wp-debug' => [
                  'html' => defined('WP_DEBUG') ? (WP_DEBUG ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG',
                  'notes' => 'When true triggers "debug" mode throughout WordPress.',
                  'type' => 'html', 
               ],
               'wp-debug-log' => [
                  'html' => defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG_LOG',
                  'notes' => 'When true and when WP_DEBUG_DISPLAY is false, WordPress writes errors to the <i>debug.log</i> file inside the <i>wp-content</i> folder.',
                  'type' => 'html', 
               ],
               'wp-debug-display' => [
                  'html' => defined('WP_DEBUG_DISPLAY') ? (WP_DEBUG_DISPLAY ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG_DISPLAY',
                  'notes' => 'When true WordPress shows errors and warnings on the page as they are generated.',
                  'type' => 'html', 
               ],
               'wp-script-debug' => [
                  'html' => defined('SCRIPT_DEBUG') ? (SCRIPT_DEBUG ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'SCRIPT_DEBUG',
                  'notes' => 'When true WordPress loads the full version of .CSS and .JS files, otherwise the minified versions are loaded.',
                  'type' => 'html', 
               ],
               'php-report' => [
                  'type' => 'html', 
                  'html' => $php_reporting,
                  'label' => 'Error Reporting',
               ],
            ],
         ], // limits-values;

         'php-mailer' => 'placeholder',

         'php-mailer-test' => 'placeholder',

         'versions' => [
            'label' => __('Versions', USI_WordPress_Solutions::TEXTDOMAIN), 
            'footer_callback' => [$this, 'sections_footer', 'Execute Versions'],
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => [
               'mode' => [
                  'f-class' => 'large-text', 
                  'label' => 'Select Functionality',
                  'options' => [
                     [0 => 'compare', 1 => 'Compare version information'],
                     [0 => 'export', 1 => 'Export current version information'],
                     [0 => 'import', 1 => 'Import source version information']
                  ],
                  'type' => 'select', 
               ],
            ],
         ], // versions;

         'xfer' => [
            'label' => $transfer_label, 
            'header_callback' => [$this, 'sections_header', '    <p>' . __('Transfer and document post content and plugin options.', USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL],
            'footer_callback' => [$this, 'sections_footer', $transfer_label],
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => [
               'post-type' => [
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Post Type',
               ],
               'parent-title' => [
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Parent Post Title',
               ],
               'content' => [
                  'f-class' => 'large-text', 
                  'rows' => 6,
                  'type' => 'textarea', 
                  'label' => 'Post Content',
                  'notes' => 'Both the above <b>Post Type</b> and <b>Parent Post Title</b> fields must be given to transfer post content.',
               ],
               'options' => [
                  'f-class' => 'large-text', 
                  'rows' => 6,
                  'type' => 'textarea', 
                  'label' => 'Plugin Options',
               ],
               'export-import' => [
                  'type' => 'radio', 
                  'choices' => [
                     [
                        'value' => 'export', 
                        'label' => true, 
                        'notes' => __('Extract the values for the keys listed in the above <b>Plugin Options</b> box.', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ],
                     [
                        'value' => 'import', 
                        'label' => true, 
                        'notes' => __('Insert the values for the keys listed in the above <b>Plugin Options</b> box.', USI_WordPress_Solutions::TEXTDOMAIN), 
                     ],
                  ],
               ],
               'pretty-print' => [
                  'type' => 'checkbox', 
                  'prefix' => '<label>',
                  'suffix' => 'Pretty print the JSON string in the above <b>Plugin Options</b> box.</label>',
               ],
            ],
         ], // xport;

      ];

      if ($skip_phpmailer) {
         unset($sections['php-mailer']);
         unset($sections['php-mailer-test']);
      } else {
         $sections['php-mailer'] = USI_WordPress_Solutions_Mailer::settings($this);
         $sections['php-mailer-test'] = USI_WordPress_Solutions_Mailer::test();
      }

      if (empty($this->options['versions']['mode'])) {
      } else if ('compare' == $this->options['versions']['mode']) {
         require_once('usi-wordpress-solutions-versions-show.php');
         $import = !empty($this->options['versions']['import']) ? $this->options['versions']['import'] : null;
         $sections['versions']['settings']['compare'] = [
            'html' => USI_WordPress_Solutions_Versions_Show::show($import),
            'type' => 'html', 
         ];
         $sections['versions']['settings']['import'] = [
            'type' => 'hidden', 
         ];
      } else if ('export' == $this->options['versions']['mode']) {
         $sections['versions']['settings']['export'] = [
            'f-class' => 'large-text', 
            'rows' => 16,
            'type' => 'textarea', 
            'label' => 'Export Current Installation',
         ];
         $sections['versions']['settings']['import'] = [
            'type' => 'hidden', 
         ];
      } else if ('import' == $this->options['versions']['mode']) {
         $sections['versions']['settings']['import'] = [
            'f-class' => 'large-text', 
            'rows' => 16,
            'type' => 'textarea', 
            'label' => 'Import Source Installation',
         ];
      }

      return($sections);

   } // sections();

} // Class USI_WordPress_Solutions_Settings_Settings;

new USI_WordPress_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>