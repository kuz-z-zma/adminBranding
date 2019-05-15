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

zp_register_filter('admin_head', 'zpBranding::customZpLogo');

class zpBranding {

	function __construct() {
		$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/zp-admin-logo';
		$matching = safe_glob($path . ".*");
		if (count($matching) !== 0) {
			$path_parts = pathinfo(array_shift($matching));
			$file = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/'.$path_parts['basename'];
			list($width) = getimagesize($file);
			setOptionDefault('width', $width);
			//setOptionDefault('height', $height);
			setOptionDefault('restore', 0);
			} else { ?>
				<div class="errorbox">
				<?php echo gettext_pl('No image found.', 'zp-branding'); exitZP(); ?>
				</div>
			<?php
			}
	}

	function getOptionsSupported() {
		return array(	gettext_pl('Width','zp-branding') => array('key' => 'width', 'type' => OPTION_TYPE_TEXTBOX,
										'order'=> 1,
										'desc' => gettext_pl('The width of the image (px). (the height is proportional)','zp-branding')),
						gettext_pl('Reset','zp-branding') => array('key' => 'restore', 'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 3,
										'desc' => gettext_pl('Reset to the original width.','zp-branding'))
		);
	}

	static function customZpLogo() {
		global $_zp_current_admin_obj,$_zp_admin_tab,$_zp_admin_subtab,$_zp_gallery;
		if (zp_loggedin(ADMIN_RIGHTS)) {
		$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/zp-admin-logo';
		$matching = safe_glob($path . ".*");
		if (count($matching) == 1) { // check if there is more than one file with the name "zp-admin-logo".
			$path_parts = pathinfo(array_shift($matching));
			$ext = $path_parts['extension'];
				if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
					$file = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/'.$path_parts['basename'];
					$title = $alt = html_encode($_zp_gallery->getTitle() . ' ' . gettext_pl('administration', 'zp-branding'));

					list($width) = getimagesize($file);

					if ( getOption('width') ) {
						$new_width = getOption('width');
						} else {
						$new_width = $width;
						setOption('width', $width);
					}

					?>
					<script type="text/javascript">
					// <!-- <![CDATA[
						$(document).ready(function(){
							$('#administration img#logo')
							.prop("src","<?php echo $file; ?>")
							.prop("title","<?php echo $title; ?>")
							.prop("alt","<?php echo $alt; ?>")
							.css({'width':'<?php echo $new_width; ?>px', 'height':'auto'});
						});
					// ]]> -->
					</script>
					<?php
					} else { ?>
						<div class="errorbox">
						<?php echo gettext_pl('No valid file type found.','zp-branding'); ?>
						</div>
					<?php
					}
			} else {
				if (count($matching) > 1) { ?>
				<div class="errorbox">
				<?php echo gettext_pl('ERROR: There is more than one file with the name <code>zp-admin-logo</code>','zp-branding'); ?>
				</div>
			<?php
			}
			}
		}
	}

	function handleOptionSave() {
		$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/zp-admin-logo';
		$matching = safe_glob($path . ".*");
		$path_parts = pathinfo(array_shift($matching));
		$file = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/'.$path_parts['basename'];
		list($width, $height) = getimagesize($file);
		if (getOption('restore')) {
			setOption('width', $width);
			purgeOption('restore');
		}
	}
}
?>