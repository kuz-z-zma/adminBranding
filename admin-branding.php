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
$plugin_category = gettext_pl("Admin", "zp-branding");
$option_interface = 'zpBrandingOptions';

zp_register_filter('admin_head', 'zpBranding::printCustomZpLogo');

$zp_branding_logo = FULLWEBPATH . '/' . ZENFOLDER . '/images/zen-logo.png';

class zpBrandingOptions {

    function __construct() {
        setOptionDefault('zpbranding_logo-width', '200');
        setOptionDefault('zpbranding_logo-image', 'default');
        setOptionDefault('zpbranding_background-image', 'default');
        setOptionDefault('zpbranding_background-repeat', '');
        setOptionDefault('zpbranding_css-custom', '');
    }

    function getOptionsSupported() {
        global $zp_branding_logo;
        if ( $zp_branding_logo ) {
        $width = getimagesize($zp_branding_logo)[0];
        $options = array(

/*---------------- Logo Options ----------------*/

        gettext_pl('Logo for Admin', 'zp-branding') => array('key' => 'zpbranding_logo-image', 'type' => OPTION_TYPE_RADIO,
            'order' => 1,
            'buttons' => array(
                gettext_pl('No Logo', 'zp-branding') => 'disabled',
                gettext_pl('Custom Logo', 'zp-branding') => 'custom',
                gettext_pl('Default Zenphoto Logo', 'zp-branding') => 'default'),
            'desc' => gettext_pl('Choose if you want to use show Logo in Admin area.', 'zp-branding')),
        gettext_pl('Admin Logo Width', 'zp-branding') => array('key' => 'zpbranding_logo-width', 'type' => OPTION_TYPE_TEXTBOX,
            'order'=> 2,
            'desc' => gettext_pl('The width of the Logo Image (in px). The height will be calculated proportionally.', 'zp-branding')),
        gettext_pl('Choose Admin Logo Image', 'zp-branding') => array('key' => 'zpbranding_logo-custom', 'type' => OPTION_TYPE_CUSTOM, 
            'order' => 4, 
            'desc' => sprintf(gettext_pl('Select a Logo image (from files in the <em>%s</em> folder) or select to use a default Zenphoto Logo for Admin area. If you use elFinder plugin for Uploads - it can upload files to this folder, alternatively you can use FTP to upload your image file and then select it here.', 'zp-branding'),(UPLOAD_FOLDER.'/design/'))),
        gettext_pl('Admin Logo Margins', 'zp-branding') => array('key' => 'zpbranding_margins', 'type' => OPTION_TYPE_TEXTBOX,
          'order'=> 5,
          'desc' => gettext_pl('Margins for Admin logo, listed as CSS <em>Margin shorthand</em> property values (WITHOUT final " ; " !). If no value provided - default Zenphoto values are used.', 'zp-branding')),

/*---------------- Background Options ----------------*/

        gettext_pl('Background Color for Admin', 'zp-branding') => array('key' => 'zpbranding_background-color', 'type' => OPTION_TYPE_TEXTBOX,
          'order'=> 10,
          'desc' => gettext_pl('Specify Background Color for Admin area by providing any value, accepted by CSS standarts (Hex, RGB, RGBA, HSL, HSLA, color names). If no value provided - default Zenphoto values are used.', 'zp-branding')),
        gettext_pl('Background Image for Admin', 'zp-branding') => array('key' => 'zpbranding_background-image', 'type' => OPTION_TYPE_RADIO,
          'order' => 11,
          'buttons' => array(
            gettext_pl('No Background Image', 'zp-branding') => 'disabled',
            gettext_pl('Custom Image', 'zp-branding') => 'custom',
            gettext_pl('Default Image', 'zp-branding') => 'default'),
          'desc' => gettext_pl('Choose if you want to use Background image in Admin area.', 'zp-branding')),
        gettext_pl('Choose Admin Background Image', 'zp-branding') => array('key' => 'zpbranding_background-custom', 'type' => OPTION_TYPE_CUSTOM, 
          'order' => 12, 
          'desc' => sprintf(gettext_pl('Select a background image (from files in the <em>%s</em> folder) or select to use a default Zenphoto Background for Admin area. If you use elFinder plugin for Uploads - it can upload files to this folder, alternatively you can use FTP to upload your image file and then select it here.', 'zp-branding'),(UPLOAD_FOLDER.'/design/'))),
        gettext_pl('Background Image repeat options', 'zp-branding') => array('key' => 'zpbranding_background-repeat', 'type' => OPTION_TYPE_SELECTOR,
          'order' => 13,
          'selections' => array(
            gettext_pl('Repeat vertically and horizontally', 'zp-branding') => 'repeat',
            gettext_pl('Repeat X-axis (horizontally)', 'zp-branding') => 'repeat-x',
            gettext_pl('Repeat Y-axis (vertically)', 'zp-branding') => 'repeat-y',
            gettext_pl('No Repeating', 'zp-branding') => 'no-repeat',
            gettext_pl('Fill/Stretch/Shrink', 'zp-branding') => 'round',
            gettext_pl('Default', 'zp-branding') => ''),
          'desc' => gettext_pl('Choose how Background Image will be repeated. Default is Repeat X-axis (horizontally).', 'zp-branding')),

/*---------------- Links and Text Options ----------------*/

        gettext_pl('Admin Text color', 'zp-branding') => array('key' => 'zpbranding_text-color', 'type' => OPTION_TYPE_TEXTBOX,
          'order'=> 14,
          'desc' => gettext_pl('Specify Text color for Admin Header and Footer text by providing any value, accepted by CSS standarts. If no value provided - default Zenphoto values are used.', 'zp-branding')),
        gettext_pl('Admin Links color', 'zp-branding') => array('key' => 'zpbranding_links-color', 'type' => OPTION_TYPE_TEXTBOX,
          'order'=> 15,
          'desc' => gettext_pl('Specify Links color for Admin Header and Footer text by providing any value, accepted by CSS standarts. If no value provided - default Zenphoto values are used.', 'zp-branding')),
        gettext_pl('Admin Links:hover color', 'zp-branding') => array('key' => 'zpbranding_links-hover', 'type' => OPTION_TYPE_TEXTBOX,
          'order'=> 16,
          'desc' => gettext_pl('Specify Links color on hover for Admin Header and Footer text by providing any value, accepted by CSS standarts. If no value provided - default Zenphoto values are used.', 'zp-branding')),

/*---------------- CSS Options ----------------*/

        gettext_pl('Custom CSS', 'zp-branding') => array('key' => 'zpbranding_css-custom', 'type' => OPTION_TYPE_TEXTAREA,
          'order' => 17,
          'multilingual' => 0,
          'desc' => gettext_pl('Enter custom CSS to alter appearance of the Admin area further. It is printed between &lt;style&gt; tags in the &lt;head&gt; section.', 'zp-branding'))
        );

        if (getOption('zpbranding_logo-width', 'zp-branding') != $width ) {
            $options[gettext_pl('Restore Logo Width', 'zp-branding')] = array('key' => 'zpbranding_logo-width-restore', 'type' => OPTION_TYPE_CHECKBOX,
            'order' => 3,
            'desc' => gettext_pl('Restore Logo width to the original value.', 'zp-branding'));
            }
        return $options;
        } else { ?>
            <div class="errorbox">
            <?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", "zp-branding"), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
            </div>
        <?php }
    }

