# usi-wordpress-solutions #

The WordPress-Solutions plugin is a helper class used by various WordPress plugins and themes developed by 
Universal Solutions.

## Installation ##
This module should be installed in the usi-wordpress-solutions folder under the main WordPress plugins folder. 
There are also some third party packages that must be installed if you use them:

### PHPSpreadsheet Installation
If you want to create and download Microsoft Excel documents then you have to install PHPOffice/PHPSpreadsheet 
which is a pure PHP library for reading and writing spreadsheet files. Go to the /PHPSpreadsheet 
folder and enter:
```
composer require phpoffice/phpspreadsheet
```
to get the latest version of PHPSpreadsheet. 
See [https://github.com/PHPOffice/PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) and [https://phpspreadsheet.readthedocs.io](https://phpspreadsheet.readthedocs.io) for more information.

PHPSpreadsheet is the next version of PHPExcel. 
It breaks compatibility to dramatically improve the code base quality 
(namespaces, PSR compliance, use of latest PHP language features, etc.).
Because all efforts have shifted to PhpSpreadsheet, PHPExcel will no longer be maintained. 

### PHPWord Installation
If you want to create and download Microsoft Word documents then you have to install PHPOffice/PHPWord 
which is a pure PHP library for reading and writing word processing documents. 
Go to the /extractions/phpword folder and enter:
```
composer require phpoffice/phpword
```
to get the latest version of PHPWordt. 
See [https://github.com/PHPOffice/PHPWord](https://github.com/PHPOffice/PHPWord) and [https://phpword.readthedocs.io](https://phpword.readthedocs.io) for more information.

### mPDF Installation
If you want to create and download PDF documents then you have to install mPDF. Go to the /extractions/mypdf folder and enter:
```
composer require mpdf/mpdf
```
to get the latest version of mpdf.

### TinyMCE Installation
Go to the /extractions/tinymce_5.7.1/tinymce/js/tinymce folder and copy everything to the tinyMCE folder.

## Debugging ##
Notes to help debug some WordPress issues.

### Deprecation errors with PHP version 8+
WordPress gives many deprecation errors when running in PHP version 8+ with debugging enabled. 
You can disable these errors but still enable debugging by doing the following:

```
in the wp-config.php file:

// Turn off default debugging:
define('WP_DEBUG', false); 

// Turn back on  debugging options without E_DEPRECATED;
error_reporting(E_ALL&~(E_DEPRECATED|E_USER_DEPRECATED));
$GLOBALS['wp_filter']=['enable_wp_debug_mode_checks'=>[10=>[['accepted_args'=>0,'function'=>function(){return(false);}]]]];
```

### Admin menu gap issue when debug mode is enabled
Sometimes when debug mode is enabled there is a 2em gap between WordPress admin menu and the bar.
This gap will show when there is a "Hidden" error if the following conditions are true:
```
if ($error && WP_DEBUG && WP_DEBUG_DISPLAY && ini_get('display_errors'))
```
To show the "Hidden" error do the following...
```
in the wp-config.php file:

define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', false|true);
define('WP_DEBUG_LOG', true); // Or give the desired log file path; 

in the wp-admin/admin-header.php file around line 201:

$error = error_get_last();
error_log('=== This is the "Hidden" error =================');
error_log(print_r($error, true));
error_log('=== Error Setting display ======================');
error_log('WP_DEBUG');
error_log(print_r(WP_DEBUG, true));
error_log('WP_DEBUG_DISPLAY');
error_log(print_r(WP_DEBUG_DISPLAY, true));
error_log('display_errors ini setting');
error_log(print_r(ini_get('display_errors'), true));
```
The default location of the debug.log file is in the wp-content folder.


## Pop Ups ##
This plugin provides enhanced pop functionality.
```
class USI_WordPress_Solutions_Popup_Action is used in:
 * ru-application.php
 * ru-docusign.php
 * ru-form.php
 * ru-work.php
 * usi-media-solutions-manage.php

class USI_WordPress_Solutions_Popup_Iframe is used in:
 * ru-email-inbox.php
 * usi-test-solutions-settings.php
 * usi-wordpress-solutions-versions.php
 * usi-wordpress-solutions-settings-settings.php
```


## License ##
> WordPress-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

> WordPress-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with Variable-Solutions.  If not, see 
<http://www.gnu.org/licenses/>.

## Donations ##
Donations are accepted at <a href="https://www.usi2solve.com/donate/wordpress-solutions">www.usi2solve.com/donate</a>. Thank you for your support!