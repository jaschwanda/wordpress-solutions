<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Static {

   const VERSION = '2.16.0 (2023-09-15)';

   private static $calls_action_admin_head = 0;

   private function __construct() {
   } // __construct();

   public static function action_admin_head($css = '') {
      if (self::$calls_action_admin_head++) {
         if ($css) echo '<style>' . PHP_EOL . $css . '</style>' . PHP_EOL;
      } else {
         echo 
         '<style>' . PHP_EOL .
         '.form-table td{padding-bottom:2px; padding-top:2px;} /* 15px; */' . PHP_EOL .
         '.form-table th{padding-bottom:7px; padding-top:7px;} /* 20px; */' . PHP_EOL .
         'h2{margin-bottom:0.1em; margin-top:2em;} /* 1em; */' . PHP_EOL;
         if ($css) echo $css;
         if (!empty(USI_WordPress_Solutions::$options['illumination']['visible-grid'])) echo
         '.form-table,.wp-list-table{border:solid 4px yellow;}' . PHP_EOL .
         '.form-table td,.form-table th,.wp-list-table td,.wp-list-table th{border:solid 2px yellow;}' . PHP_EOL .
         '.wrap{border:solid 1px green;}' . PHP_EOL;
         echo 
         '</style>' . PHP_EOL;
      }
   } // action_admin_head();

   // Make sure you have the get_hidden_columns() function in your WP_List_Table;
   public static function column_style($columns, $style = '') {

      $border = !empty(USI_WordPress_Solutions::$options['illumination']['visible-grid']) ? 'border:solid 1px yellow; ' : '';

      $space  = $style ? ' ' : '';

      $hidden = get_hidden_columns(get_current_screen());

      foreach ($hidden as $hide) unset($columns[$hide]);

      $total  = 0; foreach ($columns as $width) if (!is_array($width)) $total += $width;

      $html   = '<style>' . PHP_EOL;

      foreach ($columns as $name => $value) { 
         if (is_array($value)) {
            $width    = !empty($value['width']) ? 'width:' . $value['width'] . '!important;' : '';
            $ellipsis = !empty($value['ellipsis']) ? 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;' : '';
            $html    .= ".wp-list-table .column-$name{{$border}{$width}{$ellipsis}}" . PHP_EOL;
         } else {
            $percent  = number_format(100 * $value / $total, 1);
            $html    .= ".wp-list-table .column-$name{{$border}width:$percent%!important;$space$style}" . PHP_EOL;
         }
      }

      return $html . '</style>' . PHP_EOL;

   } // column_style();

   public static function divider($indent, $text = '') {
      if ($length = strlen($text)) {
         $text    = ' ' . $text . ' ';
         $length += 2;
      }
      return '<!--' . $text . str_repeat('-', 121 - $length - $indent) . '>' . PHP_EOL;
   } // divider();

   public static function is_int($variable) {
      if (is_integer($variable)) return $variable;
      if (!is_string($variable) || !ctype_digit($variable)) return false;
      return intval($variable);
   } // is_int();

   public static function remove_directory($directory) { 

      // This function can be called with a bogus or empty directory and it will fail silently;

      if (is_dir($directory)) { 

         @ $objects = scandir($directory);

         foreach ($objects as $object) { 

            if (('.' != $object) && ('..' != $object)) { 

               if (is_dir($directory . DIRECTORY_SEPARATOR . $object) && !is_link($directory . '/' . $object)) {

                  self::delete_folder($directory . DIRECTORY_SEPARATOR . $object);

               } else {

                 @ unlink($directory . DIRECTORY_SEPARATOR . $object); 

               }

            } 

         }

      }

      @ rmdir($directory); 

   } // remove_directory();

   public static function url() {
      return 'http' . (is_ssl() ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   } // this_url();

} // Class USI_WordPress_Solutions_Static;

// --------------------------------------------------------------------------------------------------------------------------- // ?>