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

/* 

This class is the start of a form design class and it must be included by the child class that actually displays the form layout if you want to create a form designer.

*/

require_once('usi-wordpress-solutions-log.php');

class USI_WordPress_Solutions_Settings_Layout {

   const VERSION = '2.14.1 (2022-08-10)';

   const FILE_ID = 'usi-wordpress-settings-layout';

   function __construct() {

      add_action('admin_menu', array($this, 'action_admin_menu'));

   } // __construct();

   function action_admin_menu() {

      add_menu_page(
         __('Form Layout', USI_WordPress_Solutions::TEXTDOMAIN), // Page <title/> text;
         __('Form Layout', USI_WordPress_Solutions::TEXTDOMAIN), // Sidebar menu text; 
         'manage_options', // Capability required to enable page;
         'form-layout', // Menu page slug name;
         array($this, 'display_layout'), // Render page callback;
         'dashicons-schedule', // URL of icon for menu item;
         3 // Position in menu order;
      );

   } // action_admin_menu();

   public function display_layout() {

      $elements = null;

      if ('Process' == ($_REQUEST['submit'] ?? null)) {
         if ((UPLOAD_ERR_OK == $_FILES[SELF::FILE_ID]['error']) && is_uploaded_file($_FILES[SELF::FILE_ID]['tmp_name'])) {
            $elements = file_get_contents($_FILES[SELF::FILE_ID]['tmp_name']); 
         }
      }

      echo '<form id="usi-wordpress-solutions-layout-form" action="http://oredkbd222bd01.rad.rutgers.edu/wp-admin/admin.php?page=form-layout" enctype="multipart/form-data" method="post">' . PHP_EOL;
      echo '<h1>Form Layout</h1>' . PHP_EOL;
      echo '<input name="' . SELF::FILE_ID . '" type="file" value="">' . PHP_EOL;
      echo '<input name="submit" id="submit"  title="Process" type="submit" value="Process">' . PHP_EOL;
      echo '</form>' . PHP_EOL;

      if (!empty($elements)) $this->process_elements(explode(PHP_EOL, $elements));


   } // display_layout();

   protected function process_elements(array $elements) {

      foreach ($elements as $element) {
         usi::log('$element=', $element);
      }

   } // process_elements()

} // Class USI_WordPress_Solutions_Settings_Layout;

// --------------------------------------------------------------------------------------------------------------------------- // ?>