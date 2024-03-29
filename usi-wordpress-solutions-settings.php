<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

// Reference: https://developer.wordpress.org/plugins/settings/using-settings-api/
// Reference: https://digwp.com/2016/05/wordpress-admin-notices/


class USI_WordPress_Solutions_Settings {

   const VERSION = '2.16.3 (2023-11-27)';

   private static $current_user_id = -1; 
   private static $grid            = false;
   private static $impersonate     = false; // Allow user impersonation;
   private static $label_option    = null;  // Null means default behavior, label to left of field;
   private static $one_per_line    = false;
   private static $remove_password = false; // Remove password reset;

   protected $active_tab = null;       // Will be set befor fields_sanitize() call;
   protected $active_tab_maybe = null; // Could contain a spoofed URL argument, should validate before use;
   protected $capability = 'manage_options';
   protected $capabilities = null;
   protected $datepicker = null;
   protected $debug = 0;
   protected $editor = null;
   protected $enctype = null;
   protected $field = null; // Field being rendered by do_settings_fields() functions;
   protected $hide = null;
   protected $icon_url = null;
   protected $is_all = false;
   protected $is_page = false;
   protected $is_options = false;
   protected $is_tabbed = false;
   protected $jquery = null;
   protected $name = null;
   protected $option_name = null;
   protected $option_page = true; // Options are paged via section_id's;
   protected $options = null;
   protected $override_do_settings_fields = true;
   protected $override_do_settings_sections = true;
   protected $page = null;
   protected $page_slug = null;
   protected $position = null;
   protected $prefix = null;
   protected $query = null;
   protected $render = 'page_render';
   protected $roles = null;
   protected $section_callback_offset = 0;
   protected $section_callbacks = [];
   protected $section_ids = [];
   protected $sections = null;
   protected $text_domain = null;
   protected $title = null;

   function __construct($config) {

      global $pagenow;

      if ('admin-ajax.php' == $pagenow) return;

      if (!empty($config['capability']))   $this->capability   = $config['capability'];
      if (!empty($config['capabilities'])) $this->capabilities = $config['capabilities'];
      if (!empty($config['datepicker']))   $this->datepicker   = $config['datepicker'];
      if (!empty($config['editor']))       $this->editor       = $config['editor'];
      if (!empty($config['hide']))         $this->hide         = $config['hide'];
      if (!empty($config['icon_url']))     $this->icon_url     = $config['icon_url'];
      if (!empty($config['name']))         $this->name         = $config['name'];
      if (!empty($config['options']))      $this->options      = & $config['options'];
      if (!empty($config['page']))         $this->page         = $config['page'];
      if (!empty($config['position']))     $this->position     = $config['position'];
      if (!empty($config['prefix']))       $this->prefix       = $config['prefix'];
      if (!empty($config['query']))        $this->query        = $config['query'];
      if (!empty($config['roles']))        $this->roles        = $config['roles'];
      if (!empty($config['text_domain']))  $this->text_domain  = $config['text_domain'];

      if (!empty($config['file'])) register_activation_hook($config['file'], [$this, 'hook_activation']);

      $this->debug       = USI_WordPress_Solutions_Diagnostics::get_log(USI_WordPress_Solutions::$options);

      $this->option_name = $this->prefix . '-options' . (!empty($config['suffix']) ? $config['suffix'] : '');

      $this->page_slug   = self::page_slug($this->prefix);

      $this->is_page     = !empty($_GET['page']) && ($_GET['page'] == $this->page_slug) && (('admin.php' == $pagenow) || ('options-general.php' == $pagenow));

      $this->is_options  = !empty($_POST['option_page']) && ($_POST['option_page'] == $this->page_slug) && ('options.php' == $pagenow);

      if ('plugins.php' == $pagenow) {

         if (empty($config['no_settings_link'])) add_filter('plugin_action_links', [$this, 'filter_plugin_action_links'], 10, 2);

         $filter_plugin_row_meta = [$this, 'filter_plugin_row_meta'];

         if (is_callable($filter_plugin_row_meta)) add_filter('plugin_row_meta', $filter_plugin_row_meta, 10, 2);

      } else if ($this->is_page || $this->is_options) {

         if ($this->is_tabbed) {
            $this->active_tab_maybe = $_POST[$this->prefix . '-tab'] ?? $_GET['tab'] ?? null;
            if (empty($this->active_tab_maybe) && !empty($_REQUEST['_wp_http_referer'])) {
               // Need to get the part from the referer because of the way WordPress double loads the page;
               parse_str(parse_url($_REQUEST['_wp_http_referer'] ?? null, PHP_URL_QUERY), $this->query);
               $this->active_tab_maybe = $this->query['tab'] ?? null;
            }
         }

         add_action('admin_head', [$this, 'action_admin_head']);
         add_action('admin_init', [$this, 'action_admin_init']);

         add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts']);

      } else if (!empty(USI_WordPress_Solutions::$options['illumination']['visible-grid'])) {

         switch ($pagenow) {
         case 'profile.php':
         case 'user-edit.php': 
         case 'user-new.php': 
         case 'users.php': 
            add_action('admin_head', [$this, 'action_admin_head']);
            break;
         }

      }

      add_action('admin_menu', [$this, 'action_admin_menu']);
      add_action('init', [__CLASS__, 'action_init']);

      // Add notices for custom options pages, WordPress does settings pages automatically;
      if ('menu' == $this->page) add_action('admin_notices', [$this, 'action_admin_notices']);

      // In case you get the "options page not found" error, fiddle with this;
      // add_filter('whitelist_options', [$this, 'filter_whitelist_options'], 11, 1);

   } // __construct();

