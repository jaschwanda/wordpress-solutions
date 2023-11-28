<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Versions {

   const VERSION = '2.16.0 (2023-09-15)';

   private static $built = false;

   private function __construct() {
   } // __construct();

   public static function link($link, $title, $version, $text_domain, $file) {

      if (!self::$built) {

         USI_WordPress_Solutions_Popup_Iframe::build(
            [
               'close'   => __('Close', $text_domain),
               'height' => '500px',
               'id'     => 'usi-popup-version',
               'width'  => '500px',
            ]
         );

         self::$built = true;

      }

      return
         USI_WordPress_Solutions_Popup_Iframe::link(
            [
               'id'     => 'usi-popup-version',
               'iframe' => plugins_url(null, __FILE__) . '/usi-wordpress-solutions-versions-scan.php?' . urlencode($file),
               'link'   => ['text' => $link],
               'tip'    => __('Display detailed version information', $text_domain),
               'title'  => $title . ' &nbsp; &nbsp; ' . __('Version', $text_domain) . ' ' . $version,
            ]
         )
         ;

   } // link();

} // Class USI_WordPress_Solutions_Versions;

// --------------------------------------------------------------------------------------------------------------------------- // ?>