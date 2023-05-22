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

This popup displays a confirmation message for a list of items in a WordPress table.

*/

class USI_WordPress_Solutions_Popup_Action {

   const VERSION = '2.14.3 (2022-10-05)';

   const HEIGHT_HEAD_FOOT = 93;

   private static $admin_notice = null;

   private static $scripts = array();

   private function __construct() {
   } // __construct();

   public static function build($options) {

      if (!empty($options['id'])) {
         $id       = $options['id'];
      } else {
         $id       = 'usi-popup';
      }

      if (!empty($options['title'])) {
         $title    = esc_attr($options['title']);
      } else {
         $title    = 'WordPress-Solutions Popup';
      }

      if (empty($options['height'])) {
         $size     = ' height:300px;';
         $max_body = 300;
      } else {
         $height   = explode(',', $options['height']);
         if (1 == count($height)) {
            $size  = ' height:' . $height[0] . ';';
         } else {
            $size  = ' min-height:' . $height[0] . '; max-height:' . $height[1] . ';';
         }
         $max_body = intval($height[0]);
      }
      $max_body   -= self::HEIGHT_HEAD_FOOT;

      if (empty($options['width'])) {
         $size    .= ' width:300px;';
      } else {
         $width    = explode(',', $options['width']);
         if (1 == count($width)) {
            $size .= ' width:' . $width[0] . ';';
         } else {
            $size .= ' min-width:' . $width[0] . '; max-width:' . $width[1] . ';';
         }
      }

      $cancel = $options['cancel']  ?? null;  // Cancel button text;

      $method = $options['method']  ?? 'standard'; // Popup method [standard(WordPress list table)|custom(Application defined)];

      if (empty(self::$scripts[$id])) { // IF popup html not set;

         if (empty(self::$scripts[0])) { // IF popup javascript not set;

            $divider = USI_WordPress_Solutions_Static::divider(0, $id);

            $foot = 'const foot  = { ';
            $head = 'const head  = { ';
            $work = 'const work  = { ';
            foreach ($options['actions'] as $name => $values) {
               $foot .= $name . ": '" . $values['foot'] . "', ";
               $head .= $name . ": '" . $values['head'] . "', ";
               $work .= $name . ": '" . $values['work'] . "', ";
            }
            $foot .= '};';
            $head .= '};';
            $work .= '};';

            $custom_invoke = null;
            if (!empty($options['invoke'])) {
               $custom_invoke = PHP_EOL . '// Invoke popup via custom action;' . PHP_EOL;
               foreach ($options['invoke'] as $selector => $action) {
                  $custom_invoke .= "$('$selector').click(function() { invoked_by = 'button'; return(scan('$action', '$selector')); });" . PHP_EOL;
               }
            }

            $select_bulk = "select_bulk = '" . ($options['errors']['select_bulk']  ?? 'Please select a bulk action before you click the Apply button.') . "'";
            $select_item = "select_item = '" . ($options['errors']['select_item']  ?? 'Please select some items before you click the Apply button.') . "'";

            $height_head_foot = 'const height_head_foot = ' . self::HEIGHT_HEAD_FOOT . ';';

            self::$scripts[0] = <<<EOD
// BEGIN - {$id}
{$foot}
{$head}
{$work}

{$select_bulk}
{$select_item}

{$height_head_foot}

var confirmed  = false;
var selector   = null;
var invoked_by = null;

function close(selector) {
   trace('close:selector=' + selector);
   $('#{$id}').fadeOut(300);
} // close();

// Get WordPress list bulk action;
function get_bulk_action() {
   var top = $('#bulk-action-selector-top').val();
   if (-1 != top) return(top);
   var bot = $('#bulk-action-selector-bottom').val();
   if (-1 != bot) return(bot);
   return('select_bulk');
} // get_bulk_action();

function info(action, body) {
   return('<p>' + head[action] + '</p>' + body + '<p>' + foot[action] + '</p>');
} // info();

function scan(action, selector) {
   if (confirmed) { confirmed = false; return(true); }
   var ids  = $('.usi-popup-checkbox');
   var list = '';
   var text = '';
   var action_count = 0;
   if (ids.length) {
      for (var i = 0; i < ids.length; i++) {
         if (ids[i].checked) {
            list += (list.length ? ',' : '') + ids[i].getAttribute('usi-popup-id');
            text += (action_count++ ? '<br/>' : '') + ids[i].getAttribute('usi-popup-info');
         }
      }
   } else {
      var ids  = $('input[name="post[]"]');
      for (var i = 0; i < ids.length; i++) {
         if (ids[i].checked) {
            var id = ids[i].getAttribute('id').substr(10);
            list += (list.length ? ',' : '') + id;
            text += (action_count++ ? '<br/>' : '') + $('#usi-popup-delete-' + id).attr('usi-popup-info');
         }
      }
   }
   trace('scan:action_count=' + action_count);
   if (!action_count) {
      return(show('error', '<p>' + select_item + '</p>'));
   } else {
      return(show(action, info(action, text), selector));
   }
} // scan();

function show(action, body, invoke) {

   selector = invoke;

   $('#{$id}-title').html('{$title}');

   $('#{$id}-body').html('<div style="padding:0 15px 0 15px;">' + body + '</div>');

   if ('error' === action) {
      $('#{$id}-work').html('').hide();
   } else {
      $('#{$id}-work').html(work[action]).show().attr('usi-popup-invoke', invoke);
      $('#{$id}-close').html('{$cancel}');
   }

   $('#{$id}').fadeIn(300);

   var height = $('#{$id}-body').height();

   if (height <= ${max_body}) $('#{$id}-wrap').height((height + height_head_foot) + 'px');

   return(false);

} // show();

function trace(text) {
//   alert(text);
   console.log(text);
} // trace();

// Close Popup with cancel/close/delete/ok button;
$('[usi-popup-close]').click(function() { close('[usi-popup-close]'); });

// Close with outside click;
$('[usi-popup-close-outside]').click(function() { close('[usi-popup-close-outside]'); }).children().click(function() { return(false); });

// Invoke popup via row action;
$('[usi-popup-open]').click(
   function() {
      invoked_by = 'link';
      var id     = $(this).attr('id');
      selector   = '#' + id;
      label      = '$([usi-popup-open]).click(' + selector + '):';
      trace(label + 'confirmed=' + (confirmed ? 'true' : 'false'));
      if (confirmed) { confirmed = false; return(true); }
      var action = $(this).attr('usi-popup-action');
      var body   = $(this).attr('usi-popup-info');
      return(show(action, info(action, body), selector));
   }
); // Invoke popup via row action;

// Invoke popup via bulk action;
$('#doaction,#doaction2').click(
   function() {
      invoked_by = 'button';
      selector   = '#doaction,#doaction2';
      trace('$(#doaction,#doaction2).click(' + selector + ')');
      if (confirmed) { confirmed = false; return(true); }
      var action = get_bulk_action();
      if ('select_bulk' == action) return(show('error', '<p>' + select_bulk + '</p>'));
      return(scan(action, selector));
   }
); // Invoke popup via bulk action;
{$custom_invoke}
// Execute action;
$('#{$id}-work').click(
   function() {
      label = '$(#{$id}-work).click():';
      trace(label + 'selector=' + selector + ' invoked_by=' + invoked_by);
      if ('button' == invoked_by) {
         confirmed = true;
         $(selector).click();
      } else {
         var href = $(selector).attr('href');
         trace(label + 'href=' + href);
         location.href = href;
      }
   }
); // Execute action;
// END - {$id}

EOD;

            USI_WordPress_Solutions::admin_footer_jquery(self::$scripts[0]);

         } // ENDIF popup javascript not set;

         $divider = USI_WordPress_Solutions_Static::divider(0, $id);

         // The {$id}-head div is equivalent to the WordPress thickbox TB_title div;
         // The {$id}-title div is equivalent to the WordPress thickbox TB_ajaxWindowTitle div;

         self::$scripts[$id] = <<<EOD
{$divider}<div id="{$id}" usi-popup-close-outside="{$id}" style="background:rgba(0,0,0,0.7); display:none; height:100%; left:0; position:fixed; top:0; width:100%; z-index:100050;">
  <div id="{$id}-wrap" style="background:#ffffff; box-sizing:border-box; left:50%; position:relative; top:50%; transform:translate(-50%,-50%); {$size}">
    <div id="{$id}-head" style="background:#fcfcfc; border-bottom:1px solid #ddd; height:29px;">
      <div id="{$id}-title" style="float:left; font-weight:600; line-height:29px; overflow:hidden; padding:0 29px 0 10px; text-overflow:ellipsis; white-space: nowrap; width:calc(100%-39px);"></div>
        <button type="button" style="background:#fcfcfc; border:solid 1px #00a0d2; color:#00a0d2; cursor:pointer; height:29px; position:absolute; right:0; top:0;" usi-popup-action="close" usi-popup-close="{$id}" >
          <span class="screen-reader-text">{$cancel}</span>
          <span class="dashicons dashicons-no"></span>
        </button>
    </div><!--{$id}-head-->
    <div id="{$id}-body" style="border-bottom:1px solid #ddd; max-height:{$max_body}px; overflow:auto;"></div>
    <div id="{$id}-foot">
      <div style="display:inline-block; height:13px; width:15px;"></div>
      <span class="button" id="{$id}-work" style="display:none; margin:15px 5px 0 0;"></span>
      <span class="button" id="{$id}-close" style="margin:15px 0 0 0;" usi-popup-close="{$id}">{$cancel}</span>
    </div><!--{$id}-foot-->
  </div><!--{$id}-wrap-->
</div>
$divider
EOD;
         USI_WordPress_Solutions::admin_footer_script(self::$scripts[$id]);

      }  // ENDIF popup html not set;

   } // build();

