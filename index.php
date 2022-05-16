<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kazbek.dev
 * @since             1.0.0
 * @package           Byte_Secure
 *
 * @wordpress-plugin
 * Plugin Name:       Byte Secure 
 * GitHub Plugin URI: https://github.com/kazbekkadalashvili/byte-secure
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Kazbek Kadalashvili
 * Author URI:        https://kazbek.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       byte-secure
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once (__DIR__.'/vendor/autoload.php');

add_action( 'admin_menu', 'byte_secure_add_admin_menu' );
add_action( 'admin_init', 'byte_secure_settings_init' );


function byte_secure_add_admin_menu(  ) { 
	add_options_page( 'Byte Secure ', 'Byte Secure ', 'manage_options', 'byte_secure', 'byte_secure_options_page' );
}


function byte_secure_settings_init() { 

	register_setting( 'byte_secure_plugin_page', 'byte_secure_settings' );

	add_settings_section(
		'byte_secure_plugin_page_section', 
		__( '', 'byte-secure' ), 
		'byte_secure_settings_section_callback', 
		'byte_secure_plugin_page'
	);

	add_settings_field( 
		'byte_secure_security_headers', 
		__( 'Security Headers', 'byte-secure' ), 
		'byte_secure_security_headers_render', 
		'byte_secure_plugin_page', 
		'byte_secure_plugin_page_section' 
	);

	add_settings_field( 
		'byte_secure_bot_protection', 
		__( 'Bot Protection', 'byte-secure' ), 
		'byte_secure_bot_protection_render', 
		'byte_secure_plugin_page', 
		'byte_secure_plugin_page_section' 
	);

	add_settings_field( 
		'byte_secure_request_to_file', 
		__( 'Request to File', 'byte-secure' ), 
		'byte_secure_request_to_file_render', 
		'byte_secure_plugin_page', 
		'byte_secure_plugin_page_section' 
	);


}


function byte_secure_security_headers_render() { 

	$options = get_option( 'byte_secure_settings' );
	?>
	<input type='checkbox' name='byte_secure_settings[byte_secure_security_headers]' <?php checked( $options['byte_secure_security_headers'], 1 ); ?> value='1'>
	<?php

}


function byte_secure_bot_protection_render() { 

	$options = get_option( 'byte_secure_settings' );
	?>
	<input type='checkbox' name='byte_secure_settings[byte_secure_bot_protection]' <?php checked( $options['byte_secure_bot_protection'], 1 ); ?> value='1'>
	<?php

}


function byte_secure_request_to_file_render() { 

	$options = get_option( 'byte_secure_settings' );
	?>
	<input type='checkbox' name='byte_secure_settings[byte_secure_request_to_file]' <?php checked( $options['byte_secure_request_to_file'], 1 ); ?> value='1'>
	<?php

}


function byte_secure_settings_section_callback(  ) { 

	echo __( '', 'byte-secure' );

}


function byte_secure_options_page(  ) { 

		?>
        <div class="wrap">
            <form action='options.php' method='post'>

                <h2>Byte Secure </h2>
                <?php
                settings_fields( 'byte_secure_plugin_page' );
                do_settings_sections( 'byte_secure_plugin_page' );
                
                ?>
                <p class="submit"><input type="submit" name="byte_secure_submit" id="byte_secure_submit" class="button button-primary" value="Save Changes" style=""></p>
            </form>
        </div>
		<?php

}


add_action( 'admin_init', 'byte_secure_check_settings' );

function byte_secure_check_settings(){
    $options = get_option( 'byte_secure_settings' );
    
    if(!isset($_POST['byte_secure_submit'])){
        return;
    }

    $path = get_home_path() . '.htaccess';

    //does it exist?
    if (!file_exists($path) ) {
        $this->trace_log(".htaccess not found.");
        return;
    }

    $htaccess_file = file_get_contents($path);

    $rules = get_byte_secure_rules();

    $htaccess = preg_replace("/#\s?BEGIN\s?security.*?#\s?END\s?security/s", "", $htaccess_file);
    $htaccess = preg_replace("/\n+/", "\n", $htaccess);

    //insert rules before wordpress part.
    if (strlen($rules) > 0) {
        $wptag = "# BEGIN WordPress";
        if (strpos($htaccess, $wptag) !== false) {
            
            $htaccess = str_replace($wptag, $rules . $wptag, $htaccess);
        } else {
            $htaccess = $htaccess . $rules;
        }
        file_put_contents($path, $htaccess);
    }
}

