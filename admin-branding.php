<?php
/*
 * Plugin was released as a separate work after discussion with fretzl, author of the original zp-branding.
 * Inspired by zp-branding plugin, adminBranding was completely rewritten and changes logic of operation, by getting rid of included custom Logo image (to protect files from being overwritten on the update).
 * Instead user can mix and match plugin Options to combine custom and default elements for their desired look.
 *
 * Custom Logo, Background and CSS files should be placed in "/uploaded/design" folder of your Zenphoto install (check name of folder specific to your install in notes for relevant Options).
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
 * ## Installation:
 * 
 * Place the file `admin-branding.php` and `/admin-branding/` folder into `/plugins/` directory of your Zenphoto install, enable plugin in the Zenphoto options and customize plugin options to suit your vision.
 *
 * @author kuzzzma, based on zp-branding v1.4 plugin by Fred Sondaar (fretzl)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext_pl("Customization of Zenphoto backend: custom Logo, custom Background, Text and Links styling. Option to include custom CSS to alter appearence of any element in Admin area.", "admin-branding");
$plugin_author = "kuzzzma, based on ver. 1.4 zp-branding plugin by Fred Sondaar (fretzl)";
$plugin_siteurl = 'https://github.com/kuz-z-zma/adminBranding';
$plugin_disable = (zp_has_filter('admin_head') && extensionEnabled('zp-branding')) ? gettext_pl('Only one Zenphoto backend customization plugin may be enabled. Please disable zp-branding plugin to use this one.','admin-branding') : '';
$plugin_notice = gettext_pl('Make sure NOT to enable both zp-branding and admin-branding at the same time to avoid conflicts.','admin-branding');
$plugin_version = '1.1';
$plugin_category = gettext_pl("Admin", "admin-branding");
$option_interface = 'adminbrandingOptions';

if ($plugin_disable) {
    enableExtension('adminBranding', 0);
} else {
    zp_register_filter('admin_head', 'adminbranding::printCustomAdminLogo');
}

$zp_adminbranding_logo = FULLWEBPATH . '/' . ZENFOLDER . '/images/zen-logo.png';

class adminbrandingOptions {

    function __construct() {
        setOptionDefault('adminbranding_logo-width', '200');
        setOptionDefault('adminbranding_logo-image', 'default');
        setOptionDefault('adminbranding_background-image', 'default');
        setOptionDefault('adminbranding_background-repeat', '');
        setOptionDefault('adminbranding_css-custom', '');
    }

    function getOptionsSupported() {
        global $zp_adminbranding_logo;
        if ( $zp_adminbranding_logo ) {
        $width = getimagesize($zp_adminbranding_logo)[0];
        $options = array(

/*---------------- Logo Options ----------------*/

        gettext_pl('Logo for Admin', 'admin-branding') => array('key' => 'adminbranding_logo-image', 'type' => OPTION_TYPE_RADIO,
            'order' => 1,
            'buttons' => array(
                gettext_pl('No Logo', 'admin-branding') => 'disabled',
                gettext_pl('Custom Logo', 'admin-branding') => 'custom',
                gettext_pl('Default Zenphoto Logo', 'admin-branding') => 'default'),
            'desc' => gettext_pl('Choose if you want to use show Logo in Admin area.', 'admin-branding')),
        gettext_pl('Admin Logo Width', 'admin-branding') => array('key' => 'adminbranding_logo-width', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 2,
            'desc' => gettext_pl('The width of the Logo Image (in px). The height will be calculated proportionally.', 'admin-branding')),
        gettext_pl('Choose Admin Logo Image', 'admin-branding') => array('key' => 'adminbranding_logo-custom', 'type' => OPTION_TYPE_CUSTOM, 
            'order' => 4, 
            'desc' => sprintf(gettext_pl('Select a Logo image (from files in the <em>%s</em> folder) or select to use a default Zenphoto Logo for Admin area.<br>If you use elFinder plugin for Uploads - it can upload files to this folder, alternatively you can use FTP to upload your image file and then select it here.', 'admin-branding'),(UPLOAD_FOLDER.'/design/'))),
        gettext_pl('Admin Logo Margins', 'admin-branding') => array('key' => 'adminbranding_margins', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 5,
            'desc' => gettext_pl('Margins for Admin logo, listed as CSS <em>Margin shorthand</em> property values (WITHOUT final " ; " !). If no value provided - default Zenphoto values are used.', 'admin-branding')),

