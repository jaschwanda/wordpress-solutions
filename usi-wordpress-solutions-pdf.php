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

class USI_WordPress_Solutions_PDF {

   const VERSION = '2.13.0 (2022-02-22)';

   public static $css_buffer  = null;

   public static $html_buffer = null;

   public static $mode        = null;

   public static $options     = array();

   public static function init($options = array()) {

      self::$options = $options;

      self::$mode    = $options['mode'] ?? null;

      if ('inline' == self::$mode) {

         ob_start(array(__CLASS__, 'ob_start_callback'));

         add_action('shutdown', array(__CLASS__, 'action_shutdown'));

      }

   } // init();

   public static function action_shutdown() { 

      require_once(__DIR__ . '/mPDF/vendor/autoload.php');

      $reporting_options = error_reporting(0);

      try {

         $mpdf = new \Mpdf\Mpdf();

         if (!empty(self::$options['header'])) $mpdf->SetHTMLHeader(self::$options['header']);

         if (!empty(self::$options['footer'])) $mpdf->SetHTMLFooter(self::$options['footer']);

         if (empty(self::$css_buffer)) self::$css_buffer = apply_filters('usi_wordpress_pdf_css', null);

         if (!empty(self::$css_buffer)) $mpdf->WriteHTML(self::$css_buffer, \Mpdf\HTMLParserMode::HEADER_CSS);

         if (!empty(self::$options['mark_beg']) && !empty(self::$options['mark_end'])) {

            $beg_html = strpos(self::$html_buffer, self::$options['mark_beg']);
            $beg_size = strlen(self::$options['mark_beg']);

            $end_html = strpos(self::$html_buffer, self::$options['mark_end']);
            $end_size = strlen(self::$options['mark_end']);

            if ($beg_html && $end_html) {
               self::$html_buffer = substr(self::$html_buffer, $beg_html + $beg_size, $end_html - $beg_html - $end_size);
            } else {
               self::$html_buffer = '<p>Could not find PDF markers in given page.</p>';
            }

         }

         $pcre_backtrack_limit = ini_get('pcre.backtrack_limit');

         if (USI_WordPress_Solutions::$options['admin-limits']['mpdf-pcre-limit'] > $pcre_backtrack_limit) {
            ini_set('pcre.backtrack_limit', USI_WordPress_Solutions::$options['admin-limits']['mpdf-pcre-limit']);
         }

         $mpdf->WriteHTML(self::$html_buffer, \Mpdf\HTMLParserMode::HTML_BODY);

         if ('inline' == self::$mode) $mpdf->Output(self::$options['file'] ?? null, \Mpdf\Output\Destination::INLINE);

      } catch (\Mpdf\MpdfException $e) {

         $error = 'For ' . self::$options['file'] . ' the PDF conversion failed:' . $e->getMessage();

         usi::log('mPDF:', $error);

         try {

            $mpdf->WriteHTML($error, \Mpdf\HTMLParserMode::HTML_BODY);

            if ('inline' == self::$mode) $mpdf->Output(self::$options['file'] ?? null, \Mpdf\Output\Destination::INLINE);

         } catch (\Mpdf\MpdfException $e) {

            $error = 'For ' . self::$options['file'] . ' the PDF error log failed:' . $e->getMessage();

            usi::log('mPDF:', $error);

         }

      }

      error_reporting($reporting_options);

   } // action_shutdown();

   public static function ob_start_callback(string $buffer, int $phase) {

      if (!self::$html_buffer) self::$html_buffer = $buffer;

      // Return nothing otherwise the mPDF functions won't write out the PDF;
      // But we may want to return buffer for logging or debugging;
      return(empty(self::$options['ob_start_return']) ? null : $buffer); 

   } // ob_start_callback();

   public static function set_css(string $buffer) {

      self::$css_buffer = $buffer;

   } // set_css();

   public static function set_html(string $buffer) {

      self::$html_buffer = $buffer;

   } // set_html();

} // Class USI_WordPress_Solutions_PDF;

// --------------------------------------------------------------------------------------------------------------------------- // ?>