<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Description:       The WordPress-Solutions plugin simplifys the implementation of WordPress functionality and is used by many Universal Solutions plugins and themes. The WordPress-Solutions plugin is developed and maintained by Universal Solutions.
Donate link:       https://www.usi2solve.com/donate/wordpress-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md
Plugin Name:       WordPress-Solutions
Plugin URI:        https://github.com/jaschwanda/wordpress-solutions
Requires at least: 5.0
Requires PHP:      7.0.0
Tested up to:      5.3.2
Text Domain:       usi-wordpress-solutions
Version:           2.14.3
*/

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

// Settings pages do not have to add admin notices on success, custom settings pages do;
// Un-activated plugin builds settings page;
// https://dev.to/lucagrandicelli/why-isadmin-is-totally-unsafe-for-your-wordpress-development-1le1

require_once('usi-wordpress-solutions-diagnostics.php');
require_once('usi-wordpress-solutions-log.php');

final class USI_WordPress_Solutions {

   const VERSION = '2.14.3 (2022-10-05)';

   const NAME       = 'WordPress-Solutions';
   const PREFIX     = 'usi-wordpress';
   const TEXTDOMAIN = 'usi-wordpress-solutions';

   const DEBUG_OFF      = 0x17000000;
   const DEBUG_INIT     = 0x17000001;
   const DEBUG_MAILINIT = 0x17000002;
   const DEBUG_MAILDEQU = 0x17000004;
   const DEBUG_OPTIONS  = 0x17000008;
   const DEBUG_RENDER   = 0x17000010;
   const DEBUG_SMTP     = 0x17000020;
   const DEBUG_UPDATE   = 0x17000040;
   const DEBUG_XFER     = 0x17000080;

   private static $jquery = null;
   private static $script = null;

   public static $capabilities = array(
      'impersonate-user' => 'Impersonate User|administrator',
   );

   public static $options = array();

   function __construct() {

      if (empty(self::$options)) {
         $defaults['admin-options']['history']     = false;
         $defaults['admin-options']['mailer']      = false;
         $defaults['preferences']['menu-sort']     = 'no';
         $defaults['illumination']['visible-grid'] = false;
         self::$options = get_option(self::PREFIX . '-options', $defaults);
      }
      if (!empty(self::$options['admin-options']['history'])) {
         require_once('usi-wordpress-solutions-history.php');
      }
      if (!empty(self::$options['admin-options']['mailer'])) {
         require_once('usi-wordpress-solutions-mailer.php');
      }

      $log  = USI_WordPress_Solutions_Diagnostics::get_log(self::$options);

      if (self::DEBUG_OPTIONS == (self::DEBUG_OPTIONS & $log)) usi::log('$options=', self::$options);

      if (is_admin()) {

         global $pagenow;
         if ('admin.php' == $pagenow) {
            require_once('usi-wordpress-solutions-user-sessions.php');
         }

         if (!defined('WP_UNINSTALL_PLUGIN')) {
            add_action('init', 'add_thickbox');
            require_once('usi-wordpress-solutions-install.php');
            require_once('usi-wordpress-solutions-settings-settings.php');
            if (!empty(USI_WordPress_Solutions::$options['updates']['git-update'])) {
               require_once('usi-wordpress-solutions-update.php');
               new USI_WordPress_Solutions_Update_GitHub(__FILE__, 'jaschwanda', 'wordpress-solutions', null, !empty(USI_WordPress_Solutions::$options['updates']['force-update']));
            }
         }

      }

      add_action('admin_print_footer_scripts', array($this, 'action_admin_print_footer_scripts'));

   } // __construct();

   public function action_admin_print_footer_scripts() {

      if (self::$script) echo self::$script;

      if (self::$jquery) {

         echo PHP_EOL
         . '<script> ' . PHP_EOL
         . 'jQuery(document).ready(' . PHP_EOL
         . '   function($) {' . PHP_EOL
         . str_replace(PHP_EOL, PHP_EOL . '      ', self::$jquery) . PHP_EOL
         . '   } // function($);' . PHP_EOL
         . '); // jQuery(document).ready(' . PHP_EOL
         . '</script>' . PHP_EOL
         ;

      }

   } // action_admin_print_footer_scripts();

   public static function admin_footer_jquery($jquery) {

      self::$jquery .= PHP_EOL . $jquery;

   } // admin_footer_jquery();

   public static function admin_footer_script($script) {

      self::$script .= PHP_EOL . $script;

   } // admin_footer_script();

} // Class USI_WordPress_Solutions;

new USI_WordPress_Solutions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>