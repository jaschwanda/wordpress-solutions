<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

class USI_WordPress_Solutions_Capabilities {

   const VERSION = '2.1.6 (2019-10-18)';

   private $capabilities = null;
   private $disable_save = true;
   private $name = null;
   private $prefix = null;
   private $role = null;
   private $role_id = null;
   private $prefix_select_user = null;
   private $text_domain = null;
   private $user = null;
   private $user_id = null;

   protected $options = null;

   private function __construct($name, $prefix, $text_domain, $capabilities, & $options, $translate = true) {

      if ($translate) foreach ($capabilities as $key => $value) $capabilities[$key] = __($value, $text_domain);

      $this->capabilities = $capabilities;
      $this->name         = $name;
      $this->options      = & $options;
      $this->prefix       = $prefix;
      $this->prefix_select_user = $this->prefix . '-select-user';
      $this->text_domain  = $text_domain;

   } // __construct();

   function after_add_settings_section($settings) {

      // Get the role and selected user, if none given then get the last ones modified by the user;

      $current_user_id = get_current_user_id();

      $role_id_option_name = $this->prefix . '-options-role-id';
      $user_id_option_name = $this->prefix . '-options-user-id';

      $option_role_id = get_user_option($role_id_option_name, $current_user_id);
      $option_user_id = get_user_option($user_id_option_name, $current_user_id);

      if (empty($option_role_id)) $option_role_id = 'administrator';
      if (empty($option_user_id)) $option_user_id = $current_user_id;

      $this->role_id = (!empty($_REQUEST['role_id']) ? $_REQUEST['role_id'] : $option_role_id);
      $this->user_id = (!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $option_user_id);

      $this->disable_save = true;

      $this->role = get_role($role_id = $this->role_id);

      if ($this->prefix_select_user != $this->role_id) {
         $select_user = false;
      } else {
         $select_user = true;
         $this->user = new WP_User($this->user_id);
         // IF last user selected has been removed then use current user;
         if (empty($this->user)) $this->user = new WP_User($this->user_id = $current_user_id);
         if (!empty($this->user->roles) && is_array($this->user->roles)) {
            foreach ($this->user->roles as $role_id) {
               switch ($role_id) {
               case 'subscriber':
               case 'contributor':
               case 'author':
               case 'editor':
               case 'administrator': $this->role = get_role($role_id); break 2;
               }
            }
         }
      }

      // FOREACH of the capabilities;
      foreach ($settings as $field_id => & $attributes) {
         $capability_name = $this->name . '-' . $field_id;
         // IF capability is inherited by role;
         if (!empty($this->role) && $this->role->has_cap($capability_name)) {
            $this->options['capabilities'][$field_id] = true;
            if ($select_user) {
               $attributes['readonly'] = true;
               $attributes['notes'] = ' <i>(' . 
                  sprintf(__("Set by user's %s role settings", $this->text_domain), ucfirst($role_id)) . 
               ')</i>';
            } else if ('administrator' == $this->role_id) {
               $attributes['readonly'] = true;
               $attributes['notes'] = ' <i>(' . __('Default setting for Administrator', $this->text_domain) . ')</i>';
            }
         // ELSEIF capability set by user;
         } else if ($select_user && $this->user->has_cap($capability_name)) {
            $this->options['capabilities'][$field_id] = true;
            $this->disable_save = false;
         } else { // ELSE role/user does not have capability;
            $this->options['capabilities'][$field_id] = false;
            $this->disable_save = false;
         } // ENDIF capability is inherited by role;
      } // ENDFOREACH of the capabilities;
      unset($attributes);

      if ($this->role_id != $option_role_id) update_user_option($current_user_id, $role_id_option_name, $this->role_id);
      if ($this->user_id != $option_user_id) update_user_option($current_user_id, $user_id_option_name, $this->user_id);

      return($settings);

   } // after_add_settings_section();

