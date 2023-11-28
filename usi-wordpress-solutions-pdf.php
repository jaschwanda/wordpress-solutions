<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

// https://mpdf.github.io/

class USI_WordPress_Solutions_PDF {

   const VERSION = '2.16.1 (2023-09-26)';

   public static $confidential = false;

   public static $css_buffer   = null;

   public static $file         = null;

   public static $html_buffer  = null;

   public static $inline       = null;

   public static $log          = false;

   public static $mode         = null;

   public static $options      = [];

   private static $version     = '8.0.0';

   public static function init($options = []) {

      $log           = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);

      self::$log     = !empty($options['log']) || (USI_WordPress_Solutions::DEBUG_PDF == (USI_WordPress_Solutions::DEBUG_PDF & $log));

      self::$file    = $options['file'] ?? null;

      self::$mode    = $options['mode'] ?? null;

      self::$inline  = ('inline' == self::$mode);

      self::$options = $options;

      if (self::$inline) {

         ob_start([__CLASS__, 'ob_start_callback']);

         add_action('shutdown', [__CLASS__, 'action_shutdown']);

      }

      if (self::$log) usi::log('$options=', $options);

   } // init();

   public static function action_shutdown() { 

      switch ($version = USI_WordPress_Solutions::$options['admin-limits']['mpdf-version'] ?? null) {
      case '8.1.4': 
         self::$version = $version;
         require_once __DIR__ . '/mPDF-' . $version . '/vendor/autoload.php';
         break;
      default:      
         require_once __DIR__ . '/mPDF/vendor/autoload.php';
      }

      $reporting_options = error_reporting(0);

      if (self::$log) usi::log('action_shutdown:begin:$version=', self::$version, ' $reporting_options=', $reporting_options);

      $mpdf    = null;

      try {

         $config = [];

         if (!empty(USI_WordPress_Solutions::$options['admin-limits']['mpdf-temp-dir'])) {
            $config['tempDir'] = USI_WordPress_Solutions::$options['admin-limits']['mpdf-temp-dir'];
         }

         $mpdf   = new \Mpdf\Mpdf($config);

         if(!$mpdf) throw new Exception('Cannot create PDF object.');

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

         if (self::$confidential) {
            $mpdf->SetWatermarkText('Confidential', 0.2);
            $mpdf->showWatermarkText = true;
         }

         $mpdf->WriteHTML(self::$html_buffer, \Mpdf\HTMLParserMode::HTML_BODY);

         self::output($mpdf);

         if (self::$log) usi::log('success');

      } catch (\Mpdf\MpdfException $e) {

         $error = 'For ' . self::$file . ' the PDF conversion failed:' . $e->getMessage();

         usi::log('mPDF:', $error);

         try {

            $mpdf->WriteHTML($error, \Mpdf\HTMLParserMode::HTML_BODY);

            self::output($mpdf);

         } catch (\Mpdf\MpdfException $e) {

            $error = 'For ' . self::$file . ' the PDF error log failed:' . $e->getMessage();

            usi::log('mPDF:', $error); echo $error;

         }

      } catch (exception $e) {

         $error = 'For ' . self::$file . ':exception:' . $e->getMessage();

         usi::log('mPDF:', $error); echo $error;

      }

      error_reporting($reporting_options);

      if (self::$log) usi::log('action_shutdown:end');

   } // action_shutdown();

   public static function ob_start_callback(string $buffer, int $phase) {

      if (self::$log) usi::log('$buffer=', $buffer, '\n$phase=', $phase);

      if (!self::$html_buffer) self::$html_buffer = $buffer;

      // Return nothing otherwise the mPDF functions won't write out the PDF;
      // But we may want to return buffer for logging or debugging;
      return empty(self::$options['ob_start_return']) ? null : $buffer;

   } // ob_start_callback();

   private static function output($mpdf) {

      switch (self::$version) {

      case '8.1.4': 
         if (self::$inline) {
            if ('download' == self::$options['output']) {
               $mpdf->OutputHttpDownload(self::$file);
            } else {
               $mpdf->OutputHttpInline();
            }
         }
         break;

      default:      

         if (self::$inline) $mpdf->Output(self::$file, \Mpdf\Output\Destination::INLINE);

      }

      if (self::$log) usi::log('return');

   } // output();

   public static function set_css(string $buffer) {

      if (self::$log) usi::log('$buffer=', $buffer);

      self::$css_buffer = $buffer;

   } // set_css();

   public static function set_html(string $buffer) {

      if (self::$log) usi::log('$buffer=', $buffer);

      self::$html_buffer = $buffer;

   } // set_html();

} // Class USI_WordPress_Solutions_PDF;

// --------------------------------------------------------------------------------------------------------------------------- // ?>