/*---------------- Background Options ----------------*/

        gettext_pl('Background Color for Admin', 'admin-branding') => array('key' => 'adminbranding_background-color', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 10,
            'desc' => gettext_pl('Specify Background Color for Admin area by providing any value, accepted by CSS standarts (Hex, RGB, RGBA, HSL, HSLA, color names).<br>If no value provided - default Zenphoto values are used.', 'admin-branding')),
        gettext_pl('Background Image for Admin', 'admin-branding') => array('key' => 'adminbranding_background-image', 'type' => OPTION_TYPE_RADIO,
            'order' => 11,
            'buttons' => array(
                gettext_pl('No Background Image', 'admin-branding') => 'disabled',
                gettext_pl('Custom Image', 'admin-branding') => 'custom',
                gettext_pl('Default Image', 'admin-branding') => 'default'),
            'desc' => gettext_pl('Choose if you want to use Background image in Admin area.', 'admin-branding')),
        gettext_pl('Choose Admin Background Image', 'admin-branding') => array('key' => 'adminbranding_background-custom', 'type' => OPTION_TYPE_CUSTOM, 
            'order' => 12, 
            'desc' => sprintf(gettext_pl('Select a background image (from files in the <em>%s</em> folder) or select to use a default Zenphoto Background for Admin area.<br>If you use elFinder plugin for Uploads - it can upload files to this folder, alternatively you can use FTP to upload your image file and then select it here.', 'admin-branding'),(UPLOAD_FOLDER.'/design/'))),
        gettext_pl('Background Image repeat options', 'admin-branding') => array('key' => 'adminbranding_background-repeat', 'type' => OPTION_TYPE_SELECTOR,
            'order' => 13,
            'selections' => array(
                gettext_pl('Repeat vertically and horizontally', 'admin-branding') => 'repeat',
                gettext_pl('Repeat X-axis (horizontally)', 'admin-branding') => 'repeat-x',
                gettext_pl('Repeat Y-axis (vertically)', 'admin-branding') => 'repeat-y',
                gettext_pl('No Repeating', 'admin-branding') => 'no-repeat',
                gettext_pl('Fill/Stretch/Shrink', 'admin-branding') => 'round',
                gettext_pl('Default', 'admin-branding') => ''),
            'desc' => gettext_pl('Choose how Background Image will be repeated. Default is Repeat X-axis (horizontally).', 'admin-branding')),

/*---------------- Links and Text Options ----------------*/

        gettext_pl('Admin Text color', 'admin-branding') => array('key' => 'adminbranding_text-color', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 14,
            'desc' => gettext_pl('Specify Text color for Admin Header and Footer text by providing any value, accepted by CSS standarts.<br>If no value provided - default Zenphoto values are used.', 'admin-branding')),
        gettext_pl('Admin Links color', 'admin-branding') => array('key' => 'adminbranding_links-color', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 15,
            'desc' => gettext_pl('Specify Links color for Admin Header and Footer text by providing any value, accepted by CSS standarts.<br>If no value provided - default Zenphoto values are used.', 'admin-branding')),
        gettext_pl('Admin Links:hover color', 'admin-branding') => array('key' => 'adminbranding_links-hover', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 16,
            'desc' => gettext_pl('Specify Links color on hover for Admin Header and Footer text by providing any value, accepted by CSS standarts.<br>If no value provided - default Zenphoto values are used.', 'admin-branding')),

/*---------------- CSS Options ----------------*/

        gettext_pl('Custom CSS', 'admin-branding') => array('key' => 'adminbranding_css-custom', 'type' => OPTION_TYPE_TEXTAREA,
            'order' => 17,
            'multilingual' => 0,
            'desc' => gettext_pl('Enter custom CSS to alter appearance of the Admin area further. It is printed between &lt;style&gt; tags in the &lt;head&gt; section.', 'admin-branding')),
        gettext_pl('Custom CSS File', 'admin-branding') => array('key' => 'adminbranding_css-custom-file', 'type' => OPTION_TYPE_CUSTOM,
            'order' => 18,
            'desc' => sprintf(gettext_pl('Select your custom CSS file (from CSS files in the <em>%s</em> folder).<br>If you use elFinder plugin for Uploads - it can upload files to this folder, alternatively you can use FTP to upload your CSS files and then select it here.', 'admin-branding'),(UPLOAD_FOLDER.'/design/')))
        );

        if (getOption('adminbranding_logo-width', 'admin-branding') != $width ) {
            $options[gettext_pl('Restore Logo Width', 'admin-branding')] = array('key' => 'adminbranding_logo-width-restore', 'type' => OPTION_TYPE_CHECKBOX,
            'order' => 3,
            'desc' => gettext_pl('Restore Logo width to the original value.', 'admin-branding'));
            }
        return $options;
        } else { ?>
            <div class="errorbox">
            <?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", "admin-branding"), substr($zp_adminbranding_logo, strrpos($zp_adminbranding_logo, '/') + 1)); ?>
            </div>
        <?php }
    }

    function handleOption($option, $currentValue) {

        if($option == "adminbranding_logo-custom") { ?>
            <select id="adminbranding_logo-custom" name="adminbranding_logo-custom">
                <option value="" style="background-color:LightGray"><?php echo gettext_pl('*Not specified', 'admin-branding'); ?></option>';
                <?php zp_apply_filter('theme_head');
                generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/design/','');	?>
            </select>
            <?php }

        if($option == "adminbranding_background-custom") { ?>
            <select id="adminbranding_background-custom" name="adminbranding_background-custom">
                <option value="" style="background-color:LightGray"><?php echo gettext_pl('*Not specified', 'admin-branding'); ?></option>';
                <?php zp_apply_filter('theme_head');
                generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/design/','');	?>
            </select>
            <?php }
        
        if($option == "adminbranding_css-custom-file") { ?>
            <select id="adminbranding_css-custom-file" name="adminbranding_css-custom-file">
                <option value="" style="background-color:LightGray"><?php echo gettext_pl('* no custom CSS file', 'admin-branding'); ?></option>';
                <?php zp_apply_filter('theme_head');
                generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/design/','.css'); ?>
            </select>
            <?php }
    }

    function handleOptionSave() {
        global $zp_adminbranding_logo;
        $width = getimagesize($zp_adminbranding_logo)[0];
        if (getOption('adminbranding_logo-width-restore')) {
            setOption('adminbranding_logo-width', $width);
            setOption('adminbranding_logo-width-restore', 0);
            }
    }
}

