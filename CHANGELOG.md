# Change Log #

WordPress-Solutions plugin changes are logged here using <a href="http://semver.org/">Semantic Versioning</a>.

## 2.16.3 (2023-11-27) ##
* Modified to use proper coding constructs.

## 2.16.2 (2023-10-10) ##
* Added custom code execution via short code.

## 2.16.1 (2023-09-26) ##
* Added temp directory for mPDF.

## 2.16.0 (2023-09-15) ##
* Added autoloader for USI plugins.
* Set all versions numbers to same version.

## 2.15.5 (2023-07-07) ##
* Added styling to trasnfer page.

## 2.15.4 (2023-07-06) ##
* Added common WordPress helper functions for returning filter values.

## 2.15.3 (2023-07-03) ##
* Moved the usi::log() method out of the plugin and into it's own must-use plugin.

## 2.15.2 (2023-07-02) ##
* Added disable visual editing in classic editor option.

## 2.15.1 (2023-06-30) ##
* Removed admin functions from main file and put into admin file.

## 2.15.0 (2023-06-30) ##
* Set all versions numbers to same version.

## 2.14.11 (2023-06-20) ##
* Fixed popup handling, added e-mail cloaking.

## 2.14.10 (2023-05-22) ##
* Improved popup handling.

## 2.14.9 (2023-05-19) ##
* Corrected minor typos and issue with plugin version display.

## 2.14.8 (2023-03-16) ##
* Modified so that missings settings sections doesn't throw error.

## 2.14.7 (2023-03-15) ##
* Added PDF watermark, updated code comments.

## 2.14.6 (2023-02-07) ##
* Upgraded the mPDF system to version 8.1.4.

## 2.14.5 (2022-12-19) ##
* Modifications made for PHP 8.1 compatibility.

## 2.14.4 (2022-11-01) ##
* Modifications made for PHP 8.1 compatibility.

## 2.14.3 (2022-10-05) ##
* Consolidated jQuery script handling.

## 2.14.2 (2022-09-23) ##
* Improved xPorter functionality to Transfer.

## 2.14.1 (2022-08-10) ##
* Adding PHPMailer support.
* Set all versions numbers to same version.

## 2.14.0 (2022-06-19) ##
* Set all versions numbers to same version.

## 2.13.1 (2022-03-17) ##
* Added more illumination variables.

## 2.13.0 (2022-02-22) ##
* Added first pass for a form designer/layout tool.
* Set all versions numbers to same version.

## 2.12.15 (2022-02-03) ##
* Added more illumination variables.

## 2.12.14 (2022-02-01) ##
* Added more illumination variables.

## 2.12.13 (2022-01-30) ##
* Added more illumination variables.

## 2.12.12 (2022-01-26) ##
* Added more illumination variables.

## 2.12.11 (2022-01-25) ##
* Added illumination variables.

## 2.12.10 (2022-01-24) ##
* Enhanced security.

## 2.12.9 (2022-01-23) ##
* Modified so that bad folder does not give errors.

## 2.12.8 (2022-01-21) ##
* Modified so that setting changes the pcre.backtrack_limit value for PDF downloads.

## 2.12.7 (2022-01-19) ##
* Added setting to set the pcre.backtrack_limit value.

## 2.12.6 (2022-01-18) ##
* Updated to print error message in the PDF file when PDF creation fails.

## 2.12.5 (2022-01-16) ##
* phpinfo() scan now requires administrator privileges.

## 2.12.4 (2022-01-15) ##
* Added USI_WordPress_Solutions_Logged_In class which uses cookies to verify if user is logged in.

## 2.12.3 (2022-01-14) ##
* Added bootstrap to load WordPress functions from an external .PHP file.

## 2.12.2 (2021-11-29) ##
* Apdated settings functions.

## 2.12.1 (2021-11-16) ##
* Used ?? operator in get_value() function.

## 2.12.0 (2021-11-03) ##
* Set all versions numbers to same version.

## 2.11.17 (2021-10-01) ##
* Added USI_WordPress_Solutions_PDF class.
* Fixed some PHP warning messages.

## 2.11.16 (2021-07-23) ##
* Added $active_tab_maybe variable.

## 2.11.15 (2021-07-14) ##
* Added headers and footers to PDF dumps.

