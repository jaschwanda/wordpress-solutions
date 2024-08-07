<?php // ------------------------------------------------------------------------------------------------------------------------ //

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2023 by Jim Schwanda.
*/

final class USI_WordPress_Solutions_Logged_In {

   const VERSION = '2.16.0 (2023-09-15)';

   private function __construct() {
   } // __construct();

   public static function logged_in() {
      foreach ($_COOKIE as $key => $value) {
         if (substr($key, 0, 20) == 'wordpress_logged_in_') return;
      }
      die('Accesss not allowed.');
   } // versions();

} // Class USI_WordPress_Solutions_Logged_In;

USI_WordPress_Solutions_Logged_In::logged_in();

// --------------------------------------------------------------------------------------------------------------------------- // ?>