class adminbranding {

    static function printCustomAdminLogo() {
        global $zp_adminbranding_logo;
        if ((getOption('adminbranding_logo-image') == 'custom') && (getOption('adminbranding_logo-custom') != '')) {
            $zp_adminbranding_logo = FULLWEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('adminbranding_logo-custom');
        }

        if (getimagesize($zp_adminbranding_logo)) {// Check if file is image
            $width = getimagesize($zp_adminbranding_logo)[0];
            $height = getimagesize($zp_adminbranding_logo)[1];
            $ratio = round($height / $width, 2);
            setOptionDefault('adminbranding_logo-width', $width);
            setOptionDefault('adminbranding_logo-width-restore', 0);
            if (getOption('adminbranding_logo-width')) {
                $new_width = getOption('adminbranding_logo-width');
                $height = ceil($new_width * $ratio);
            } else {
                $new_width = $width;
                setOption('adminbranding_logo-width', $width);
            }
            ?>

<?php if (!empty(getOption('adminbranding_css-custom-file'))) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('adminbranding_css-custom-file')); echo ('.css') ?>" />
   <?php } ?>

<style>
    body {
<?php if (getOption('adminbranding_background-color') != '') { ?>
        background-color: <?php echo getOption('adminbranding_background-color'); ?>;
<?php } ?>
<?php if (getOption('adminbranding_background-image') == 'disabled') { ?>
        background-image: none;
<?php } elseif ((getOption('adminbranding_background-image') == 'custom') && (getOption('adminbranding_background-custom') != '')) {?>
        background-image: url("<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('adminbranding_background-custom')); ?>");
<?php } ?>
<?php if (getOption('adminbranding_background-repeat')!='') { ?>
        background-repeat: <?php echo getOption('adminbranding_background-repeat'); ?>;
<?php } ?> }

<?php if (getOption('adminbranding_text-color')!='') { ?>
    #links,
    #footer {
        color: <?php echo getOption('adminbranding_text-color'); ?>;
    }
<?php } ?>

<?php if (getOption('adminbranding_links-color')!='') { ?>
    #links a, 
    #links a em, 
    #footer a {
        color: <?php echo getOption('adminbranding_links-color'); ?>;
    }
<?php } ?>

<?php if (getOption('adminbranding_links-hover')!='') { ?>
    #links a:hover, 
    #links a:hover em, 
    #footer a:hover {
        color: <?php echo getOption('adminbranding_links-hover'); ?>;
        text-decoration: none;
        border-bottom: 1px solid <?php echo getOption('adminbranding_links-hover'); ?>;
    }
<?php } ?>

<?php if (getOption('adminbranding_logo-image') == 'disabled') { ?>
    #logo {
        display: none; 
    }
<?php } ?>
<?php if ((getOption('adminbranding_logo-image') == 'custom') && (getOption('adminbranding_logo-custom') != '')) {?>
    #logo {
        display: none;
    }

    #administration {
        width: <?php echo $new_width; ?>px;
        height: <?php echo $height; ?>px;
        background: url("<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('adminbranding_logo-custom')); ?>") no-repeat 0 0;
        background-size: <?php echo $new_width; ?>px;
<?php if (getOption('adminbranding_margins')!='') { ?>
        margin: <?php echo getOption('adminbranding_margins'); ?>;
<?php } ?> }
<?php } ?>
<?php if (getOption('adminbranding_logo-image') == 'default') { ?>
    #administration {
        width: <?php echo $new_width; ?>px;
        height: <?php echo $height; ?>px;
<?php if (getOption('adminbranding_margins')!='') { ?>
        margin: <?php echo getOption('adminbranding_margins'); ?>;
<?php } ?> }
<?php } ?>

<?php if ( !empty(getOption('adminbranding_css-custom')) ) {
    echo "\n/**----------- Custom CSS -----------**/\n" . getOption('adminbranding_css-custom') . "\n" . "/*-----------End of Custom CSS-----------------*/" . "\n";
   } ?>
</style>

<?php } else { ?>
        <div class="errorbox">
        <?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", "admin-branding"), substr($zp_adminbranding_logo, strrpos($zp_adminbranding_logo, '/') + 1)); ?>
        </div>
<?php }
    }
}
?>