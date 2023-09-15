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

final class USI_WordPress_Solutions_Admin {

   const VERSION = '2.16.0 (2023-09-15)';

   private static $jquery = null;
   private static $script = null;

   private function __construct() {
   } // __construct();

   public static function _init() {

      if (is_admin()) {

         global $pagenow;
         if ('admin.php' == $pagenow) {
            if (!empty($_GET['page']) && ('usi-wordpress-solutions-user-sessions' == $_GET['page'])) {
               add_action('admin_head', ['USI_WordPress_Solutions_User_Sessions', 'action_admin_head']);
            }
            add_action('admin_menu', ['USI_WordPress_Solutions_User_Sessions', 'action_admin_menu']);
         }

         if (!defined('WP_UNINSTALL_PLUGIN')) {
            add_action('init', 'add_thickbox');
            if (!empty(USI_WordPress_Solutions::$options['admin-options']['history'])) {
               USI_WordPress_Solutions_History::_init();
            }
            if (!empty(USI_WordPress_Solutions::$options['admin-options']['mailer'])) {
               add_action('phpmailer_init', ['USI_WordPress_Solutions_Mailer', 'action_phpmailer_init'], 10, 1);
            }
            USI_WordPress_Solutions_Install::init();
            new USI_WordPress_Solutions_Settings_Settings();
         }

         add_action('admin_print_footer_scripts', [__CLASS__, 'action_admin_print_footer_scripts']);

         if (!empty(USI_WordPress_Solutions::$options['admin-options']['visual-edit'])) {
            add_filter('user_can_richedit', '__return_false');
         }

      }

   } // _init();

   public static function action_admin_print_footer_scripts() {

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

} // Class USI_WordPress_Solutions_Admin;

// --------------------------------------------------------------------------------------------------------------------------- // ?>