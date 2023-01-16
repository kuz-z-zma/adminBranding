<?php
/**
 * Replace the default Zenphoto logo on the backend with a custom logo.
 * Name the logo <code>zp-admin-logo.png</code> and place it in the <code>/plugins/zp-branding</code> folder.
 *
 * @author Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Replace the default Zenphoto logo on the backend with a custom logo.",'zp-branding');
$plugin_author = "Fred Sondaar (fretzl)";
$plugin_category = gettext('Admin');
$plugin_version = '1.3';
$option_interface = 'zpBranding';

zp_register_filter('admin_head', 'zpBranding::customZpLogo', 999);
		
class zpBranding {

	function __construct() {
		purgeOption('restore');
		purgeOption('width');
		setOptionDefault('zpbranding-width', '200');
		setOptionDefault('zpbranding-restore', 0);
	}

	function getOptionsSupported() {
		return array(gettext_pl('Width','zp-branding') => array('key' => 'zpbranding-width', 'type' => OPTION_TYPE_TEXTBOX,
						'order'=> 1,
						'desc' => gettext_pl('The width of the image (px). (the height is proportional)','zp-branding')),
					gettext_pl('Reset','zp-branding') => array('key' => 'zpbranding-restore', 'type' => OPTION_TYPE_CHECKBOX,
						'order'=> 3,
						'desc' => gettext_pl('Reset to the original width.','zp-branding'))
		);
	}

	static function customZpLogo() {
			$logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';
			if (getimagesize($logo)) {// Check if file is image
				$width = getimagesize($logo)[0];
				$height = getimagesize($logo)[1];
				$ratio = round($height / $width, 2);
				setOptionDefault('zpbranding-width', $width);
				setOptionDefault('zpbranding-restore', 0);
				if (getOption('zpbranding-width')) {
					$new_width = getOption('zpbranding-width');
					$height = intval($new_width * $ratio);
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
					background: url(<?php echo $logo; ?>) no-repeat 50% 50%;
					background-size: <?php echo $new_width; ?>px auto;
				}
				</style>
				<script>
				document.addEventListener('DOMContentLoaded', function() { 
					document.getElementById('logo').remove(); 
				});
				</script>
				<?php
			} else { ?>
				<div class="errorbox">
				<?php echo gettext_pl('No image found.', 'zp-branding'); ?>
				</div>
			<?php
			}
	}
 

	function handleOptionSave() {
		$logo = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/zp-branding/zp-admin-logo.png';
		$width = getimagesize($logo)[0];
		if (getOption('zpbranding-restore')) {
			setOption('zpbranding-width', $width);
			purgeOption('zpbranding-restore');
		}
	}
}
?>