   function action_admin_enqueue_scripts() {

      // https://api.jqueryui.com/datepicker/
      // https://phptechnologytutorials.wordpress.com/2014/04/01/use-wordpress-default-jquery-ui-datepicker-in-your-theme/
      // https://trentrichardson.com/examples/timepicker/

      if ($this->datepicker) {

         wp_enqueue_script('jquery');
         wp_enqueue_script('jquery-ui-core');
         wp_enqueue_script('jquery-ui-datepicker');

         wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

         $datepicker_option = (true === $this->datepicker) ? PHP_EOL : ',' . $this->datepicker . PHP_EOL;

         $this->jquery .= ''
         . '// BEGIN usi-wordpress-solutions-settings' . PHP_EOL
         . "$('.datepicker').datepicker(" . PHP_EOL
         . '   {' . PHP_EOL
         . '      changeMonth : true,' . PHP_EOL
         . '      changeYear  : true,' . PHP_EOL
         . '      closeText   : "Clear",' . PHP_EOL
         . '      dateFormat  : "yy-mm-dd",' . PHP_EOL
         . '      onClose     : function (dateText, inst) {' . PHP_EOL
         . '         if ($(window.event.srcElement).hasClass("ui-datepicker-close")) {' . PHP_EOL
         . '            document.getElementById(this.id).value = "";' . PHP_EOL
         . '         }' . PHP_EOL
         . '      },' . PHP_EOL
         . '      showButtonPanel    : true,' . PHP_EOL 
         . '      showMonthAfterYear : true' . $datepicker_option
         . '   }' . PHP_EOL
         . ');' . PHP_EOL
         . "$('.datepicker').keydown(function(event) {event.preventDefault();});" . PHP_EOL
         . "$('.datepicker').keypress(function(event) {event.preventDefault();});" . PHP_EOL
         . '// END usi-wordpress-solutions-settings' . PHP_EOL
         ;

         $this->datepicker = false;

      }

      if ($this->jquery) {

         USI_WordPress_Solutions_Admin::admin_footer_jquery($this->jquery);

         $this->jquery = null;
      }

      if ($this->editor) {

         wp_register_script('usi_wordpress_tiny', plugins_url('tinyMCE/tinymce.min.js', __FILE__));
         wp_enqueue_script('usi_wordpress_tiny', null, null, null, true);

         if (is_string($this->editor)) {
            wp_register_script('usi_wordpress_tiny_config', plugins_url() . '/' . $this->editor);
         } else {
            wp_register_script('usi_wordpress_tiny_config', plugins_url('tinyMCE/usi-wordpress-solutions.js', __FILE__));
         }

         wp_enqueue_script('usi_wordpress_tiny_config', null, null, null, true);

      }

   } // action_admin_enqueue_scripts();

   function action_admin_head($css = null) {

      if (!empty($this->options['css'])) $css .= $this->options['css'];

      USI_WordPress_Solutions_Static::action_admin_head($css);

   } // action_admin_head();

   function action_admin_init() {

      $prefix = $this->prefix;

      $this->sections_load();

      if ($this->sections) { // IF sections exist;

         foreach ($this->sections as $section_id => $section) {

            if (empty($section)) continue;

            $this->section_callbacks[] = !empty($section['header_callback']) ? $section['header_callback'] : null;
            $this->section_ids[] = $section_id;

            add_settings_section(
               $section_id, // Section id;
               !$this->is_tabbed && !empty($section['label']) ? $section['label'] : (!empty($section['title']) ? $section['title'] : ''), // Section title;
               [$this, 'section_render'], // Render section callback;
               $this->page_slug // Settings page menu slug;
            );

            if (!empty($section['after_add_settings_section'])) {
               $object = $section['after_add_settings_section'][0];
               $method = $section['after_add_settings_section'][1];
               if (method_exists($object, $method)) $section['settings'] = $object->$method($section['settings']);
            }

            if (!empty($section['settings'])) {

               foreach ($section['settings'] as $option_id => $attributes) {

                  if ($this->option_page) {

                     $option_name  = (!empty($attributes['name']) ? $attributes['name'] : $this->option_name . '[' . $section_id . '][' . $option_id . ']');
                     $option_value = isset($this->options[$section_id][$option_id]) 
                        ? $this->options[$section_id][$option_id] 
                        : self::get_value($attributes);
                     if (USI_WordPress_Solutions::DEBUG_INIT == (USI_WordPress_Solutions::DEBUG_INIT & $this->debug)) usi::log('$options[' . $section_id . '][' . $option_id . ']=' . $option_value);

                  } else {

                     $option_name  = (!empty($attributes['name']) ? $attributes['name'] : $this->option_name . '[' . $option_id . ']');
                     $option_value = isset($this->options[$option_id]) 
                        ? $this->options[$option_id] 
                        : self::get_value($attributes);
                     if (USI_WordPress_Solutions::DEBUG_INIT == (USI_WordPress_Solutions::DEBUG_INIT & $this->debug)) usi::log('$options[' . $option_id . ']=' . $option_value);

                  }

                  if (empty($attributes['skip'])) {
                     add_settings_field(
                        $option_id, // Option name;
                        !empty($attributes['label']) ? $attributes['label'] : null, // Field title; 
                        [ $this, !empty($attributes['callback']) ? $attributes['callback'] : 'fields_render' ], // Render field callback;
                        $this->page_slug, // Settings page menu slug;
                        $section_id, // Section id;
                        array_merge($attributes, 
                           [
                              'name'  => $option_name,
                              'value' => $option_value
                           ]
                        )
                     );
                  }
               }
            }
         }
      } // ENDIF sections exist;

      register_setting(
         $this->page_slug, // Settings group name, must match the group name in settings_fields();
         $this->option_name, // Option name;
         [ $this, 'fields_sanitize' ] // Sanitize field callback;
      );

   } // action_admin_init();

