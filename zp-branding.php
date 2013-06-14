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
	
	function getOptionsSupported() {
		return array(	gettext('Width') => array('key' => 'width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The width of the image (px)')),
						gettext('Height') => array('key' => 'height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext('The height of the image (px)'))
		);
	}
	
	static function customZpLogo() {
		global $_zp_current_admin_obj,$_zp_admin_tab,$_zp_admin_subtab,$_zp_gallery;
		if (zp_loggedin(ADMIN_RIGHTS)) {
		$new_src = WEBPATH.'/'.USER_PLUGIN_FOLDER.'/zp-branding/zp-admin-logo.png';
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
		}
	}
}
?>