   public static function column_cb($args) {

      $id_field = $args['id_field'] ?? null;
      $indent   = $args['indent']   ?? null;
      $info     = $args['info']     ?? null;
      $post     = $args['post']     ?? null;

      if ($post) {
         $id    = $post->ID;
      } else {
         $id    = $args['id']       ?? null;
      }

      return($indent
         . '<input class="usi-popup-checkbox" name="' . $id_field . '[' . $id . ']" type="checkbox" '
         . $indent
         . 'usi-popup-id="' . $id . '" '
         . $indent
         . 'usi-popup-info="' . $info . '" value="' . $id .'" />'
         . $indent
      );

   } // column_cb();

   public static function delete_single_post($pre_delete_function = null, $admin_notice = null) {

      if (!empty($_REQUEST['what2do']) && !empty($_REQUEST['_wpnonce']) && !empty($_REQUEST['post']) && ('delete' == $_REQUEST['what2do'])) {

         if (wp_create_nonce('what2do=delete') == $_REQUEST['_wpnonce']) {

            $post_id = (int)$_REQUEST['post'];

            if (is_callable($pre_delete_function)) call_user_func_array($pre_delete_function, array($post_id));

            wp_delete_post($post_id, true);

            self::$admin_notice = $admin_notice;

            add_action(
               'admin_notices', 
               function () {
                  echo ''
                  . '<div id="message" class="updated notice is-dismissible">'
                  .   '<p>' . self::$admin_notice . '</p>'
                  .   '<button type="button" class="notice-dismiss">'
                  .   '<span class="screen-reader-text">' . __('Dismiss this notice', USI_WordPress_Solutions::TEXTDOMAIN) . '</span>'
                  .   '</button>'
                  . '</div>'
                  ;
               }
            );

         }

      }

   } // delete_single_post();

