<?php // ------------------------------------------------------------------------------------------------------------------------ //

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2020 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Versions_Scan {

   const VERSION = '2.11.4 (2021-05-06)';

   private function __construct() {
   } // __construct();

   private static function scan($path) {
      $files = scandir($path);
      $html  = '';
      foreach ($files as $file) {
         if ('.usi-ignore' == $file) break;
         $full_path = $path . DIRECTORY_SEPARATOR . $file;
         if (('.' == $file) || ('..' == $file)) {
         } else if (is_dir($full_path)) {
            $html .= self::scan($full_path);
         } else {
            $contents = file_get_contents($full_path);
            $status   = preg_match('/(V|v)(E|e)(R|r)(S|s)(I|i)(O|o)(N|n)\s*(=|:)\s*(\')?([(0-9\.\s\-\)]*)/', $contents, $matches);
            if (!empty($matches[10])) $html .= '<tr><td>' . $file . ' &nbsp; &nbsp; </td><td>' . $matches[10] . '</td></tr>';
         }
      }
      return($html);
   } // scan();

   public static function versions() {
      $style = '<style>td{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,' .
         '"Helvetica Neue",sans-serif; font-size: 13px;}</style>';
      foreach ($_COOKIE as $key => $value) {
         if (substr($key, 0, 20) == 'wordpress_logged_in_') {
            die($style . '<table>' . self::scan(explode('?', urldecode($_SERVER['QUERY_STRING']))[0]) . '</table>');
         }
      }
      die('Accesss not allowed.');
   } // versions();

} // Class USI_WordPress_Solutions_Versions_Scan;

USI_WordPress_Solutions_Versions_Scan::versions();

// --------------------------------------------------------------------------------------------------------------------------- // ?>