function get_byte_secure_rules(){
    $options = $_POST['byte_secure_settings'];
    $rules = "";

    if(isset($options['byte_secure_security_headers']) AND $options['byte_secure_security_headers']){
        $rules .= "
        <IfModule mod_headers.c>
            Header set Strict-Transport-Security 'max-age=31536000' env=HTTPS
            Header set X-XSS-Protection '1; mode=block'
            Header set X-Content-Type-Options nosniff
            Header set X-Frame-Options DENY
            Header set Referrer-Policy: no-referrer-when-downgrade
            Header set Permissions-Policy 'self'
        </IfModule>
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{HTTP:Authorization} ^(.*)
            RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
        </IfModule>";
        $rules .= "\n";
    }

    if(isset($options['byte_secure_bot_protection']) AND  $options['byte_secure_bot_protection']){
        $rules .= '
        <IfModule mod_rewrite.c>
            RewriteEngine on
            RewriteCond %{HTTP_USER_AGENT} "^PetalBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^DotBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Mozilla.*Indy" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Mozilla.*NEWT" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Maxthon$" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^SeaMonkey$" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Acunetix" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^binlar" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^BlackWidow" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Bolt 0" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^BOT for JCE" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Bot mailto\:craftbot@yahoo\.com" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^casper" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^checkprivacy" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^ChinaClaw" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^clshttp" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^cmsworldmap" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Custo" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Default Browser 0" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^diavol" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^DIIbot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^DISCo" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^dotbot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Download Demon" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^eCatch" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^EirGrabber" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^EmailCollector" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^EmailSiphon" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^EmailWolf" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Express WebPictures" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^extract" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^ExtractorPro" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^EyeNetIE" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^feedfinder" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^FHscan" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^FlashGet" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^flicky" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^g00g1e" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^GetRight" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^GetWeb\!" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Go\!Zilla" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Go\-Ahead\-Got\-It" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^grab" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^GrabNet" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Grafula" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^harvest" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^HMView" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Image Stripper" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Image Sucker" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^InterGET" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Internet Ninja" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^InternetSeer\.com" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^jakarta" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Java" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^JetCar" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^JOC Web Spider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^kanagawa" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^kmccrew" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^larbin" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^LeechFTP" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^libwww" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Mass Downloader" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^microsoft\.url" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^MIDown tool" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^miner" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Mister PiX" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^MSFrontPage" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Navroad" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^NearSite" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Net Vampire" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^NetAnts" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^NetSpider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^NetZIP" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^nutch" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Octopus" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Offline Explorer" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Offline Navigator" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^PageGrabber" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Papa Foto" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^pavuk" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^pcBrowser" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^PeoplePal" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^planetwork" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^psbot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^purebot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^pycurl" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^RealDownload" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^ReGet" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Rippers 0" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^sitecheck\.internetseer\.com" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^SiteSnagger" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^skygrid" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^SmartDownload" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^sucker" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^SuperBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^SuperHTTP" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Surfbot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^tAkeOut" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Teleport Pro" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Toata dragostea mea pentru diavola" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^turnit" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^vikspider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^VoidEYE" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Web Image Collector" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebAuto" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebBandit" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebCopier" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebFetch" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebGo IS" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebLeacher" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebReaper" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebSauger" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Website eXtractor" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Website Quester" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebStripper" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebWhacker" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WebZIP" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Widow" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WPScan" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WWW\-Mechanize" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^WWWOFFLE" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Xaldon WebSpider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^Zeus" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "^zmeu" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "360Spider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "CazoodleBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "discobot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "EasouSpider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "ecxi" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "GT\:\:WWW" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "heritrix" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "HTTP\:\:Lite" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "HTTrack" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "ia_archiver" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "id\-search" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "IDBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Indy Library" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "IRLbot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "ISC Systems iRc Search 2\.1" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "LinksCrawler" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "LinksManager\.com_bot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "linkwalker" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "lwp\-trivial" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "MFC_Tear_Sample" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Microsoft URL Control" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Missigua Locator" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "MJ12bot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "panscient\.com" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "PECL\:\:HTTP" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "PHPCrawl" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "PleaseCrawl" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "SBIder" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "SearchmetricsBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Snoopy" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Steeler" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "URI\:\:Fetch" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "urllib" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Web Sucker" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "webalta" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "WebCollage" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "Wells Search II" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "WEP Search" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "XoviBot" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "YisouSpider" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "zermelo" [NC,OR]
            RewriteCond %{HTTP_USER_AGENT} "ZyBorg" [NC,OR]
            RewriteCond %{HTTP_REFERER} "^https?://(?:[^/]+\.)?semalt\.com" [NC,OR]
            RewriteCond %{HTTP_REFERER} "^https?://(?:[^/]+\.)?kambasoft\.com" [NC,OR]
            RewriteCond %{HTTP_REFERER} "^https?://(?:[^/]+\.)?savetubevideo\.com" [NC]
            RewriteRule ^.* - [F,L]
        </IfModule>
        ';
        $rules .= "\n";
    }
    

    if(isset($options['byte_secure_request_to_file']) AND $options['byte_secure_request_to_file']){
        $rules .= '
        <files xmlrpc.php>
            <IfModule mod_authz_core.c>
                Require all denied
            </IfModule>
            <IfModule !mod_authz_core.c>
                Order allow,deny
                Deny from all
            </IfModule>
        </files>
        
        <files wp-config.php>
            <IfModule mod_authz_core.c>
                Require all denied
            </IfModule>
            <IfModule !mod_authz_core.c>
                Order allow,deny
                Deny from all
            </IfModule>
        </files>

        Options -Indexes
        <IfModule mod_rewrite.c>
            RewriteEngine On
            
            RewriteRule ^wp-admin/install\.php$ - [F]
            RewriteRule ^wp-admin/includes/ - [F]
            RewriteRule !^wp-includes/ - [S=3]
            RewriteRule ^wp-includes/[^/]+\.php$ - [F]
            RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F]
            RewriteRule ^wp-includes/theme-compat/ - [F]
            RewriteCond %{REQUEST_FILENAME} -f
            RewriteRule (^|.*/)\.(git|svn)/.* - [F]
            
            RewriteRule ^wp\-content/uploads/.*\.(?:php[1-7]?|pht|phtml?|phps)\.?$ - [NC,F]
            
            RewriteRule ^wp\-content/plugins/.*\.(?:php[1-7]?|pht|phtml?|phps)\.?$ - [NC,F]
            RewriteRule ^wp\-content/themes/.*\.(?:php[1-7]?|pht|phtml?|phps)\.?$ - [NC,F]
            
            RewriteCond %{QUERY_STRING} \.\.\/ [OR]
            RewriteCond %{QUERY_STRING} \.(bash|git|hg|log|svn|swp|cvs) [NC,OR]
            RewriteCond %{QUERY_STRING} etc/passwd [NC,OR]
            RewriteCond %{QUERY_STRING} boot\.ini [NC,OR]
            RewriteCond %{QUERY_STRING} ftp: [NC,OR]
            RewriteCond %{QUERY_STRING} https?: [NC,OR]
            RewriteCond %{QUERY_STRING} (<|%3C)script(>|%3E) [NC,OR]
            RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|%3D) [NC,OR]
            RewriteCond %{QUERY_STRING} base64_decode\( [NC,OR]
            RewriteCond %{QUERY_STRING} %24&x [NC,OR]
            RewriteCond %{QUERY_STRING} 127\.0 [NC,OR]
            RewriteCond %{QUERY_STRING} (^|\W)(globals|encode|localhost|loopback)($|\W) [NC,OR]
            RewriteCond %{QUERY_STRING} (^|\W)(concat|insert|union|declare)($|\W) [NC,OR]
            RewriteCond %{QUERY_STRING} %[01][0-9A-F] [NC]
            RewriteCond %{QUERY_STRING} !^loggedout=true
            RewriteCond %{QUERY_STRING} !^action=jetpack-sso
            RewriteCond %{QUERY_STRING} !^action=rp
            RewriteCond %{HTTP_COOKIE} !wordpress_logged_in_
            RewriteCond %{HTTP_REFERER} !^http://maps\.googleapis\.com
            RewriteRule ^.* - [F]
        </IfModule>
        ';
    }
    
    if (strlen($rules) > 0) {
        $rules = "\n" . "# BEGIN security\n" . $rules . "\n# END security\n";
    }

    return $rules;

}