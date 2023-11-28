<?php // ------------------------------------------------------------------------------------------------------------------------ //

final class USI_WordPress_Solutions_Uninstall {

   const VERSION = '2.16.0 (2023-09-15)';

   private function __construct() {
   } // __construct();

   static function uninstall($prefix) {

      global $wpdb;

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

      if ($prefix) {

         $results = $wpdb->get_results('SELECT option_name FROM ' . $wpdb->prefix . 
            'options WHERE (option_name LIKE "' . $prefix . '-options%")');
         foreach ($results as $result) {
            delete_option($result->option_name);
         }

         $results = $wpdb->get_results('SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 
            'usermeta WHERE (meta_key LIKE "' . $wpdb->prefix . $prefix . '-options%")');
         foreach ($results as $result) {
            delete_metadata('user', null, $result->meta_key, null, true);
         }

      }

   } // uninstall();

} // Class USI_WordPress_Solutions_Uninstall;

// --------------------------------------------------------------------------------------------------------------------------- // ?>