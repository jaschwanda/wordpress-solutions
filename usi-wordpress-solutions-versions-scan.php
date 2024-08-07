<?php // ------------------------------------------------------------------------------------------------------------------------ //

require_once 'usi-wordpress-solutions-bootstrap.php';

if (!in_array('administrator', wp_get_current_user()->roles)) die('Accesss not allowed.');

final class USI_WordPress_Solutions_Versions_Scan {

   const VERSION = '2.16.0 (2023-09-15)';

   private function __construct() {
   } // __construct();

   private static function scan($path) {
      @ $files = scandir($path);
      $html    = '';
      if (!empty($files)) {
         foreach ($files as $file) {
            if ('.usi-ignore' == $file) break;
            $full_path = $path . DIRECTORY_SEPARATOR . $file;
            if (('.' == $file) || ('..' == $file)) {
            } else if (is_dir($full_path)) {
               if ('.git' == $file) continue;
               $html .= self::scan($full_path);
            } else {
               $contents = file_get_contents($full_path);
               $status   = preg_match('/(V|v)(E|e)(R|r)(S|s)(I|i)(O|o)(N|n)\s*(=|:)\s*(\')?([(0-9\.\s\-\)]*)/', $contents, $matches);
               if (!empty($matches[10])) $html .= '<tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $matches[10] . '</td></tr>';
            }
         }
      }
      return $html;
   } // scan();

   public static function versions() {

      $style = '<style>td{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,' .
         '"Helvetica Neue",sans-serif; font-size: 13px;}</style>';

      die($style . '<table>' . self::scan(explode('?', urldecode($_SERVER['QUERY_STRING']))[0]) . '</table>');

   } // versions();

} // Class USI_WordPress_Solutions_Versions_Scan;

USI_WordPress_Solutions_Versions_Scan::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>