   function fields_sanitize($input) {

      $prefix_role_id = $this->prefix . '-role_id';

      if (!empty($_POST[$prefix_role_id])) {

         if ($this->prefix_select_user == $_POST[$prefix_role_id]) {
            foreach ($this->capabilities as $name => $capability) {
               $capability_name = $this->name . '-' . $name;
               // IF capability not set of it can be inherited by the user's role;
               if (empty($input['capabilities'][$name]) || (!empty($this->role) && $this->role->has_cap($capability_name))) {
                  $this->user->remove_cap($capability_name);
               } else { // ELSE the capability has been set;
                  $this->user->add_cap($capability_name);
               }
            }
         } else {
            foreach ($this->capabilities as $name => $capability) {
               $capability_name = $this->name . '-' . $name;
               !empty($input['capabilities'][$name]) ? $this->role->add_cap($capability_name) : $this->role->remove_cap($capability_name);
            }
         }

      }

      return($input);

   } // fields_sanitize();

   function render_section() {
      echo 
         '    <p>' . sprintf(__('The %s plugin enables you to set the role capabilites system wide or for a specific user on a user-by-user basis. Select the role or specific user you would like to edit and then check or uncheck the desired capabilites for that role or user.', $this->text_domain), $this->name) . '</p>' . PHP_EOL .
         '    <label>' . __('Capabilities for', $this->text_domain) . ' : </label>' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-role_id" value="' . $this->role_id . '" />' . PHP_EOL .
         '    <input type="hidden" name="' . $this->prefix . '-user_id" value="' . $this->user_id . '" />' . PHP_EOL .
         '    <select id="' . $this->prefix . '-role-select">' . PHP_EOL . 
         '      <option value="' . $this->prefix . '-select-user">' . __('Select User', $this->text_domain) . '</option>';
      wp_dropdown_roles($this->role_id);
      echo PHP_EOL . 
         '    </select>' . PHP_EOL;
         if ($this->prefix_select_user == $this->role_id) {
            wp_dropdown_users(array('id' => $this->prefix . '-user-select', 'selected' => $this->user_id));
            if (!empty($this->user->roles) && is_array($this->user->roles)) {
               $comma = ' (';
               foreach ($this->user->roles as $role) {
                  echo $comma . ucfirst($role);
                  $comma = ', ';
               }
               echo ')';
            } else {
               echo ' (No role for this site)';
            }
         }
      echo PHP_EOL . 
         '<script>' . PHP_EOL .
         'jQuery(document).ready(function($) {' . PHP_EOL .
         "   var url = 'options-general.php?page=" . USI_WordPress_Solutions_Settings::page_slug($this->prefix) . "&tab=capabilities&role_id='" . PHP_EOL .
         "   $('#{$this->prefix}-role-select').change(function(){window.location.href = url + $(this).val() + '&user_id={$this->user_id}';});" . PHP_EOL .
         "   $('#{$this->prefix}-user-select').change(function(){window.location.href = url + '{$this->role_id}' + '&user_id=' + $(this).val();});" . PHP_EOL .
         '});' . PHP_EOL .
         '</script>' . PHP_EOL;
   } // render_section();

   function section_footer() {
      submit_button(
         __('Save Capabilities', $this->text_domain),
        'primary', 
        'submit', 
        true, 
        $this->disable_save ? 'disabled' : null
      ); 
      return(null);
   } // section_footer();

   public static function section($name, $prefix, $text_domain, $capabilities, & $options) {

      $that = new USI_WordPress_Solutions_Capabilities($name, $prefix, $text_domain, $capabilities, $options);

      $section = array(
         'after_add_settings_section' => array($that, 'after_add_settings_section'),
         'fields_sanitize' => array($that, 'fields_sanitize'),
         'footer_callback' => array($that, 'section_footer'),
         'header_callback' => array($that, 'render_section'),
         'label' => __('Capabilities', $that->text_domain),
         'settings' => array(),
      );

      foreach ($that->capabilities as $name => $capability) {
         $section['settings'][$name] = array(
            'readonly' => false, 
            'label' => $capability, 
            'notes' => null, 
            'type' => 'checkbox'
         );
      }

      return($section);

   } // section();

} // Class USI_WordPress_Solutions_Capabilities;

// --------------------------------------------------------------------------------------------------------------------------- // ?>