    function handleOption($option, $currentValue) {

        if($option == "zpbranding_logo-custom") { ?>
            <select id="zpbranding_logo-custom" name="zpbranding_logo-custom">
            <option value="" style="background-color:LightGray"><?php echo gettext_pl('*Not specified', 'zp-branding'); ?></option>';
            <?php zp_apply_filter('theme_head');
            generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/design/','');	?>
            </select>
            <?php }

        if($option == "zpbranding_background-custom") { ?>
            <select id="zpbranding_background-custom" name="zpbranding_background-custom">
            <option value="" style="background-color:LightGray"><?php echo gettext_pl('*Not specified', 'zp-branding'); ?></option>';
            <?php zp_apply_filter('theme_head');
            generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/design/','');	?>
            </select>
            <?php }
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
        if ((getOption('zpbranding_logo-image') == 'custom') && (getOption('zpbranding_logo-custom') != '')) {
            $zp_branding_logo = FULLWEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('zpbranding_logo-custom');
        }

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
    body {
    <?php if (getOption('zpbranding_background-color') != '') { ?>
        background-color: <?php echo getOption('zpbranding_background-color'); ?>;
    <?php } ?>
    <?php if (getOption('zpbranding_background-image') == 'disabled') { ?>
        background-image: none;
    <?php } elseif ((getOption('zpbranding_background-image') == 'custom') && (getOption('zpbranding_background-custom') != '')) {?>
        background-image: url("<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('zpbranding_background-custom')); ?>");
    <?php } ?>
    <?php if (getOption('zpbranding_background-repeat')!='') { ?>
        background-repeat: <?php echo getOption('zpbranding_background-repeat'); ?>;
    <?php } ?>
    }
    <?php if (getOption('zpbranding_text-color')!='') { ?>
    #links,
    #footer {
        color: <?php echo getOption('zpbranding_text-color'); ?>;
    }
    <?php } ?>