   function action_admin_menu() { 

      // IF custom settings page;
      if ('menu' == $this->page) {

         $slug = add_menu_page(
            __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
            __($this->name, $this->text_domain), // Sidebar menu text; 
            $this->capability, // Capability required to enable page;
            $this->page_slug, // Menu page slug name;
            [ $this, $this->render ], // Render page callback;
            $this->icon_url, // URL of icon for menu item;
            $this->position // Position in menu order;
         );

      } else { // ELSE standard settings page;

         $slug = add_options_page(
            __($this->name . ' Settings', $this->text_domain), // Page <title/> text;
            __($this->name, $this->text_domain), // Sidebar menu text; 
            $this->capability, // Capability required to enable page;
            $this->page_slug, // Menu page slug name;
            [ $this, $this->render ] // Render page callback;
         );

      } // ENDIF standard settings page;

      $action_load_help_tab = [ $this, 'action_load_help_tab' ];

      if (is_callable($action_load_help_tab)) add_action('load-'. $slug, $action_load_help_tab);

      if ($this->hide) {
         global $menu;
         foreach ($menu as $key => $values) {
            if ($values[2] == $this->page_slug) {
               unset($menu[$key]);
               break;
            }
         }
      }

   } // action_admin_menu();

   // Add settings error/success status for custom settings pages;
   function action_admin_notices() {
      if (isset($_GET['settings-updated'])) {
         settings_errors($this->page_slug);
      }
   } // action_admin_notices();

   public static function action_init() { 

      self::$impersonate     = !empty(USI_WordPress_Solutions::$options['admin-options']['impersonate']) && current_user_can('usi_wordpress_impersonate_user');

      self::$remove_password = !empty(USI_WordPress_Solutions::$options['admin-options']['pass-reset']);

      if (self::$impersonate || self::$remove_password) {
         self::$current_user_id = wp_get_current_user()->ID ?? -1;
         add_filter('user_row_actions', [ __CLASS__, 'filter_user_row_actions' ], 10, 2);
      }

      if (self::$impersonate) {
         if (!empty($_REQUEST['action']) && !empty( $_REQUEST['user_id']) && ('impersonate' == $_REQUEST['action'])) {
            if ($user = get_userdata($user_id = $_REQUEST['user_id'])) {
               if (wp_verify_nonce($_REQUEST['_wpnonce'], "impersonate_$user_id")) {
                  if (!empty(USI_WordPress_Solutions::$options['admin-options']['history'])) {
                     $old_user = get_userdata($old_user_id = get_current_user_id());
                     USI_WordPress_Solutions_History::history($old_user_id, 'user', 
                        'User <' . $old_user->display_name . '> impersonating user <' . $user->display_name . '>', $user_id, $_REQUEST);
                  }
                  wp_clear_auth_cookie();
                  wp_set_current_user($user_id, $user->user_login);
                  wp_set_auth_cookie($user_id);
                  do_action('wp_login', $user->user_login, $user);
               }
            }
         }
      }

      if (!empty(USI_WordPress_Solutions::$options['preferences']['menu-sort'])) {
         switch (USI_WordPress_Solutions::$options['preferences']['menu-sort']) {
         case 'alpha':
         case 'usi':
            add_filter('custom_menu_order' , '__return_true');
            add_filter('menu_order' , [ __CLASS__, 'filter_menu_order' ]);
            break;
         }
      }

   } // action_init();

   public function capabilities() { 

      return $this->capabilities;

   } // capabilities();

   // This function riped from wp-admin/includes/template.php;
   function do_settings_fields($page, $section) {

      global $wp_settings_fields;

      if (!isset($wp_settings_fields[$page][$section])) return;

      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $i4 = '  ' . $i3;
      $i5 = '  ' . $i4;
      $n  = PHP_EOL;

      foreach ((array)$wp_settings_fields[$page][$section] as $field) {
         $class = '';

         $o1 = self::$label_option ? $n . $i4 . '</tr>' . $n . $i4 . '<tr>' : '';

         if (!empty($field['args']['class'])) $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';

         if (!self::$one_per_line) {
            echo "$i4<tr{$class}>$n$i5";
            if (!empty($field['args']['label_for'])) {
               echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label></th>';
            } else if (empty($field['args']['alt_html'])) {
               echo '<th scope="row">' . $field['title'] . '</th>';
            } else {
               echo '<th scope="row">' . $field['args']['alt_html'] . '</th>';
            }
            echo $o1 . $n . $i5 . '<td>';
         }
         call_user_func($field['callback'], $field['args']);
         if (!self::$one_per_line) {
            echo '</td>' . $n;
            echo $i4 . '</tr>' . $n;
         }
      }

   } // do_settings_fields();

