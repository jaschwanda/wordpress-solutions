# usi-wordpress-solutions #

The WordPress-Solutions plugin is a helper class used by various WordPress plugins and themes developed by Universal Solutions.

## Installation ##
This module should be installed in the usi-wordpress-solutions folder under the main WordPress plugins folder.

### mPDF Installation
Go to the /extractions/mypdf folder and enter:
```
composer require mpdf/mpdf
```
to get the latest version of mpdf.

### TinyMCE Installation
Go to the /extractions/tinymce_5.7.1/tinymce/js/tinymce folder and copy everything to the tinyMCE folder.

## Debugging ##
Some notes to help debugging some WordPress issues.

### Admin menu gap when debug mode is enabled issue
Sometimes when debug mode is enabled, there is a 2em gap between WordPress admin menu and the bar.
That CSS gap will show when there is a "Hidden" error if the following conditions are true:
```
if ($error && WP_DEBUG && WP_DEBUG_DISPLAY && ini_get('display_errors'))
```
To show the "Hidden" error do the following...
```
wp-admin/admin-header.php on line 201

$error = error_get_last();
error_log('=== This is the hidden error =================');
error_log(print_r($error, true));
error_log('=== Error Setting display ====================');
error_log('WP_DEBUG');
error_log(print_r(WP_DEBUG, true));
error_log('WP_DEBUG_DISPLAY');
error_log(print_r(WP_DEBUG_DISPLAY, true));
error_log('display_errors ini setting');
error_log(print_r(ini_get('display_errors'), true));
```
The debug log file can be found in the wp-content folder.

## License ##
> WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

> WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with Variable-Solutions.  If not, see 
<http://www.gnu.org/licenses/>.

## Donations ##
Donations are accepted at <a href="https://www.usi2solve.com/donate/wordpress-solutions">www.usi2solve.com/donate</a>. Thank you for your support!