<?php
/*
 * Version 2.0 was completely rewritten and changes logic of operation, by getting rid of included custom Logo image (to protect files from being overwritten on the update).
 * Instead user can mix and match plugin Options to combine custom and default elements for their desired look.
 *
 * Custom Logo and Background files should be placed in "/uploaded/design" folder of your Zenphoto install (check name of folder specific to your install in notes for relevant Options).
 * 
 * You can select Custom files to use, use Default Logo and Background or disable them altogether.
 * Specify new Width for Logo to change it's scale if needed.
 * Specify Margins for Logo by by providing standart CSS Margin shorthand property ("20px 0 10px 20px", or any variation with 1/2/3 values) without the closing ";" character.
 *
 * Custom colors for Links and Text target only small text in Header and Footer of Admin area.
 * Provide any value, accepted by CSS standarts (Hex, RGB, RGBA, HSL, HSLA, color names).
 *
 * Custom CSS allows to further change appearence of any Admin area elements.
 *
 * @author kuzzzma (ver. 2.0), originally by Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Customization of Zenphoto backend: custom Logo, custom Background, Text and Links styling. Option to include custom CSS to alter appearence of any element in Admin area.", "zp-branding");
$plugin_author = "kuzzzma (ver. 2.0), fork of ver. 1.4 by Fred Sondaar (fretzl)";
$plugin_version = '2.0';
$plugin_category = gettext('Admin');
$option_interface = 'zpBrandingOptions';

zp_register_filter('admin_head', 'zpBranding::printCustomZpLogo');

$zp_branding_logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';

class zpBrandingOptions {
	
	function __construct() {
		purgeOption('width');// older version option name
		purgeOption('restore');// older version option name
		setOptionDefault('zpbranding_logo-width', '200');
		setThemeOptionDefault('zpbranding_css-custom', '');
	}

	static function getOptionsSupported() {
		global $zp_branding_logo;
		if ( $zp_branding_logo ) {
			$width = getimagesize($zp_branding_logo)[0];
			$options = array(    
					gettext_pl('Width', 'zp-branding') => array('key' => 'zpbranding_logo-width', 'type' => OPTION_TYPE_TEXTBOX,
						'order'=> 1,
						'desc' => gettext_pl('The width of the image (px). The height is proportional.', 'zp-branding')),
					gettext_pl('Custom CSS', 'zp-branding') => array('key' => 'zpbranding_css-custom', 'type' => OPTION_TYPE_TEXTAREA, 
						'order' => 3,
						'multilingual' => 0,
						'desc' => gettext_pl('Enter custom CSS to alter the appearance of the admin area.<br> It is printed between &lt;style&gt; tags in the &lt;head&gt; section.', 'zp-branding'))
				);
				if ( getOption('zpbranding_logo-width') != $width ) {
					$options[gettext_pl('Reset', 'zp-branding')] = array('key' => 'zpbranding_logo-width-restore', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext_pl('Reset to the original width.', 'zp-branding'));
				}			
		return $options;
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
	
	function handleOptionSave() {
		global $zp_branding_logo;
		$width = getimagesize($zp_branding_logo)[0];
		if (getOption('zpbranding_logo-width-restore')) {
			setOption('zpbranding_logo-width', $width);
			setOption('zpbranding_logo-width-restore', 0);
		}
	}
}

class zpBranding {

	static function printCustomZpLogo() {
		global $zp_branding_logo;
		if (getimagesize($zp_branding_logo)) {// Check if file is image
			$width = getimagesize($zp_branding_logo)[0];
			$height = getimagesize($zp_branding_logo)[1];
			$ratio = round($height / $width, 2);
			setOptionDefault('zpbranding_logo-width', $width);
			setOptionDefault('zpbranding_logo-width-restore', 0);
			if (getOption('zpbranding_logo-width')) {
				$new_width = getOption('zpbranding_logo-width');
				$height = ceil($new_width * $ratio);
			} else {
				$new_width = $width;
				setOption('zpbranding_logo-width', $width);
			}
			?>
			<style>
			#logo {
				display: none;
			}
		
			#administration {
				width: <?php echo $new_width; ?>px;
				height: <?php echo $height; ?>px;
				margin: 20px 10px 0px 30px;
				background: url(<?php echo $zp_branding_logo; ?>) no-repeat 0 0;
				background-size: <?php echo $new_width; ?>px;
			}
			<?php
			if ( !empty(getOption('zpbranding_css-custom')) ) {
				echo "\n/** Custom CSS **/\n" . getOption('zpbranding_css-custom') . "\n" . "/****************/" . "\n";
			}
			?>
			</style>

			<?php
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
}
?>