   // This function riped from wp-admin/includes/template.php;
   function do_settings_sections($page) {
      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $n  = PHP_EOL;
      global $wp_settings_sections, $wp_settings_fields;
      if (!isset($wp_settings_sections[$page])) return;
      foreach ((array)$wp_settings_sections[$page] as $section) {
         if ($section['title']) echo "$i3<h2>{$section['title']}</h2>\n";
         if ($section['callback']) call_user_func($section['callback'], $section);
         if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) continue;
         echo $i3 . '<table class="form-table" role="presentation">' . $n;
         if ($this->override_do_settings_fields) {
            $this->do_settings_fields($page, $section['id']);
         } else {
            do_settings_fields($page, $section['id']);
         }
         echo $i3 . '</table>' . $n;
      }
   } // do_settings_sections();

   function do_settings_fields_advanced($page, $section) {

      global $wp_settings_fields;

      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $i4 = '  ' . $i3;
      $i5 = '  ' . $i4;
      $n  = PHP_EOL;

      if (!isset($wp_settings_fields[$page][$section])) return;

      // FOREACH field in the sections;
      foreach ((array)$wp_settings_fields[$page][$section] as $field) {

         $class = '';

         if (!empty($field['args']['class'])) $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';

         // IF default or over mode (not none);
         if ('none' != self::$grid) {
            $html = null;
            $span = (!empty($field['args']['span']) ? ' colspan="' . $field['args']['span'] . '"' : '');
            $xtra = ('over' == self::$grid ? ' class="th-over"' : '');
            if (!empty($field['args']['label_for'])) {
               $html = '<th' . $xtra . $span . ' scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label></th>';
            } else if (!empty($field['args']['alt_html'])) {
               $html = '<th' . $xtra . $span . ' scope="row">' . $field['args']['alt_html'] . '</th>';
            } else if (!empty($field['title'])) {
               $html = '<th' . $xtra . $span . ' scope="row">' . $field['title'] . '</th>';
            } 
            if ($html) echo "$i4<tr{$class}>$n$i5" . $html . $n;
            if ('over' == self::$grid) {
               if ($html) echo $i4 . '</tr>' . $n;
               echo $i4 . '<tr>' . $n;
            }
            echo $i5 . '<td' . $span . '>';
         } // ENDIF default or over mode (not none);

         // emit the actual field;
         $this->field = $field;
         call_user_func($field['callback'], $field['args']);

         if ('none' != self::$grid) {
            echo '</td>' . $n . $i4 . '</tr>' . $n;
         }
      } // ENDFOR each field in the sections;

      $this->field = null;

   } // do_settings_fields_advanced();

   // This function riped from wp-admin/includes/template.php;
   function do_settings_sections_advanced($page) {

      global $wp_settings_sections, $wp_settings_fields;

      if (!isset($wp_settings_sections[$page])) return;

      $i  = $this->is_tabbed ? '  ' : '';
      $i2 = '  ' . $i;
      $i3 = '  ' . $i2;
      $n  = PHP_EOL;

      foreach ((array)$wp_settings_sections[$page] as $section) {

         $section_id = $section['id'];

         if (isset($this->sections[$section_id]['grid'])) self::set_grid($this->sections[$section_id]['grid']);
         if ($section['title']) echo "$i3<h2>{$section['title']}</h2>\n";
         if ($section['callback']) call_user_func($section['callback'], $section);
         if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section_id])) continue;

         if (empty($this->sections[$section_id]['no_table'])) echo $i3 . '<table class="form-table" role="presentation">' . $n;

         $this->do_settings_fields_advanced($page, $section_id);

         if (empty($this->sections[$section_id]['no_table'])) echo $i3 . '</table>' . $n;

      }

   } // do_settings_sections_advanced();

   public static function esc_tiny($value) {

      return str_replace([ '&lt;', '&gt;', '&quot;' ], [ '<', '>', '"' ], $value);

   } // esc_tiny();

   function fields_render($args) {
      if (USI_WordPress_Solutions::DEBUG_RENDER == (USI_WordPress_Solutions::DEBUG_RENDER & $this->debug)) {
         if ($this->field) {
            $temp = [];
            foreach ($this->field as $key => $value) {
               if ('callback' != $key) $temp[$key] = $value;
            }
            if (!empty($temp['args']['type'])) {
               if ('html' != $temp['args']['type']) {
                  if (empty($temp['options-value'])) $temp['options-value'] = $this->options[$temp['id']];
               }
            }
            usi::log('field=', $temp);
         } else {
            usi::log('field=', $args);
         }
      }
      self::fields_render_static($args);
   } // fields_render();

   public static function fields_render_select($attributes, $rows, $value = null) {
      // Where $rows = [[1st-value, '1st-option'), [2nd-value, '2nd-option'], ... ]
      $html = '<select' . $attributes . '>';
      foreach ($rows as $row) {
         $html .= '<option ' . ($row[0] === $value ? 'selected ' : '') . 'value="' . $row[0] . '">' . $row[1] . '</option>';
      }
      return $html . '</select>';
   } // fields_render_select();

   // Static version so that other classes can use this rendering function;
   public static function fields_render_static($args) {

      // A field of 'alt_html' will put the html given in the default label column;

      if (isset($args['one_per_line'])) self::$one_per_line = empty($args['one_per_line']);

      if (isset($args['.grid'])) self::set_grid($args['.grid']);

      $notes    = !empty($args['notes'])   ? $args['notes'] : null;
      $type     = !empty($args['type'])    ? $args['type']  : 'text';

      $id       = !empty($args['id'])      ? ' id="'    . $args['id']      . '"' : null;
      $class    = !empty($args['f-class']) ? ' class="' . $args['f-class'] . '"' : null;
      $name     = !empty($args['name'])    ? ' name="'  . $args['name']    . '"' : null;
      $attr     = !empty($args['attr'])    ? ' '        . $args['attr']          : null;

      $min      = isset($args['min'])      ? ' min="'   . $args['min']     . '"' : null;
      $max      = isset($args['max'])      ? ' max="'   . $args['max']     . '"' : null;

      $prefix   = isset($args['prefix'])   ? $args['prefix'] : '';
      $suffix   = isset($args['suffix'])   ? $args['suffix'] : '';

      $rows     = isset($args['rows'])     ? ' rows="'  . $args['rows']  . '"' : null;

      $maxlen   = !empty($args['maxlength']) ? (is_integer($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : null) : null;

      $value    = self::get_value($args);

      switch ($type) {
      case 'textarea':
         $value = esc_textarea($value);
         break;
      case 'tiny':
         // We don't use esc_textarea() here because it will remove all of the TinyMCE formatting;
         $value = self::esc_tiny($value);
         break;
      default:
         $value = esc_attr($value);
      }

      // Some fields don't have a "readonly" attribute, so we have to use disabled "instead",
      // but then the value of the disabled field is not posted to the server, so we add a
      // hidden field to post the value back to the server;
      $readonly = $disable_hidden = null;
      if (!empty($args['readonly'])) {
         if (('checkbox' == $type) || ('radio' == $type) || ('select' == $type)) {
            $readonly = ' disabled';
            if ($value) $disable_hidden = '<input' . $name . ' type="hidden" value="' . $value . '" />';
         } else {
            $readonly = ' readonly';
         }
      } else {
         if ('tiny' == $type) {
            $class    = ($class ? trim($class,'"') . ' ' : ' class="') . 'usi-wordpress-tiny"';
         }
      }

      $attributes = $id . $class . $name . $attr . $min . $max . $maxlen . $readonly . $rows;

      switch ($type) {

      case 'checkbox':
         // Not sure why we have to convert 'true' to true, but checked() sometimes wouldn't check otherwise;
         echo $prefix . '<input type="checkbox"' . $attributes . ' value="true"' . checked('true' == $value ? true : $value, true, false) . ' />' . $disable_hidden . $suffix;
         break;

      case 'radio':
         foreach ($args['choices'] as $choice) {
            $label = !empty($choice['label']);
            echo $prefix . (!empty($choice['prefix']) ? $choice['prefix'] : '') .
               ($label ? '<label>' : '') . '<input type="radio"' . $attributes . ' value="' . esc_attr($choice['value']) . '"' . 
               checked($choice['value'], $value, false) . ' />'  . $choice['notes'] . ($label ? '</label>' : '') .
               (!empty($choice['suffix']) ? $choice['suffix'] : '');
         }
         if ($disable_hidden) echo $disable_hidden;
         break;

      case 'file':
         echo $prefix . '<input type="' . $type . '"' . $attributes . ' />' . $suffix;
         break;
      case 'null-number':
         $type = 'number';
      case 'email':
      case 'hidden':
      case 'number':
      case 'password':
      case 'text':
         echo $prefix . '<input type="' . $type . '"' . $attributes . ' value="' . $value . '" />' . $suffix;
         break;

      case 'money':
         $value = str_replace([ '$', ' ', ',' ], '', $value);
         if ('double' !== gettype($value)) $value = (float)$value;
         if (empty($value)) $value = 0;
         if ($readonly) {
            $decimal = !empty($args['decimal']) ? (int)$args['decimal'] : 0;
            echo $prefix . '<input type="text"' . $attributes . ' value="' . '$ ' . @number_format($value, $decimal) . '" />' . $suffix;
         } else {
            echo $prefix . '<input type="number"' . $attributes . ' value="' . $value . '" />' . $suffix;
         }
         break;

      case 'html':
         echo $args['html'];
         break;

      case 'select':
         echo $prefix . self::fields_render_select($attributes, $args['options'], $value) . $disable_hidden . $suffix;
         break;

      case 'textarea':
         echo $prefix . '<textarea' . $attributes . '>' . $value . '</textarea>' . $suffix;
         break;

      case 'tiny':
         if (!empty($args['readonly'])) {
            echo $prefix . (!empty($args['lead']) ? $args['lead'] : '') . $value . (!empty($args['tail']) ? $args['tail'] : '') . $suffix;
         } else {
            echo $prefix . '<textarea' . $attributes . '>' . $value . '</textarea>' . $suffix;
         }
         break;

      }

      if ($notes) echo $notes;

      if (!empty($args['more'])) {
         foreach ($args['more'] as $more) {
            self::fields_render_static($more);
         }
      }

      if (isset($args['grid.'])) self::set_grid($args['grid.']);

   } // fields_render_static();

   function fields_sanitize($input) {

      // FOREACH section in the settings;
      foreach ($this->sections as $section_id => $section) {

         // This handles field sanitization on a section by section basis where the
         // sanitize function name is given and differs from fields_sanitize();
         if (!empty($section['fields_sanitize'])) {
            $object = $section['fields_sanitize'][0];
            $method = $section['fields_sanitize'][1];
            if (method_exists($object, $method)) {
               $input = $object->$method($input, $section_id);
            }
         }

         $settings = $section['settings'];

         if (is_array($input) && is_array($settings)) { // IF input array;

            foreach ($input as $key => $value) {
               switch (!empty($settings[$key]['type']) ? $settings[$key]['type'] : null) {
               case 'checkbox':
               case 'file':
               case 'hidden':
               case 'email':
               case 'money':
               case 'null-number':
               case 'number':
               case 'password':
               case 'radio':
               case 'select':
               case 'text':
                  $length = strlen($value);
                  $ascii  = null;
                  for ($i = 0; $i < $length; $i++) {
                     $c = ord($value[$i]);
                     if (127 < $c) continue;
                     if ( 20 > $c) continue;
                     $ascii .= chr($c);
                  }
                  $input[$key] = sanitize_text_field($ascii); 
                  break;

               case 'tiny': 
                  $value = str_replace([ '<', '>', '"' ], [ '&lt;', '&gt;', '&quot;' ], $value);
               case 'textarea': 
                  $input[$key] = sanitize_textarea_field($value); 
                  break;
               } // END switch;
            } // END foreach;

            // Search for all the checkbox that aren't checked and make sure they are cleared if previously checked;
            foreach ($settings as $key => $value) {
               if (!empty($value['type']) && ('checkbox' == $value['type'])) {
                  if (empty($input[$key]) && !empty($this->options[$key])) {
                     $this->options[$key] = $input[$key] = false;
                  }
               }
            } // END foreach;

         } // ENDIF input array;

      } // ENDFOR section in the settings;

      USI_WordPress_Solutions_History::history(get_current_user_id(), 'code', 
         'Updated <' . $this->name . '> settings', 0, $input);

      return $input;

   } // fields_sanitize();

   public static function filter_menu_order($menu_order) {
      global $submenu;
      $keys = [];
      $names = [];
      $options = [] ;
      if (!empty($submenu['options-general.php'])) {
         switch (USI_WordPress_Solutions::$options['preferences']['menu-sort']) {
         case 'alpha': $match = '/./'; break;
         case 'usi':   $match = '/^usi\-\w+-settings/'; break;
         }
         foreach ($submenu['options-general.php'] as $key => $option) {
            if (!empty($option[2]) && preg_match($match, $option[2])) {
               $keys[] = $key;
               $names[] = $option[0];
               $options[] = $option;
               unset($submenu['options-general.php'][$key]);
            }
         }
      }
      asort($names);
      foreach ($names as $index => $value) {
         $submenu['options-general.php'][$keys[$index]] = $options[$index];
      }
      return $menu_order;
   } // filter_menu_order();

   function filter_plugin_action_links($links, $file) {
      if (false !== strpos($file, $this->text_domain)) {
         $links[] = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . 
            $this->page_slug . '">' . __('Settings', $this->text_domain) . '</a>';
      }
      return $links;
   } // filter_plugin_action_links();

   public static function filter_user_row_actions(array $actions, WP_User $user) {

      if (self::$impersonate) {

         if (($user->ID != self::$current_user_id)) {
            $actions['impersonate'] = sprintf(
               '<a href="%s">%s</a>',
               esc_url(
                  wp_nonce_url( 
                     add_query_arg( 
                        [
                           'action'  => 'impersonate',
                           'user_id' => $user->ID,
                        ], 
                        get_admin_url() . 'user-edit.php?user_id=' . $user->ID
                     ), 
                     'impersonate_' . $user->ID
                  )
               ),
               esc_html__('Impersonate', 'user-switching')
            );
         }

      }

      if (self::$remove_password) unset($actions['resetpassword']);

      return $actions;

   } // filter_user_row_actions();

// In case you get the "options page not found" error, fiddle with this;
// public function filter_whitelist_options($whitelist_options) {
//     $whitelist_options[settings][0] = 'options';
//     usi::log('$whitelist_options=', $whitelist_options);
//     return $whitelist_options;
// } // filter_whitelist_options();

   function free_render() {

      $i  = '  ';
      $i2 = '    ';
      $n  = PHP_EOL;

      ob_start();
      settings_fields($this->page_slug);
      $settings_fields = ob_get_clean();

      echo '' 
      . $n . '<div class="wrap">' . $n
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . USI_WordPress_Solutions_Static::divider(2, $this->name . ' BEGIN')
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . $this->title . $n
      . $i . '<form id="myForm" action="options.php"' . $this->enctype . ' method="post">' . $n
      . str_replace('<input', $n . $i2 . '<input', $settings_fields) . $n
      ;

      global $wp_settings_sections, $wp_settings_fields;

      if (isset($wp_settings_sections[$this->page_slug])) {
         foreach ((array)$wp_settings_sections[$this->page_slug] as $section) {
            $section_id = $section['id'];
            if (isset($wp_settings_fields[$this->page_slug][$section_id])) {
               foreach ((array)$wp_settings_fields[$this->page_slug][$section_id] as $field) {
                  call_user_func($field['callback'], $field['args']);
               }
            }
         }
      }

      echo ''
      . $n
      . $i . '</form>' . $n
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . USI_WordPress_Solutions_Static::divider(2, $this->name . ' END')
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . '</div><!-- wrap -->' . $n
      ;

   } // free_render();

   private static function get_value($args) {

      // IF value not empty then return it;
      if (!empty($args['value'])) return $args['value'];

      // Now we have to decide to return a "zero" or a "null";
      $type = $args['type'] ?? 'text';
      if ('hidden' == $type) return isset($args['value']) ? $args['value'] : null;
      if ('money'  == $type) return 0;
      if ('number' == $type) return 0;

      return null;

   } // get_value();

   function hook_activation() {

      if (current_user_can('activate_plugins')) {

         check_admin_referer('activate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

         if (!empty($this->capabilities)) {
            USI_WordPress_Solutions_Capabilities::init($this->prefix, $this->capabilities);
         }

      }

   } // hook_activation();

   public function name() { 

      return $this->name;

   } // name();

   public function options() { 

      return $this->options;

   } // options();

   // To include more options on this page, override this function and call parent::page_render($options);
   function page_render($options = null) {

      $i  = '  ';
      $i2 = '    ';
      $i3 = '      ';
      $n  = PHP_EOL;
      $n2 = PHP_EOL . PHP_EOL;

      $page_header   = !empty($options['page_header'])   ? $options['page_header']   : null;
      $title_buttons = !empty($options['title_buttons']) ? $options['title_buttons'] : null;
      $tab_parameter = !empty($options['tab_parameter']) ? $options['tab_parameter'] : null;
      $trailing_code = !empty($options['trailing_code']) ? $options['trailing_code'] : null;
      $wrap_submit   = !empty($options['wrap_submit']);

      $submit_text   = null;

      if (!empty($this->sections) && ($section = reset($this->sections))) {
         if (isset($section['options']['grid'])) self::set_grid($section['options']['grid']);
      }

      echo ''
      . $n . '<div class="wrap">' . $n
      . $i . '<h1>' . ($page_header ? $page_header : __($this->name . ' Settings', $this->text_domain)) . $title_buttons . '</h1>' . $n
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . USI_WordPress_Solutions_Static::divider(2, $this->name . ' BEGIN')
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . '<form id="myForm" action="options.php"' . $this->enctype . ' method="post">' . $n
      . $i . $this->title . $n
      ;

      if ($this->is_tabbed) {
         echo $i2 . '<h2 class="nav-tab-wrapper">' . $n;
         if ($this->sections) { // IF sections exist;
            foreach ($this->sections as $section_id => $section) {
               if (empty($section) || !empty($section['not_tabbed'])) continue;
               $active_class = null;
               if ($section_id == $this->active_tab) {
                  $active_class = ' nav-tab-active';
                  $submit_text = isset($section['submit']) ? $section['submit'] : 'Save ' . $section['label'];
               }
               echo ''
               . $i3 . '<a href="' . ('menu' == $this->page ? 'admin' : 'options-general') . '.php?page='
               . $this->page_slug . '&tab=' . $section_id . $tab_parameter . ($this->query ? $this->query : '')
               . '" class="nav-tab' . $active_class . '">' . __($section['label'], $this->text_domain) . '</a>' . $n
               ;
            }
         } // ENDIF sections exist;
         echo ''
         . $i2 . '</h2>' . $n2
         . $i2 . '<input type="hidden" name="' . $this->prefix . '-tab" value="' . $this->active_tab . '" />' . $n2
         ;
      }

      settings_fields($this->page_slug);
      if (self::$grid) {
         $this->do_settings_sections_advanced($this->page_slug);
      } else if ($this->override_do_settings_sections) {
         $this->do_settings_sections($this->page_slug);
      } else {
         do_settings_sections($this->page_slug);
      }

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) {
            $section_name = $this->page_slug . '-' . $this->section_ids[$this->section_callback_offset - 1];
            echo $i2 . '</div>' . USI_WordPress_Solutions_Static::divider(10, $section_name);
         }

         if ($this->sections) { // IF sections exist;
            foreach ($this->sections as $section_id => $section) {
               if (empty($section)) continue;
               if ($section_id == $this->active_tab) {
                  if (!empty($section['footer_callback'])) {
                     $object = $section['footer_callback'][0];
                     $method = $section['footer_callback'][1];
                     $params = !empty($section['footer_callback'][2]) ? $section['footer_callback'][2] : null;
                     if (method_exists($object, $method)) $submit_text = $object->$method($params);
                  }
               }
            }
         } // ENDIF sections exist;

      } else {

         // Call the first footer callback function found for submit button HTML;
         if ($this->sections) { // IF sections exist;
            foreach ($this->sections as $section_id => $section) {
               if (empty($section)) continue;
               if (!empty($section['footer_callback'])) {
                  $object = $section['footer_callback'][0];
                  $method = $section['footer_callback'][1];
                  $params = $section['footer_callback'][2] ?? null;
                  if (method_exists($object, $method)) $submit_text = $object->$method($params);
                  break;
               }
            }
         } // ENDIF sections exist;

      }

      echo '    '; // Add spacer to line things up;

      if ($wrap_submit) echo '<p class="submit">';

      if ($submit_text) submit_button($submit_text, 'primary', 'submit', !$wrap_submit); 

      if (!empty($options['submit_button'])) echo $options['submit_button'];

      if ($wrap_submit) echo '</p>';

      echo ''
      . $n
      . $i . '</form>' . $n
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $i . USI_WordPress_Solutions_Static::divider(2, $this->name . ' END')
      . $i . USI_WordPress_Solutions_Static::divider(2)
      . $trailing_code
      ;

   } // page_render();

   public static function page_slug($prefix) {
      return $prefix . '-settings';
   } // page_slug();

   public function prefix() { 
      return $this->prefix;
   } // prefix();

   public function roles() { 
      return $this->roles;
   } // roles();

   function sections_load() {

      $jquery_save    = $this->jquery;
      $jquery_head    = '// BEGIN ' .$this->page_slug . PHP_EOL;
      $this->jquery  .= $jquery_head;

      $this->sections = $this->sections();

      if ($this->jquery == $jquery_save . $jquery_head) {
         $this->jquery  = $jquery_save;
      } else {
         $this->jquery .= '// END ' .$this->page_slug . PHP_EOL;
      }

      // Convert capabilities object to array();
      if (!empty($this->sections['capabilities']) && is_object($this->sections['capabilities'])) {
         $this->sections['capabilities'] = $this->sections['capabilities']->section;
      }

      // Convert diagnostics object to array();
      if (!empty($this->sections['diagnostics']) && is_object($this->sections['diagnostics'])) {
         $this->sections['diagnostics'] = $this->sections['diagnostics']->section;
      }

      // Convert updates object to array();
      if (!empty($this->sections['updates']) && is_object($this->sections['updates'])) {
         $this->sections['updates'] = $this->sections['updates']->section;
      }

      $labels = false;
      $notes  = 0;

      if ($this->sections) { // IF sections exist;

         foreach ($this->sections as $section_id => & $section) {

            if (empty($section)) continue;

            if (isset($section['localize_labels'])) $labels = ('yes' == $section['localize_labels']);
            if (isset($section['localize_notes']))  $notes  = (int)$section['localize_notes'];
            // The WordPress do_settings_sections() function renders the title before WordPress calls the section_render() function
            // which means the title is rendered before the previous tab <div> is closed, so we save the title under the usi-title 
            // property name, unset the title, then render the usi-title in the section_render() function when we want to;
            if (isset($section['title'])) {
               $section['usi-title'] = __($section['title'], $this->text_domain);
               unset($section['title']);
            }
            if (!empty($section['options']['css'])) {
               $this->options['css'] = $section['options']['css'];
            }

            foreach ($section['settings'] as $name => & $setting) {
               if ($labels && !empty($setting['label'])) $setting['label'] = __($setting['label'], $this->text_domain);
               if ($notes  && !empty($setting['notes'])) {
                  switch ($notes) {
                  case 1: $setting['notes'] =  __($setting['notes'], $this->text_domain); break;                              // __();
                  case 2: $setting['notes'] = ' &nbsp; <i>' . __($setting['notes'], $this->text_domain) . '</i>'; break;      // &nbsp; <i>__()</i>;
                  case 3: $setting['notes'] = '<p class="description">' . __($setting['notes'], $this->text_domain) . '</p>'; // <p class="description">__()</p>;
                  }
               }
            }
            unset($setting);
         }
         unset($section);

         if ($this->is_tabbed) {
            $prefix_tab  = $this->prefix . '-tab';
            $active_tab  = !empty($_POST[$prefix_tab]) ? $_POST[$prefix_tab] : (!empty($_GET['tab']) ? $_GET['tab'] : null);
            $default_tab = null;
            if ($this->sections) { // IF sections exist;
               foreach ($this->sections as $section_id => $section) {
                  if (empty($section) || !empty($section['not_tabbed'])) continue;
                  if (!$default_tab) $default_tab = $section_id;
                  if ($section_id == $active_tab) {
                     $this->active_tab = $active_tab;
                     break;
                  }
               }
            } // ENDIF sections exist;
            if (!$this->active_tab) $this->active_tab = $default_tab;
         }

      } // ENDIF sections exist;

   } // sections_load();

   function section_render() {

      $i  = '  ';
      $i2 = '    ';
      $i3 = '      ';
      $n  = PHP_EOL;
      $n2 = PHP_EOL . PHP_EOL;

      $section_id = $this->section_ids[$this->section_callback_offset];

      if ($this->is_tabbed) {

         if ($this->section_callback_offset) {
            $old_section_id = $this->section_ids[$this->section_callback_offset - 1];
            $section_name   = $this->page_slug . '-' . $old_section_id;
            if (empty($this->sections[$section_id]['not_tabbed'])) {
               echo $i2 . '</div>' . USI_WordPress_Solutions_Static::divider(10, $section_name);
            } else {
               echo $i3 .  USI_WordPress_Solutions_Static::divider(6, $section_name);
            }
         }

         $section_name = $this->page_slug . '-' . $section_id;
         if (empty($this->sections[$section_id]['not_tabbed'])) {
            echo $n2 . $i2 .  USI_WordPress_Solutions_Static::divider(4, $section_name);
            echo $i2 . '<div id="' . $section_name . '"' . ($this->active_tab != $section_id ? ' style="display:none;"' : '') . '>' . $n;
         } else {
            echo $i3 .  USI_WordPress_Solutions_Static::divider(6, $section_name);
         }
      }

      if (!empty($this->sections[$section_id]['usi-title'])) echo "      <h2>{$this->sections[$section_id]['usi-title']}</h2>\n";

      if (isset($this->sections[$section_id]['grid'])) self::set_grid($this->sections[$section_id]['grid']);

      $section_callback = $this->section_callbacks[$this->section_callback_offset];
      if (!empty($section_callback[0]) && !empty($section_callback[1])) {
         $object = $section_callback[0];
         $method = $section_callback[1];
         if (method_exists($object, $method)) $object->$method($section_callback[2] ?? null);
      }

      $this->section_callback_offset++;

   } // section_render();

   function sections() { // Should be over ridden by extending class;
      return null;
   } // sections();

   function sections_footer($params) {
      echo '    '; // Add spacer to line things up;
      submit_button(__($params, $this->text_domain), 'primary', 'submit', true); 
      return null;
   } // sections_footer();

   function sections_header($html) {
      echo $html;
   } // sections_header();

   private static function set_grid($grid) {
      self::$grid = (('none' == $grid) || ('over' == $grid)) ? $grid : false;
   } // set_grid();

   public function set_options($section, $name, $value) { 
      $this->options[$section][$name] = $value;
   } // set_options();

   public function text_domain() { 
      return $this->text_domain;
   } // text_domain();

} // Class USI_WordPress_Solutions_Settings;

// --------------------------------------------------------------------------------------------------------------------------- // ?>