<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

require_once('usi-wordpress-solutions-capabilities.php');
require_once('usi-wordpress-solutions-diagnostics.php');
require_once('usi-wordpress-solutions-popup-iframe.php');
require_once('usi-wordpress-solutions-settings.php');
require_once('usi-wordpress-solutions-settings-layout.php');
require_once('usi-wordpress-solutions-updates.php');
require_once('usi-wordpress-solutions-versions.php');

class USI_WordPress_Solutions_Settings_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '2.14.0 (2022-06-19)';

   protected $debug     = 0;
   protected $is_tabbed = true;

   function __construct() {

      $this->debug = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);

      parent::__construct(
         array(
            'name' => USI_WordPress_Solutions::NAME, 
            'prefix' => USI_WordPress_Solutions::PREFIX, 
            'text_domain' => USI_WordPress_Solutions::TEXTDOMAIN,
            'options' => USI_WordPress_Solutions::$options,
            'capabilities' => USI_WordPress_Solutions::$capabilities,
            'file' => str_replace('-settings', '', __FILE__), // Plugin main file, this initializes capabilities on plugin activation;
         )
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

      $input = parent::fields_sanitize($input);

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

      if (!empty($_REQUEST['submit']) && ('eXport Posts' == $_REQUEST['submit'])) {

         if (!empty($input['xport']['parent-post']) && !empty($input['xport']['post-type'])) {

            $log = (USI_WordPress_Solutions::DEBUG_XPORT == (USI_WordPress_Solutions::DEBUG_XPORT & $this->debug));

            $parent_post = get_posts(
               $args = array(
                  'numberposts' => 1,
                  'post_status' => 'publish',
                  'post_type' => $input['xport']['post-type'],
                  'title' => $input['xport']['parent-post'],
               )
            );

            if ($log) usi::log('$args=', $args, '\n$post_parent=', $parent_post);

            if (!empty($parent_post[0]->ID)) {

               $posts = get_posts(
                  $args = array(
                     'numberposts' => -1,
                     'post_parent' => $parent_post[0]->ID,
                     'post_status' => 'publish',
                     'post_type' => $input['xport']['post-type'],
                  )
               );

               if ($log) usi::log('$args=', $args, '\n$posts=', $posts);

               $text = null;

               foreach ($posts as $post) {

                  $text .= '"' . $post->post_title . '":' . PHP_EOL . '{' . PHP_EOL;

                  $text .= str_replace('","', '",' . PHP_EOL . '"', trim(trim($post->post_content, '}'), '{'));

                  $text .= PHP_EOL . '}' . PHP_EOL;

               }

               $input['xport']['content'] = $text;

            }

         }
      }

      return($input);

   } // fields_sanitize();

   function sections() {

      USI_WordPress_Solutions_Popup_Iframe::build(
         array(
            'close'  => __('Close', USI_WordPress_Solutions::TEXTDOMAIN),
            'height' => '640px',
            'id'     => 'usi-popup-phpinfo',
            'width'  => '980px',
         )
      );

      $phpinfo_anchor = USI_WordPress_Solutions_Popup_Iframe::link(
         array(
            'id'     => 'usi-popup-phpinfo',
            'iframe' => plugins_url(null, __FILE__) . '/usi-wordpress-solutions-phpinfo-scan.php',
            'link'   => array('text' => 'phpinfo()'),
            'tip'    => __('Display PHP information', USI_WordPress_Solutions::TEXTDOMAIN),
            'title'  => 'phpinfo()',
         )
      );

      $current         = error_reporting();

      $error_constants = array(
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
      );

      $php_reporting   = '0x' . strtoupper(dechex($current)) . ' = ';
      $separator       = '';

      foreach ($error_constants as $key => $value) {
         if ($value == ($current & $value)) {
            $php_reporting .= $separator . $key;
            $separator      = ' | ';
         }
      }

      $pcre_backtrack_limit = ini_get('pcre.backtrack_limit');

      $mpdf_pcre_limit      = (int)(USI_WordPress_Solutions::$options['admin-limits']['mpdf-pcre-limit'] ?? $pcre_backtrack_limit);

      $diagnostics = new USI_WordPress_Solutions_Diagnostics($this, 
         array(
            'DEBUG_INIT' => array(
               'value' => USI_WordPress_Solutions::DEBUG_INIT,
               'notes' => 'Log USI_WordPress_Solutions_Settings::action_admin_init() method.',
            ),
            'DEBUG_OPTIONS' => array(
               'value' => USI_WordPress_Solutions::DEBUG_OPTIONS,
               'notes' => 'Log USI_WordPress_Solutions::$options.',
            ),
            'DEBUG_RENDER' => array(
               'value' => USI_WordPress_Solutions::DEBUG_RENDER,
               'notes' => 'Log USI_WordPress_Solutions_Settings::fields_render() method.',
            ),
            'DEBUG_UPDATE' => array(
               'value' => USI_WordPress_Solutions::DEBUG_UPDATE,
               'notes' => 'Log USI_WordPress_Solutions_Update methods.',
            ),
            'DEBUG_XPORT' => array(
               'value' => USI_WordPress_Solutions::DEBUG_XPORT,
               'notes' => 'Log USI_WordPress_Solutions xport functionality.',
            ),
         )
      );

      $sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'sections_header', '    <p>' . __('The WordPress-Solutions plugin is used by many Universal Solutions plugins and themes to simplify the ' .
         'implementation of WordPress functionality. Additionally, you can place all of the Universal Solutions settings pages ' .
         'at the end of the Settings sub-menu, or you can sort the Settings sub-menu alphabetically or not at all.', 
          USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL),
            'label' => __('Preferences', USI_WordPress_Solutions::TEXTDOMAIN), 
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => array(
               'menu-sort' => array(
                  'type' => 'radio', 
                  'label' => 'Settings Menu Sort Option',
                  'choices' => array(
                     array(
                        'value' => 'none', 
                        'label' => true, 
                        'notes' => __('No sorting', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'alpha', 
                        'label' => true, 
                        'notes' => __('Alphabetical sorting selection', USI_WordPress_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'usi', 
                        'label' => true, 
                        'notes' => __('Sort Universal Solutions settings and move to end of menu', USI_WordPress_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>No sorting</b>.',
               ), // menu-sort;
               'admin-notice' => array(
                  'f-class' => 'large-text', 
                  'rows' => 2,
                  'type' => 'textarea', 
                  'label' => 'Admin Notice',
               ),
            ),
         ), // preferences;

         'admin-options' => array(
            'title' => __('Administrator Options', USI_WordPress_Solutions::TEXTDOMAIN),
            'not_tabbed' => 'preferences',
            'settings' => array(
               'history' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable Historian',
                  'notes' => 'The system historian records user, configuration and update events in the system database.',
               ),
               'impersonate' => array(
                  'type' => 'checkbox', 
                  'label' => 'Enable User Switching',
                  'notes' => 'Enables administrators to impersonate another WordPress user.',
               ),
               'pass-reset' => array(
                  'type' => 'checkbox', 
                  'label' => 'Remove Password Reset',
                  'notes' => 'Remove the password reset link option from the row actions in the user display.',
               ),
               'options_php' => array(
                  'type' => 'html', 
                  'html' => '<a href="options.php" title="Semi-secret settings on options.php page">options.php</a>',
                  'label' => 'Semi-Secret Settings',
               ),
            ),
         ), // admin-options;

         'admin-limits' => array(
            'title' => __('Other Options and Limits', USI_WordPress_Solutions::TEXTDOMAIN),
            'not_tabbed' => 'preferences',
            'settings' => array(
               'mpdf-pcre-limit' => array(
                  'f-class' => 'regular-text', 
                  'label' => 'pcre.backtrack_limit',
                  'notes' => 'This option only affects the <i>pcre.backtrack_limit</i> when doing a PDF download, the current system wide limit is ' . $pcre_backtrack_limit . '.',
                  'type' => 'number', 
                  'value' => $mpdf_pcre_limit, 
               ),
            ),
         ), // admin-limits;

         'capabilities' => new USI_WordPress_Solutions_Capabilities($this),

         'diagnostics' => $diagnostics,

         'illumination' => array(
            'title' => 'Illumination',
            'not_tabbed' => 'diagnostics',
            'settings' => array(
               'php-report' => array(
                  'type' => 'html', 
                  'html' => $php_reporting,
                  'label' => 'Error Reporting',
               ),
               'info-php' => array(
                  'type' => 'html', 
                  'html' => $phpinfo_anchor,
                  'label' => 'Information - PHP',
               ),
               'info-site' => array(
                  'type' => 'html', 
                  'html' => '<a href="' . admin_url('site-health.php?tab=debug') . '">Site Health - Info</a>',
                  'label' => 'Information - Site',
               ),
               'active-users' => array(
                  'type' => 'html', 
                  'html' => '<a href="admin.php?page=usi-wordpress-solutions-user-sessions">Users Logged In</a>',
                  'label' => 'Users Currently Logged In',
               ),
               'visible-grid' => array(
                  'type' => 'checkbox', 
                  'label' => 'Visable Grid Borders',
               ),
            ),
         ), // illumination;

         'limits-values' => array(
            'title' => 'Limits and Values',
            'not_tabbed' => 'diagnostics',
            'settings' => array(
               'php-memory-limit' => array(
                  'html' => ini_get('memory_limit'),
                  'label' => 'PHP memory_limit',
                  'notes' => 'The maximum amount of memory in bytes that an individual script is allowed to allocate.',
                  'type' => 'html', 
               ),
               'disable-wp-cron' => array(
                  'html' => defined('DISABLE_WP_CRON') ? (DISABLE_WP_CRON ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'DISABLE_WP_CRON',
                  'notes' => 'When true the standard WordPress cron handling is disabled.',
                  'type' => 'html', 
               ),
               'wp-debug' => array(
                  'html' => defined('WP_DEBUG') ? (WP_DEBUG ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG',
                  'notes' => 'When true triggers "debug" mode throughout WordPress.',
                  'type' => 'html', 
               ),
               'wp-debug-log' => array(
                  'html' => defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG_LOG',
                  'notes' => 'When true and when WP_DEBUG_DISPLAY is false, WordPress writes errors to the <i>debug.log</i> file inside the <i>wp-content</i> folder.',
                  'type' => 'html', 
               ),
               'wp-debug-display' => array(
                  'html' => defined('WP_DEBUG_DISPLAY') ? (WP_DEBUG_DISPLAY ? 'TRUE' : 'false') : 'undefined',
                  'label' => 'WP_DEBUG_DISPLAY',
                  'notes' => 'When true WordPress shows errors and warnings on the page as they are generated.',
                  'type' => 'html', 
               ),
               'wp-memory-limit' => array(
                  'html' => defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 'undefined',
                  'label' => 'WP_MEMORY_LIMIT',
                  'notes' => 'The maximum amount of memory that can be consumed by PHP.',
                  'type' => 'html', 
               ),
            ),
         ), // limits-values;

         'updates' => new USI_WordPress_Solutions_Updates($this),

         'versions' => array(
            'label' => __('Versions', USI_WordPress_Solutions::TEXTDOMAIN), 
            'footer_callback' => array($this, 'sections_footer', 'Execute Versions'),
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => array(
               'mode' => array(
                  'f-class' => 'large-text', 
                  'label' => 'Select Functionality',
                  'options' => array(
                     array(0 => 'compare', 1 => 'Compare version information'),
                     array(0 => 'export', 1 => 'Export current version information'),
                     array(0 => 'import', 1 => 'Import source version information'),
                  ),
                  'type' => 'select', 
               ),
            ),
         ), // versions;

         'xport' => array(
            'label' => __('eXporter', USI_WordPress_Solutions::TEXTDOMAIN), 
            'header_callback' => array($this, 'sections_header', '    <p>' . __('Export custom post content.', USI_WordPress_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL),
            'footer_callback' => array($this, 'sections_footer', 'eXport Posts'),
            'localize_labels' => 'yes',
            'localize_notes' => 3, // <p class="description">__()</p>;
            'settings' => array(
               'post-type' => array(
                  'f-class' => 'regular-text', 
                  'type' => 'text', 
                  'label' => 'Post Type',
               ),
               'parent-post' => array(
                  'f-class' => 'large-text', 
                  'type' => 'text', 
                  'label' => 'Parent Post',
               ),
               'content' => array(
                  'f-class' => 'large-text', 
                  'rows' => 6,
                  'type' => 'textarea', 
                  'label' => 'Post Content',
               ),
            ),
         ), // xport;

      );

      if (empty($this->options['versions']['mode'])) {
      } else if ('compare' == $this->options['versions']['mode']) {
         require_once('usi-wordpress-solutions-versions-show.php');
         $import = !empty($this->options['versions']['import']) ? $this->options['versions']['import'] : null;
         $sections['versions']['settings']['compare'] = array(
            'html' => USI_WordPress_Solutions_Versions_Show::show($import),
            'type' => 'html', 
         );
         $sections['versions']['settings']['import'] = array(
            'type' => 'hidden', 
         );
      } else if ('export' == $this->options['versions']['mode']) {
         $sections['versions']['settings']['export'] = array(
            'f-class' => 'large-text', 
            'rows' => 16,
            'type' => 'textarea', 
            'label' => 'Export Current Installation',
         );
         $sections['versions']['settings']['import'] = array(
            'type' => 'hidden', 
         );
      } else if ('import' == $this->options['versions']['mode']) {
         $sections['versions']['settings']['import'] = array(
            'f-class' => 'large-text', 
            'rows' => 16,
            'type' => 'textarea', 
            'label' => 'Import Source Installation',
         );
      }

      return($sections);

   } // sections();

} // Class USI_WordPress_Solutions_Settings_Settings;

new USI_WordPress_Solutions_Settings_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>