    <?php if (getOption('zpbranding_links-color')!='') { ?>
    #links a, 
    #links a em, 
    #footer a {
        color: <?php echo getOption('zpbranding_links-color'); ?>;
    }
    <?php } ?>

    <?php if (getOption('zpbranding_links-hover')!='') { ?>
    #links a:hover, 
    #links a:hover em, 
    #footer a:hover {
        color: <?php echo getOption('zpbranding_links-hover'); ?>;
        text-decoration: none;
        border-bottom: 1px solid <?php echo getOption('zpbranding_links-hover'); ?>;
    }
    <?php } ?>

   <?php if (getOption('zpbranding_logo-image') == 'disabled') { ?>
    #logo {
        display: none; 
    }
    <?php } ?>
    <?php if ((getOption('zpbranding_logo-image') == 'custom') && (getOption('zpbranding_logo-custom') != '')) {?>
    #logo {
        display: none;
    }

    #administration {
        width: <?php echo $new_width; ?>px;
        height: <?php echo $height; ?>px;
        background: url("<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/design/'.getOption('zpbranding_logo-custom')); ?>") no-repeat 0 0;
        background-size: <?php echo $new_width; ?>px;
    <?php if (getOption('zpbranding_margins')!='') { ?>
        margin: <?php echo getOption('zpbranding_margins'); ?>;
    <?php } ?>
    }
    <?php } ?>
    <?php if (getOption('zpbranding_logo-image') == 'default') {?>
    #administration {
        width: <?php echo $new_width; ?>px;
        height: <?php echo $height; ?>px;
    <?php if (getOption('zpbranding_margins')!='') { ?>
        margin: <?php echo getOption('zpbranding_margins'); ?>;
    <?php } ?> 
    } 
    <?php } ?>

<?php if ( !empty(getOption('zpbranding_css-custom')) ) {
    echo "\n/**----------- Custom CSS -----------**/\n" . getOption('zpbranding_css-custom') . "\n" . "/*-----------End of Custom CSS-----------------*/" . "\n";
   } ?>
</style>

    <?php } else { ?>
        <div class="errorbox">
        <?php echo sprintf(gettext_pl("Image <em>%s</em> does not exist.", "zp-branding"), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
        </div>
    <?php
    }
    }
}
?>