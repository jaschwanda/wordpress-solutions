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

require_once('usi-wordpress-solutions-bootstrap.php');

if (!in_array('administrator', wp_get_current_user()->roles)) die('Accesss not allowed.');

final class USI_WordPress_Solutions_Capabilities_List {

   const VERSION = '2.14.1 (2022-08-10)';

   private function __construct() {
   } // __construct();

   public static function list() {

      $query = $_SERVER['QUERY_STRING'];

      die('<table>' . '<tr><td>' . $query . '</td></tr>' . '</table>');

   } // versions();

} // Class USI_WordPress_Solutions_Capabilities_List;

USI_WordPress_Solutions_Capabilities_List::list();

// --------------------------------------------------------------------------------------------------------------------------- // ?>