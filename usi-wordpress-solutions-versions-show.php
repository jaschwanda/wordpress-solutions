<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Versions_Show {

   const VERSION = '2.16.0 (2023-09-15)';

   public static function parse($expression) {

      $lines   = explode(PHP_EOL, $expression);

      $package = null;

      $site    = [];

      foreach ($lines as $line) {

         $tokens = explode(':', $line);

         if (empty($site['title'])) {

            $site['title'] = $tokens[0];

         } else {

            if (3 != count($tokens)) continue;

            $site['packages'][$tokens[0]][$tokens[1]] = $tokens[2];

         }

      }

      return $site;

   } // parse();

   public static function show($import_expression = null) {

      $title = sanitize_title(get_bloginfo('title'));

      $site  = self::parse(USI_WordPress_Solutions_Versions_All::versions($title, WP_CONTENT_DIR));

      if ($import_expression) {
         $import_site = self::parse($import_expression);
         $first_title = $import_site['title'];
         $sites       = [$first_title => $import_site, $site['title'] => $site];
      } else {
         $first_title = $site['title'];
         $sites       = [$first_title => $site];
      }

      $html  = '<table border="1" cellpadding="2" style="margin:20px 0 0 -220px;">' . PHP_EOL . 
               '  <tr><td>Function</td>';
      foreach ($sites as $site) {
         $html .= '<td>' . $site['title'] . '</td>';
      }
      $html .= '</tr>' . PHP_EOL;

      foreach ($sites[$first_title]['packages'] as $package_name => $package) {

         $html .= '  <tr><td>' . $package_name . '</td><td colspan="' . count($sites) . '"></td></tr>' . PHP_EOL;

         foreach ($package as $file => $version) {
            $version = trim($version);
            $html   .= '  <tr><td>' . $file . '</td><td>' . $version . '</td>';
            foreach ($sites as $site_title => $site) {
               if ($site_title == $first_title) continue;
               $other_version = !empty($site['packages'][$package_name][$file]) ? trim($site['packages'][$package_name][$file]) : null;
               $html         .= '<td' . ($other_version != $version ? ' style="color:red;"' : '') . '>' . ($other_version ? $other_version : 'missing') . '</td>';
               unset($sites[$site_title]['packages'][$package_name][$file]);
            }

            $html .= '</tr>' . PHP_EOL;
         }

         $html .= '  <tr><td>&nbsp;</td><td></td>';

         foreach ($sites as $site_title => $site) {
            if ($site_title == $first_title) continue;
            if (!empty($site['packages'][$package_name])) {
               $html .= '<td style="color:red;">';
               foreach ($site['packages'][$package_name] as $file => $version) {
                  $html .= $file . '<br/>';
               }
               $html .= '</td>';
            } else {
               $html .= '<td></td>';
            }
         }

         $html .= '</tr>' . PHP_EOL;

      }

      return $html . '</table>' . PHP_EOL;

   } // show();

} // USI_WordPress_Solutions_Versions_Show();

// --------------------------------------------------------------------------------------------------------------------------- // ?>