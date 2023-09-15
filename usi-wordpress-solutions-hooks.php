<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/*
WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either version 3 of the License, or any later version.
 
WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License along with WordPress-Solutions. If not, see 
https://github.com/jaschwanda/wordpress-solutions/blob/master/LICENSE.md

Copyright (c) 2023 by Jim Schwanda.
*/

//https://stackoverflow.com/questions/5224209/wordpress-how-do-i-get-all-the-registered-functions-for-the-content-filter

class USI_WordPress_Solutions_Hooks {

   const VERSION = '2.16.0 (2023-09-15)';

   private function __construct() {
   } // __construct();

   public static function get($hook = '') {

      global $wp_filter;

      if (isset($wp_filter[$hook]->callbacks)) {
         array_walk(
            $wp_filter[$hook]->callbacks, 
            function($callbacks, $priority) use (&$hooks) {
               foreach ($callbacks as $id => $callback) {
                  $hooks[] = array_merge(['id' => $id, 'priority' => $priority], $callback);
               }
            }
         );         
      } else {
         return([]);
      }

      foreach($hooks as &$item) {
         // skip if callback does not exist;
         if (!is_callable($item['function'])) continue;
         // function name as string or static class method eg. 'Foo::Bar';
         if (is_string($item['function'])) {
            $ref = strpos($item['function'], '::') 
               ? new ReflectionClass(strstr($item['function'], '::', true)) 
               : new ReflectionFunction($item['function']);
            $item['file'] = $ref->getFileName();
            $item['line'] = get_class($ref) == 'ReflectionFunction' 
               ? $ref->getStartLine() 
               : $ref->getMethod(substr($item['function'], strpos($item['function'], '::' ) + 2))->getStartLine();
         } else if (is_array($item['function'])) {
            $ref = new ReflectionClass($item['function'][0]);
            // $item['function'][0] is a reference to existing object;
            $item['function'] = [
                is_object($item['function'][0]) ? get_class($item['function'][0]) : $item['function'][0],
                $item['function'][1]
            ];
            $item['file'] = $ref->getFileName();
            $item['line'] = strpos($item['function'][1], '::')
               ? $ref->getParentClass()->getMethod(substr($item['function'][1], strpos($item['function'][1], '::') + 2))->getStartLine()
               : $ref->getMethod($item['function'][1])->getStartLine();
        // Closures;
        } elseif (is_callable($item['function'])) {
            $ref = new ReflectionFunction($item['function']);
            $item['function'] = get_class($item['function']);
            $item['file'] = $ref->getFileName();
            $item['line'] = $ref->getStartLine();
        }
     }

     return($hooks);

   } // get();

} // Class USI_WordPress_Solutions_Hooks;

// --------------------------------------------------------------------------------------------------------------------------- // ?>