   public static function row_action($args, $action, $text) {

      $id_field = !empty($args['id_field']) ? $args['id_field'] : null;
      $info     = !empty($args['info'])     ? $args['info']     : null;
      $item     = !empty($args['item'])     ? $args['item']     : null;
      $post     = !empty($args['post'])     ? $args['post']     : null;

      if ($post) {
         $id    = $post->ID;
         $url   = !empty($args['url'])      ? $args['url']      : null;
      } else {
         $id    = !empty($item[$id_field])  ? $item[$id_field]  : null;

      $url      = esc_url(
         wp_nonce_url( 
            add_query_arg( 
               array(
                  'action'  => $action,
                  $id_field => $id,
               ), 
               get_admin_url() . (!empty($args['url']) ? $args['url'] : null)
            ), 
            'bulk-' . (!empty($args['bulk']) ? $args['bulk'] : $action . '_' . $id)
         )
      );
      }

      return(
         '<a id="usi-popup-' . $action . '-' . $id . '" href="' . $url . '" usi-popup-action="' . $action . 
         '" usi-popup-open="' . $id . '" usi-popup-info="' . $info . '">' . $text . '</a>'
      );

   } // row_action();

} // Class USI_WordPress_Solutions_Popup_Action;

// --------------------------------------------------------------------------------------------------------------------------- // ?>