<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Author:            Jim Schwanda
Author URI:        https://www.usi2solve.com/leader
Description:       The WordPress-Solutions plugin simplifys the implementation of WordPress functionality and is used by many Universal Solutions plugins and themes. The WordPress-Solutions plugin is developed and maintained by Universal Solutions.
Donate link:       https://www.usi2solve.com/donate/wordpress-solutions
License:           GPL-3.0
License URI:       https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md
Plugin Name:       WordPress-Solutions
Plugin URI:        https://github.com/jaschwanda/wordpress-solutions
Requires at least: 5.0
Requires PHP:      7.0.0
Tested up to:      5.3.2
Text Domain:       usi-wordpress-solutions
Version:           2.15.5
*/

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2023 by Jim Schwanda.
*/

// Settings pages do not have to add admin notices on success, custom settings pages do;
// Un-activated plugin builds settings page;
// https://dev.to/lucagrandicelli/why-isadmin-is-totally-unsafe-for-your-wordpress-development-1le1

require_once('usi-wordpress-solutions-diagnostics.php');

final class USI_WordPress_Solutions {

   const VERSION    = '2.15.5 (2023-07-07)';

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

      if (is_admin()) {
         require_once('usi-wordpress-solutions-admin.php');
      }

      if (!empty(self::$options['preferences']['e-mail-cloak'])) {
         add_shortcode(self::$options['preferences']['e-mail-cloak'], [__CLASS__, 'shortcode_email']);
      }

   } // _init();
 
   function action_wp_footer() {
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

   public static function shortcode_email($attr, $content = null) {
      if (!self::$emails++) add_action('wp_footer', [__CLASS__, 'action_wp_footer'], 20);
      $class   = empty($attr['class'])   ? null : ' class="' . $attr['class']   . '"';
      $id      = empty($attr['id'])      ? null : ' id="'    . $attr['id']      . '"';
      $style   = empty($attr['style'])   ? null : ' style="' . $attr['style']   . '"';
      $title   = empty($attr['subject']) ? null : ' title="' . $attr['subject'] . '"';
      $email   = $attr['email']   ?? null;
      $encode  = function($string) {
         $html = '';
         $size = strlen($string);
         $seed = max(3, $size / 3);
         for ($ith = 0; $ith < $size; $ith++) {
            $code  = '&#' . ord($string[$ith]) . ';';
            $html .= $code . ($ith % $seed ? '' : '<i style="display:none;">' . $code . '</i>');
         }
         return($html);
      };
      list($target, $domain) = explode('@', $email);
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
      return('<a' . $id . $class . ' href="https://' . $domain . '" onclick="usi_link_validate(event);"' . $style . ' target="' . str_rot13($target) . '"' . $title . '>' . $content . '</a>');
   } // shortcode_email();

} // Class USI_WordPress_Solutions;

USI_WordPress_Solutions::_init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>