## 2.11.14 (2021-06-24) ##
* Addressed an issue where checked check boxes couldn't be cleared.

## 2.11.13 (2021-06-16) ##
* Added check in processing settings.

## 2.11.12 (2021-06-15) ##
* Added clear function to datepicker widget.

## 2.11.11 (2021-06-11) ##
* Changed usi::log() to accomondate the Page Solutions plugin.

## 2.11.10 (2021-06-10) ##
* Set jQuery datepicker plugin date format.

## 2.11.9 (2021-06-09) ##
* Fixed some PHP warnings resulting from new PHP version.
* Re-ordered some functions alphavetically.

## 2.11.8 (2021-05-29) ##
* Added datepicker support.
* Modified to ensure backwards compatibility with PHP 5.4 versions.

## 2.11.7 (2021-05-25) ##
* Added PHP error reporting function to settings, addressed false errors in mPDF creation.

## 2.11.6 (2021-05-18) ##
* Added feature to remove the password reset link option from the row actions in the user display.

## 2.11.5 (2021-05-12) ##
* Added .usi-ignore file to skip sub folder from version scanning in the all-scan module.

## 2.11.4 (2021-05-06) ##
* Added e-mail field type and validation function.
* Added .usi-ignore file to skip sub folder from version scanning.

## 2.11.3 (2021-04-20) ##
* Set all versions numbers to same version.
* Added TinyMCE editor to settings functionality.
* Increseaed the size of the USI::log() action field from TEXT to MEDIUMTEXT.
* Made USI::log() class protected by if defined logic so that file can be included anywhere.

## 2.11.2 (2021-03-21) ##
* Moved section_header() so that all sub-classes can utilize.

## 2.11.1 (2021-03-10) ##
* Changed esc_attr() to esc_textarea() for text area settings fields.

## 2.11.0 (2021-02-24) ##
* Set all versions numbers to same version.

## 2.10.7 (2021-02-02) ##
* Added ability to force user logout from active sessions list.

## 2.10.6 (2021-01-20) ##
* Added diagnostic logging to post export feature.

## 2.10.5 (2021-01-04) ##
* Converted the USI_WordPress_Solutions_Settings::action_init() function to be static so it could be called by other plugins to impersonate a user.

## 2.10.4 (2020-12-21) ##
* Improved money field functionality.

## 2.10.3 (2020-12-04) ##
* Added remove_directory() method to the USI_WordPress_Solutions_Static class.

## 2.10.2 (2020-11-18) ##
* Added eXporter to settings to export posts, updated popup usage list, added money settings field type.

## 2.10.1 (2020-11-02) ##
* Added USI_WordPress_Solutions_Popup_Action class.
* Added USI_WordPress_Solutions_Popup_Iframe class.
* Added USI_WordPress_Solutions_Versions_All class.
* Made list table improvements.
* Made session display and history tracking improvements.
* Set all versions numbers to same version.

## 2.9.9 (2020-09-27) ##
* Added scan all plugin/theme versions.

## 2.9.8 (2020-09-24) ##
* Added disabled/readonly for radio buttons.

## 2.9.7 (2020-09-22) ##
* Added free-format settings page rendering.

## 2.9.6 (2020-09-15) ##
* Removed expired users from logged in users list.

## 2.9.5 (2020-09-14) ##
* Added forced update capabilities to the USI_WordPress_Update class.

## 2.9.0 (2020-07-30) ##
* Set all versions numbers to same version.

## 2.8.0 (2020-07-27) ##
* Reworked the USI_WordPress_Solutions_Popup class.

## 2.7.5 (2020-07-22) ##
* Added admin_footer_script() method for queuing dynamic scripts.

## 2.7.4 (2020-07-20) ##
* Improved the post upfdate log function so that non-changed saves are not logged.

## 2.7.3 (2020-07-06) ##
* Added USI_WordPress_Solutions_Static::is_int() method.

## 2.7.2 (2020-06-16) ##
* Added USI_WordPress_Solutions_Capabilities::remove() method to remove capabilities on plugin deletion.

## 2.7.1 (2020-06-12) ##
* Updated USI_WordPress_Solutions_Static::action_admin_head() to not emit redendant css and also fixed an index error.

