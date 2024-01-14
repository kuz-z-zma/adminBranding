<?php
/**
 * Replacement of the Zenphoto logo on the backend with a custom logo.
 *
 * Name the logo <code>zp-admin-logo.png</code> and place it in the <code>/plugins/zp-branding</code> folder.
 *
 * @author Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Replace the default Zenphoto logo on the backend with a custom logo.", 'zp-branding');
$plugin_author = "Fred Sondaar (fretzl)";
$plugin_version = '1.4';
$plugin_category = gettext('Admin');
$option_interface = 'zpBrandingOptions';

zp_register_filter('admin_head', 'zpBranding::printCustomZpLogo');

class zpBrandingOptions {
	
	function __construct() {
		purgeOption('width');// older version option name
		purgeOption('restore');// older version option name
		setOptionDefault('zpbranding-width', '200');
		setThemeOptionDefault('zpbranding_customcss', '');
	}

	static function getOptionsSupported() {
		$zp_branding_logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';
		if ( $zp_branding_logo ) {
			$width = getimagesize($zp_branding_logo)[0];
			$options = array(    
					gettext_pl('Width', 'zp-branding') => array('key' => 'zpbranding-width', 'type' => OPTION_TYPE_TEXTBOX,
						'order'=> 1,
						'desc' => gettext_pl('The width of the image (px). The height is proportional.', 'zp-branding')),
					gettext_pl('Custom CSS', 'zp-branding') => array('key' => 'zpbranding_customcss', 'type' => OPTION_TYPE_TEXTAREA, 
						'order' => 3,
						'multilingual' => 0,
						'desc' => gettext_pl('Enter custom CSS to alter the appearance of the admin area.<br> It is printed between &lt;style&gt; tags in the &lt;head&gt; section.', 'zp-branding'))
				);
				if ( getOption('zpbranding-width') != $width ) {
					$options[gettext_pl('Reset', 'zp-branding')] = array('key' => 'zpbranding-restore', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => gettext_pl('Reset to the original width.', 'zp-branding'));
				}			
		return $options;
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(gettext_pl("Image <i>%s</i> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
	
	function handleOptionSave() {
		$zp_branding_logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';
		$width = getimagesize($zp_branding_logo)[0];
		if (getOption('zpbranding-restore')) {
			setOption('zpbranding-width', $width);
			setOption('zpbranding-restore', 0);
		}
	}
}

class zpBranding {

	static function printCustomZpLogo() {
		$zp_branding_logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';
		$relativeLogo = str_replace( FULLWEBPATH, '..', $zp_branding_logo );
		if (getimagesize($zp_branding_logo)) {// Check if file is image
			$width = getimagesize($zp_branding_logo)[0];
			$height = getimagesize($zp_branding_logo)[1];
			$ratio = round($height / $width, 2);
			setOptionDefault('zpbranding-width', $width);
			setOptionDefault('zpbranding-restore', 0);
			if (getOption('zpbranding-width')) {
				$new_width = getOption('zpbranding-width');
				$height = ceil($new_width * $ratio);
			} else {
				$new_width = $width;
				setOption('zpbranding-width', $width);
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
				background: url(<?php echo $relativeLogo; ?>) no-repeat 0 0;
				background-size: <?php echo $new_width; ?>px;
			}
			<?php
			if ( !empty(getOption('zpbranding_customcss')) ) {
				echo "\n/** Custom CSS **/\n" . getOption('zpbranding_customcss') . "\n" . "/****************/" . "\n";
			}
			?>
			</style>
			<script>
			document.addEventListener('DOMContentLoaded', function() { 
				document.getElementById('logo').remove(); 
			});
			</script>
			<?php
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(gettext_pl("Image <i>%s</i> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
}
?>