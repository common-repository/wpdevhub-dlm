<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 5/19/15
 * Time: 9:23 PM
 * To change this template use File | Settings | File Templates.
 */

if ( ! defined( 'ABSPATH' ) ) exit();	// sanity check

// Setup the Add Define Routine
if(!function_exists("dimbal_add_define")){
    function dimbal_add_define($key, $val) {
        if (!defined($key)) {
            define($key, $val);
            return true;
        }
        return false;
    }
}

// Define the Constants to control which classes are loaded
dimbal_add_define('WPDEVHUB_CONST_DLM_CLASSES_ZONES', false);
dimbal_add_define('WPDEVHUB_CONST_DLM_CLASSES_VIRTUAL_PAGES', false);
dimbal_add_define('WPDEVHUB_CONST_DLM_CLASSES_BROWSER', false);
dimbal_add_define('WPDEVHUB_CONST_DLM_CLASSES_DIMBALCOM', false);
dimbal_add_define('WPDEVHUB_CONST_DLM_CLASSES_CHART', false);

/********** INCLUDES **********/
$classes = array();

// Core Files
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardCustomPostType.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardEditor.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardGroupRecord.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardLinkRecord.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardMain.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardManager.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardMetaBox.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardMetaBoxAndDbObject.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardMetaBoxObject.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardObjectRecord.php';
$classes[] = dirname(__FILE__).'/../classes/core/class.DSCF_DLM_StandardSetting.php';


// Utilities
$classes[] = dirname(__FILE__).'/../classes/class.DSCF_DLM_Utilities.php';
$classes[] = dirname(__FILE__).'/../classes/class.DSCF_DLM_Box.php';
$classes[] = dirname(__FILE__).'/../classes/class.DSCF_DLM_MessagePopup.php';

// Dimbal Com
if(WPDEVHUB_CONST_DLM_CLASSES_DIMBALCOM){
    $classes[] = dirname(__FILE__).'/../classes/com/class.DSCF_DLM_ComRequest.php';
    $classes[] = dirname(__FILE__).'/../classes/com/class.DSCF_DLM_ComDispatch.php';
}

// Zone Manager
if(WPDEVHUB_CONST_DLM_CLASSES_ZONES){
    $classes[] = dirname(__FILE__).'/../classes/zone/class.DSCF_DLM_ZoneManager.php';
    $classes[] = dirname(__FILE__).'/../classes/zone/class.DSCF_DLM_Zone.php';
    $classes[] = dirname(__FILE__).'/../classes/zone/class.DSCF_DLM_ZoneItem.php';
}

// Virtual Pages
if(WPDEVHUB_CONST_DLM_CLASSES_VIRTUAL_PAGES){
    $classes[] = dirname(__FILE__).'/../classes/vp/interface.DSCF_DLM_VirtualPages_PageInterface.php';
    $classes[] = dirname(__FILE__).'/../classes/vp/interface.DSCF_DLM_VirtualPages_ControllerInterface.php';
    $classes[] = dirname(__FILE__).'/../classes/vp/interface.DSCF_DLM_VirtualPages_TemplateLoaderInterface.php';
    $classes[] = dirname(__FILE__).'/../classes/vp/class.DSCF_DLM_VirtualPages_Page.php';
    $classes[] = dirname(__FILE__).'/../classes/vp/class.DSCF_DLM_VirtualPages_Controller.php';
    $classes[] = dirname(__FILE__).'/../classes/vp/class.DSCF_DLM_VirtualPages_TemplateLoader.php';
}

// Browser Helper
if(WPDEVHUB_CONST_DLM_CLASSES_BROWSER){
    $classes[] = dirname(__FILE__).'/../classes/class.DSCF_DLM_Browser.php';
}

// Chart Helper
if(WPDEVHUB_CONST_DLM_CLASSES_CHART){
    $classes[] = dirname(__FILE__).'/../classes/class.DSCF_DLM_Chart.php';
}

foreach($classes as $classpath){
    include($classpath);
}

// Setup Virtual Page Hook Controller
if(WPDEVHUB_CONST_DLM_CLASSES_VIRTUAL_PAGES){
    $virtualPageController = new DSCF_DLM_VirtualPages_Controller( new DSCF_DLM_VirtualPages_TemplateLoader() );
    add_action( 'init', array( $virtualPageController, 'init' ) );
    add_filter( 'do_parse_request', array( $virtualPageController, 'dispatch' ), PHP_INT_MAX, 2 );
    add_action( 'loop_end', function( \WP_Query $query ) {
        if ( isset( $query->virtual_page ) && ! empty( $query->virtual_page ) ) {
            $query->virtual_page = NULL;
        }
    } );
    add_filter( 'the_permalink', function( $plink ) {
        global $post, $wp_query;

        if (
            $wp_query->is_page
            && isset( $wp_query->virtual_page )
            && ($wp_query->virtual_page instanceof DSCF_DLM_VirtualPages_Page)
            && isset( $post->is_virtual )
            && $post->is_virtual
        ) {
            $plink = home_url( $wp_query->virtual_page->getUrl() );
        }
        return $plink;
    } );
}



// Base Constants that should be overridden
dimbal_add_define('WPDEVHUB_CONST_DLM_APP_CODE', 'undefined');
dimbal_add_define('WPDEVHUB_CONST_DLM_SLUG', 'wpdevhub-'.WPDEVHUB_CONST_DLM_APP_CODE);
dimbal_add_define('WPDEVHUB_CONST_DLM_FOLDER', 'wpdevhub-'.WPDEVHUB_CONST_DLM_APP_CODE);
dimbal_add_define('WPDEVHUB_CONST_DLM_DB_PREFIX', 'wpdevhub-'.WPDEVHUB_CONST_DLM_APP_CODE);
dimbal_add_define('WPDEVHUB_CONST_DLM_SETTINGS_PREFIX', WPDEVHUB_CONST_DLM_SLUG.'-');
dimbal_add_define('WPDEVHUB_CONST_DLM_URL', plugins_url() . "/" . WPDEVHUB_CONST_DLM_FOLDER);
dimbal_add_define('WPDEVHUB_CONST_DLM_DIR', WP_PLUGIN_DIR . '/' . WPDEVHUB_CONST_DLM_FOLDER);
dimbal_add_define('WPDEVHUB_CONST_DLM_URL_IMAGES', WPDEVHUB_CONST_DLM_URL . '/images');
dimbal_add_define('WPDEVHUB_CONST_DLM_PLUGIN_FILE', WPDEVHUB_CONST_DLM_DIR . '/index.php');
dimbal_add_define('WPDEVHUB_CONST_DLM_USE_UPDATER',false);      // Use the WordPress Updater by Default
dimbal_add_define('WPDEVHUB_CONST_DLM_PROMO_DCD',true);       // Safety switch to turn off promo'ing.
dimbal_add_define('WPDEVHUB_CONST_DLM_URL_SUBSCRIPTIONS', 'https://www.wpdevhub.com/subscriptions/');

// Pages
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_HOME', 'home');
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_ZONES', 'zones');
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_SETTINGS', 'settings');
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_REPORTS', 'reports');
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_PREVIEW', 'preview');
dimbal_add_define('WPDEVHUB_CONST_DLM_PAGE_SUPPORT', 'support');


// Zones
dimbal_add_define('WPDEVHUB_CONST_DLM_ZONE_GROUP_NAME', 'Zone');
dimbal_add_define('WPDEVHUB_CONST_DLM_ZONE_ITEM_NAME', 'Item');

// Environment Specific Loading
include dirname(__FILE__).'/inc.env.php';
include dirname(__FILE__).'/inc.ver.php';

