<?php // ------------------------------------------------------------------------------------------------------------------------ //

require_once 'usi-wordpress-solutions-bootstrap.php';

if (!in_array('administrator', wp_get_current_user()->roles)) die('Accesss not allowed.');

final class USI_WordPress_Solutions_Capabilities_List {

   const VERSION = '2.16.0 (2023-09-15)';

   private function __construct() {
   } // __construct();

   public static function list() {

      $query = $_SERVER['QUERY_STRING'];

      die('<table>' . '<tr><td>' . $query . '</td></tr>' . '</table>');

   } // versions();

} // Class USI_WordPress_Solutions_Capabilities_List;

USI_WordPress_Solutions_Capabilities_List::list();

// --------------------------------------------------------------------------------------------------------------------------- // ?>