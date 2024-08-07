<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Copyright:         2023 by Jim Schwanda.
Description:       The WordPress-Solutions plugin simplifys the implementation of WordPress functionality and is used by many Universal Solutions plugins and themes. The WordPress-Solutions plugin is developed and maintained by Universal Solutions.
Donate link:       https://www.usi2solve.com/donate/wordpress-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md
Plugin Name:       WordPress-Solutions
Plugin URI:        https://github.com/jaschwanda/wordpress-solutions
Text Domain:       usi-wordpress-solutions
Version:           2.16.3
Warranty:          This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// Settings pages do not have to add admin notices on success, custom settings pages do;
// Un-activated plugin builds settings page;
// https://dev.to/lucagrandicelli/why-isadmin-is-totally-unsafe-for-your-wordpress-development-1le1

final class USI_WordPress_Solutions {

   const VERSION    = '2.16.3 (2023-11-27)';

   const NAME       = 'WordPress-Solutions';
   const PREFIX     = 'usi-wordpress';
   const TEXTDOMAIN = 'usi-wordpress-solutions';

   const DEBUG_OFF      = 0x17000000;
   const DEBUG_INIT     = 0x17000001;
   const DEBUG_MAILINIT = 0x17000002;
   const DEBUG_MAILDEQU = 0x17000004;
   const DEBUG_OPTIONS  = 0x17000008;
   const DEBUG_PDF      = 0x17000010;
   const DEBUG_RENDER   = 0x17000020;
   const DEBUG_SMTP     = 0x17000040;
   const DEBUG_XFER     = 0x17000080;

   private static $emails = 0;

   public static $capabilities = [
      'impersonate-user' => 'Impersonate User|administrator',
   ];

   public static $options = [];

   private function __construct() {
   } // __construct();

   public static function _init() {

      if (empty(self::$options)) {
         $defaults['admin-options']['history']     =
         $defaults['admin-options']['mailer']      =
         $defaults['illumination']['visible-grid'] = false;
         $defaults['preferences']['e-mail-cloak']  = '';
         $defaults['preferences']['menu-sort']     = 'no';
         self::$options = get_option(self::PREFIX . '-options', $defaults);
      }

      $log  = USI_WordPress_Solutions_Diagnostics::get_log(self::$options);

      if (self::DEBUG_OPTIONS == (self::DEBUG_OPTIONS & $log)) usi::log('$options=', self::$options);

      if (is_admin()) USI_WordPress_Solutions_Admin::_init();

      if (!empty(self::$options['preferences']['e-mail-cloak'])) {
         add_shortcode(self::$options['preferences']['e-mail-cloak'], [__CLASS__, 'shortcode_email']);
      }

      if (!empty(self::$options['preferences']['custom-code'])) {
         add_shortcode(self::$options['preferences']['custom-code'], [__CLASS__, 'shortcode_custom']);
      }

   } // _init();
 
   public static function action_wp_footer() {
      echo PHP_EOL . '    <script>'
      . 'function usi_link_validate(e){'
      . "function a(o){while(o&&('a'!=o.nodeName.toLowerCase())){o=o.parentNode;}return(o);}"
      . 'function c(i){return(String.fromCharCode(i));}'
      . "function r(s){return(s.split('').map(c=>String.fromCharCode(c.charCodeAt(0)+(c.toLowerCase()<'n'?13:-13))).join(''));}"
      . 'e.preventDefault();'
      . 'let o=a(e.target);'
      . "let d=o.href.substring(8).replace(new RegExp('[\/]+$'),'');"
      . "let s=o.getAttribute('title');"
      . "let t=o.getAttribute('target');"
      . "window.location.href=c(109)+c(97)+c(105)+c(108)+c(116)+c(111)+c(58)+r(t)+c(64)+d+(s?'?subject='+s:'');"
      . 'return(false);'
      . '}'
      . '</script>' 
      . PHP_EOL
      ;
   } // action_wp_footer();

   public static function shortcode_custom($attr, $content = null) {

      $class  = $attr['class']  ?? null;
      $debug  = $attr['debug']  ?? false;
      $method = $attr['method'] ?? null;
      $print  = $attr['print']  ?? false;
      $static = $attr['static'] ?? false;

      try {

         if (!method_exists($class, $method)) {

            if (!class_exists($class)) {

               if ($debug && $print) return '{Class ' . $class . ' does not exist}';

            }

            if ($debug && $print) return '{method ' . $method . '() does not exist on class ' . $class . '}';

            return null;

         } else if ($static) {

            $output = $class::$method($attr, $content);

         } else {

            $object = new $class();

            $output = $object->$method($attr, $content);

         }
 
         if ($print) return $output;

      } catch (Exception $e) {

      }

      return null;

   } // shortcode_custom();

   public static function shortcode_email($attr, $content = null) {
      $email   = $attr['email'] ?? null;
      $parts   = explode('@', $email);
      if (1   >= count($parts)) return '[' . self::$options['preferences']['e-mail-cloak'] . ' ' . implode(',', $attr) . ']';
      $class   = empty($attr['class'])   ? null : ' class="' . $attr['class']   . '"';
      $id      = empty($attr['id'])      ? null : ' id="'    . $attr['id']      . '"';
      $style   = empty($attr['style'])   ? null : ' style="' . $attr['style']   . '"';
      $title   = empty($attr['subject']) ? null : ' title="' . $attr['subject'] . '"';
      $encode  = function($string) {
         $html = '';
         $size = strlen($string);
         $seed = max(3, $size / 3);
         for ($ith = 0; $ith < $size; $ith++) {
            $code  = '&#' . ord($string[$ith]) . ';';
            $html .= $code . ($ith % $seed ? '' : '<i style="display:none;">' . $code . '</i>');
         }
         return $html;
      };
      if (!self::$emails++) add_action('wp_footer', [__CLASS__, 'action_wp_footer'], 20);
      list($target, $domain) = $parts;
      $offset     = false;
      if (empty($content)) {
         $content = '{cloak}';
         $offset  = 0;
      } else {
         $offset  = strpos($content, '{cloak}');
      }
      if (false !== $offset) {
         $cloaked = $encode($target) . '&#64;' . $encode($domain);
         $content = substr_replace($content, $cloaked, $offset, 7);
      }
      return '<a' . $id . $class . ' href="https://' . $domain . '" onclick="usi_link_validate(event);"' . $style . ' target="' . str_rot13($target) . '"' . $title . '>' . $content . '</a>';

   } // shortcode_email();

} // Class USI_WordPress_Solutions;

USI_WordPress_Solutions::_init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>