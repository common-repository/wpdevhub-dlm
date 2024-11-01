<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DSCF_DLM_Links_Main extends DSCF_DLM_StandardMain {

    static $classname = "DSCF_DLM_Links_Main";

    const CURRENT_VERSION = 3;

    const PAGE_HOME = "home";
    const PAGE_LINKS = "links";
    const PAGE_REPORTS = "reports";
    const PAGE_SETTINGS = "settings";

    const SC_ID_INSERT_LINK = 1;

    const SETTINGS_SLUG_BASE = "vp_slug_base";
    const SLUG_BASE = "go";

    /*
    * WordPress Action Hook for init :: Generally the first action available to the Plugins
    */
    public static function wpActionInit(){
        parent::wpActionInit();

        // Add CSS and JS Resources
        //self::addResourceToEnqueue($name, $url, $type);

        // Add AJAX Handlers
        //self::addAjaxMapping($name, $callable, $type);

        // Add CRON Hooks
        //self::addCronHandler($name, $callable, $schedule);

        // Add Widgets
        //self::addWidget($classname);

        // Add ShortCode
        self::addShortcode(DSCF_DLM_Links_Main::SC_ID_INSERT_LINK, array('DSCF_DLM_Links_Main','shortcodeHandler'));

        // Add Custom post types
        //self::addCustomPostType( DSCF_DLM_Quizzes_Quiz::KEYNAME , 'DSCF_DLM_Quizzes_Quiz' );

        // Random Filters, etc
        //global $wpdb;
        //DSCF_DLM_Utilities::migrateTableNameIfNeeded($wpdb->base_prefix."dimbal_dlm_links", DSCF_DLM_Links_Link::getTableName());
        //DSCF_DLM_Utilities::migrateTableNameIfNeeded($wpdb->base_prefix."dimbal_dlm_hits", DSCF_DLM_Links_Hit::getTableName());

    }


    public static function wpActionAdminMenu(){
        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        //add_menu_page( 'Dimbal Links', 'Dimbal Links', 'manage_options', DSCF_DLM_Utilities::buildPageSlug(self::PAGE_HOME), array('DSCF_DLM_Utilities','renderPage') );
        add_menu_page( 'WDH Links', 'WDH Links', 'manage_options', DSCF_DLM_Utilities::buildPageSlug(self::PAGE_LINKS), array('DSCF_DLM_Utilities','renderPage') );

        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        //add_submenu_page( DSCF_DLM_Utilities::buildPageSlug(self::PAGE_HOME), 'Links', 'Links', 'manage_options', DSCF_DLM_Utilities::buildPageSlug(self::PAGE_LINKS), array('DSCF_DLM_Utilities','renderPage'));
        //add_submenu_page( DSCF_DLM_Utilities::buildPageSlug(self::PAGE_HOME), 'Reports', 'Reports', 'manage_options', DSCF_DLM_Utilities::buildPageSlug(self::PAGE_REPORTS), array('DSCF_DLM_Utilities','renderPage'));
        add_submenu_page( DSCF_DLM_Utilities::buildPageSlug(self::PAGE_LINKS), 'Settings', 'Settings', 'manage_options', DSCF_DLM_Utilities::buildPageSlug(self::PAGE_SETTINGS), array('DSCF_DLM_Utilities','renderPage'));

        // Any hidden but useful pages
    }

    // Database Install Routine
    public static function installDatabase(){
        global $wpdb;

        // Get the DB CharSet
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "";

        // Setup the Basic Item Content Table
        $tt_table_name = DSCF_DLM_Links_Link::getTableName();
        $sql .= "
            CREATE TABLE $tt_table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                blogId BIGINT(20),
                hashKey varchar(50),
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            ";

        $tt_table_name = DSCF_DLM_Links_Hit::getTableName();
        $sql .= "
            CREATE TABLE $tt_table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                blogId BIGINT(20),
                linkId BIGINT(20),
                hitType BIGINT(20),
                hitDate BIGINT(20),
                browserId BIGINT(20),
                platformId BIGINT(20),
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            ";

        // Run the SQL
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Add an option to store the version number
        add_option( DSCF_DLM_Utilities::buildDatabaseVersionString(), self::CURRENT_VERSION );
    }

    public static function buildSettingsEditorOptions($object=null){

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Global Framework Settings',
        );
        $options[]=array(
            'title'=>'Plugin Enabled',
            'objectType'=>DSCF_DLM_StandardEditor::OT_BOOLEAN,
            'objectName'=>'plugin_enabled',
            'formType'=>DSCF_DLM_StandardEditor::ET_CHECKBOX,
            'value'=>(isset($object->plugin_enabled))?$object->plugin_enabled:true,
            'help'=>'True to enable the Plugin, False to disable it without uninstalling it.  If False, will prevent the display of all user facing albums, etc...  Use this feature to disable the plugin globally without having to uninstall it.'
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Virtual Path Settings',
        );
        $keyname = self::SETTINGS_SLUG_BASE;
        $options[]=array(
            'title'=>'Base Virtual Path',
            'objectType'=>DSCF_DLM_StandardEditor::OT_STRING,
            'objectName'=>$keyname,
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT,
            'size'=>50,
            'value'=>(isset($object->$keyname))?$object->$keyname:self::SLUG_BASE,
            'help'=>'The virtual path off of '.site_url().' to house the redirect handler.  Default Value: "'.self::SLUG_BASE.'"'
        );
        return $options;

    }

    /*
     * Returns the base full URL that all other URLs for this plugins should be based off of.
     */
    public static function getBaseUrl(){
        $slug = self::getSlugBase();
        $slug = trim($slug, '/');
        $slug = '/'.$slug.'/';
        $url = site_url() . $slug;
        return $url;
    }

    public static function getSlugBase(){
        $slug = DSCF_DLM_StandardSetting::getSetting(self::SETTINGS_SLUG_BASE);
        if(empty($slug)){
            $slug = self::SLUG_BASE;
        }
        return $slug;
    }

    public static function shortcodeHandler($atts){

        //DSCF_DLM_Utilities::logMessage("Inside: ".__CLASS__."::".__FUNCTION__);

        $html = "";
        $sc_id = 0;
        extract( shortcode_atts( array(
            'sc_id' => 0
        ), $atts ) );

        //DSCF_DLM_Utilities::logMessage("SC ID: [$sc_id]");

        switch($sc_id){
            case self::SC_ID_INSERT_LINK:
                // Insert a tracked link into a post
                $html = DSCF_DLM_Links_Link::shortcodeHandlerLinkDisplay($atts);
                break;
        }

        return $html;
    }


}
