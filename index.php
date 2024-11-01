<?php
/*
 * Plugin Name:   WPDevHub Link Manager
 * Version:       2.7
 * Plugin URI:    https://www.wpdevhub.com/wordpress-plugins/link-manager/
 * Description:   Create and Track custom link forwarders with a minimized URL off your own website.
 * Author:        WPDevHub
 * Author URI:    https://www.wpdevhub.com/
 */

define('WPDEVHUB_CONST_DLM_PLUGIN_TITLE', 'WPDevHub Links');
define('WPDEVHUB_CONST_DLM_APP_CODE', 'dlm');
define('WPDEVHUB_CONST_DLM_CLASSES_BROWSER', true);
define('WPDEVHUB_CONST_DLM_CLASSES_VIRTUAL_PAGES', true);

// Standard Setup Steps
include dirname(__FILE__).'/inc/inc.setup.php';

// Class Includes
include dirname(__FILE__).'/classes/class.DSCF_DLM_Links_Main.php';
include dirname(__FILE__).'/classes/class.DSCF_DLM_Links_Link.php';
include dirname(__FILE__).'/classes/class.DSCF_DLM_Links_Hit.php';

add_action( 'init', array('DSCF_DLM_Links_Main', 'wpActionInit'), 0 );

$controller = DSCF_DLM_VirtualPages_Controller::getController();
add_action( WPDEVHUB_CONST_DLM_SLUG.'_virtual_pages', function( $controller ) {

    $linkSlugBase = DSCF_DLM_Links_Main::getSlugBase();

    // Plugin Page
    $controller->addPage( new DSCF_DLM_VirtualPages_Page( $linkSlugBase.'/%%hash_key%%' ) )
        ->setTitle( 'Link Controller' )
        ->setTemplate( 'pages/wpdevhub-dlm-vp-link-handler.php' );

} );

// Shortcodes
add_shortcode( WPDEVHUB_CONST_DLM_SLUG, array('DSCF_DLM_Links_Main', 'shortcodeHandler') );