## 2.7.0 (2020-06-08) ##
* Added diagnostics session tracking.
* Added current users logged in list.
* Added history tracking.
* Added free format mode for settings pages.
* added simple list table example page.
* Set all versions numbers to same version.

## 2.5.1 (2020-05-07) ##
* Added logging logger option to diagnostices get_log() method.
* Added user action logging.
* Set all versions numbers to same version.

## 2.4.18 (2020-05-07) ##
* Improved uninstall support.

## 2.4.17 (2020-05-07) ##
* Added history support.

## 2.4.16 (2020-05-02) ##
* Added diagnostics support.

## 2.4.15 (2020-04-26) ##
* Address issue with GitLab updates.

## 2.4.12 (2020-04-19) ##
* Added popup static class.
* Added logging static class.
* Set all versions numbers to same version.

## 2.4.11 (2020-03-31) ##
* Added 'action_admin_head' convenience function to new static class.
* Added 'visual-grid' option to 'diagnostics'.

## 2.4.10 (2020-03-22) ##
* Added 'column_style' convenience function to new static class.

## 2.4.9 (2020-03-16) ##
* Added 'current_user_can' convenience function to capabilities.

## 2.4.8 (2020-03-09) ##
* Set all versions numbers to same version.

## 2.4.7 (2020-02-28) ##
* Added query parameter option for tabbed settings.

## 2.4.6 (2020-02-27) ##
* Fixed a foreach loop error.

## 2.4.5 (2020-02-26) ##
* Improved settings text localization.

## 2.4.4 (2020-02-19) ##
* Updated capability and updates handling, set all versions numbers to same version.

## 2.4.3 (2020-02-11) ##
* Added capability option to settings page menu item creation.

## 2.4.2 (2020-02-10) ##
* Added fields_render_select() function to settings.

## 2.4.1 (2020-02-09) ##
* Moved settings load sections code to the action_admin_init() function.

## 2.4.0 (2020-02-04) ##
* Improved update handling.

## 2.3.8 (2020-02-02) ##
* Refractored code to handle multiple repository sources for updates.

## 2.3.7 (2020-01-31) ##
* Refractored code to handle multiple repository sources for updates.

## 2.3.6 (2020-01-30) ##
* Fix some null access bugs and started to add support for GitLab updates.

## 2.3.5 (2020-01-25) ##
* Improved ability to string multiple fields on the same line by enabling a conditional over ride of WordPress settings API functions.

## 2.3.4 (2020-01-24) ##
* Added drop down select fields to settings page and ability to string multiple fields on the same line.

## 2.3.3 (2020-01-20) ##
* Add functionality to use settings functions for non-settings sub pages.

## 2.3.2 (2020-01-08) ##
* Added license and copyright notice.

## 2.3.1 (2020-01-01) ##
* Added usi-wordpress-solutions-updates.php to facilitate the addition of an 'Updates' tab in the settings page.

## 2.3.0 (2019-12-12) ##
* Added usi-wordpress-solutions-update.php for downloading directly from GIT.

## 2.2.0 (2019-12-11) ##
* Added phpinfo(), reworked thickbox and updated all versions to same versions.

## 2.1.3 (2019-07-07) ##
* Added 'html' option to settings fields, improved version scanning to include themes, updated all versions to same versions.

## 2.1.1 (2019-06-29) ##
* Added a 'skip' option for a field to exclue it from rendering based on latter logic condition.

## 2.1.0 (2019-06-08) ##
* Changed the name to 'WordPress-Solutions', updated all versions to same versions.

## 2.0.0 (2019-04-13) ##
* Removed classes from sub-folder under parent plugin and made a stand alone class, changed the name to 'Settings-Solutions'.

## 1.2.0 (2018-01-13) ##
* Added debugging options to class USI_Settings_Admin and updated all versions to same version.

## 1.1.1 (2018-01-11) ##
* Updated USI_Settings_Admin to optionally load settings link, previous update over written some how.

## 1.1.0 (2018-01-10) ##
* Modified the version scanning function to scan recursively.
* Moved files to their folder and made it a Git submodule.

## 1.0.5 (2018-01-07) ##
* Updated USI_Settings_Admin to change scope of class properties and added options to page_render().

## 1.0.6 (2017-12-14) ##
* Added version list to plugins page.

## 1.0.0 (2017-10-29) ##
* Initial release.

