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
$plugin_description = gettext("Replace the default Zenphoto logo on the backend with a custom logo.");
$plugin_author = "Fred Sondaar (fretzl)";

$option_interface = 'zpBranding';

zp_register_filter('admin_head', 'zpBranding::customZpLogo');

class zpBranding {

	function __construct() {
		$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/zp-admin-logo';
		$matching = safe_glob($path . ".*");
		if (count($matching) !== 0) {
			$path_parts = pathinfo(array_shift($matching)); 
			$file = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/'.$path_parts['basename'];
			list($width, $height) = getimagesize($file);
			setOptionDefault('width', $width);
			setOptionDefault('height', $height);
			setOptionDefault('restore', 0);
			} else { ?>
				<div class="errorbox">
				<?php echo gettext('No image found.'); exitZP(); ?>
				</div>
			<?php
			}	
	}
	
	function getOptionsSupported() {
		return array(	gettext('Width') => array('key' => 'width', 'type' => OPTION_TYPE_TEXTBOX,
										'order'=> 1,
										'desc' => gettext('The width of the image (px). Default is the original width.')),
						gettext('Height') => array('key' => 'height', 'type' => OPTION_TYPE_TEXTBOX,
										'order'=> 2,
										'desc' => gettext('The height of the image (px). Default is the original height.')),
						gettext('Reset') => array('key' => 'restore', 'type' => OPTION_TYPE_CHECKBOX,
										'order'=> 3,
										'desc' => gettext('Reset to the original width and height.'))
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
					$file = WEBPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/'.$path_parts['basename'];
					$new_src = $file;
					$new_title = sprintf(gettext('%1$s administration'),html_encode($_zp_gallery->getTitle()),html_encode($_zp_admin_tab));
					$new_alt = sprintf(gettext('%1$s administration'), html_encode($_zp_gallery->getTitle()));
					?>
					<script type="text/javascript">
					// <!-- <![CDATA[
						$(document).ready(function(){
							$('#administration img#logo')
							.prop("src","<?php echo $new_src; ?>")
							.prop("title","<?php echo $new_title; ?>")
							.prop("alt","<?php echo $new_alt; ?>")
							.css({'width':'<?php echo getOption("width"); ?>', 'height':'<?php echo getOption("height"); ?>'});
						});
					// ]]> -->
					</script>
					<?php
					} else { ?>
						<div class="errorbox">
						<?php echo gettext('No valid file type found.'); ?>
						</div>
					<?php
					}
			} else { 
				if (count($matching) > 1) { ?>
				<div class="errorbox">
				<?php echo gettext('ERROR: There is more than one file with the name <code>zp-admin-logo</code>'); ?>
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
			setOption('height', $height);
			purgeOption('restore');
		}
	}
}
?>