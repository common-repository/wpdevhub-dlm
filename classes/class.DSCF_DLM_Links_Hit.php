<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DSCF_DLM_Links_Hit extends DSCF_DLM_StandardObjectRecord{

    public $linkId=0;             // The key to identify the plugin by
    public $hitType=0;			    // The title of the plugin.
    public $hitDate;
    public $ipAddress;
    public $browser;
    public $platform;
    public $requestMethod;
    public $language;

    const TABLE_NAME = "hits";

    const HIT_TYPE_PRIMARY = 1;
    const HIT_TYPE_ALTERNATE = 2;

    public function __construct($linkId=0, $hitType=0, $overrideHitDate=false, $browser=null, $platform=null){

        global $wpdb;
        $table_name = self::getTableName();

        //Pass it to the parent to setup some common items
        $this->create();

        if($linkId == 0 || $hitType == 0){
            //Exit out - these two must be specified
            DSCF_DLM_Utilities::logError("linkId and hitType are required fields.  Cannot create Hit Tracker");
        }

        //Assign other values...
        $this->setLastModified();
        $this->linkId = $linkId;
        $this->hitType = $hitType;
        if($overrideHitDate){
            $this->hitDate = $overrideHitDate;
        }else{
            $this->hitDate = time();
        }

        // Get the Browser and Platform
        $browserObject = new DSCF_DLM_Browser();
        $this->browser = $browserObject->getBrowser();
        if(!empty($browser)){
            $this->browser = $browser;
        }
        $this->browserId = DSCF_DLM_Browser::getIdForBrowser($this->browser);

        $this->platform = $browserObject->getPlatform();
        if(!empty($platform)){
            $this->platform = $platform;
        }
        $this->platformId = DSCF_DLM_Browser::getIdForPlatform($this->platform);

        $this->ipAddress = $_SERVER['REMOTE_ADDR'];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $this->unsetVarsForSave();

        //Save in the DB
        try{
            $wpdb->insert(
                $table_name,
                array(
                    'blogId' => $this->blogId,
                    'linkId' => $this->linkId,
                    'hitType'=>$this->hitType,
                    'hitDate'=>$this->hitDate,
                    'browserId' => $this->browserId,
                    'platformId' => $this->platformId,
                    'data' => self::pack($this)
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                )
            );
        }catch(Exception $e){
            //DSCF_DLM_Utilities::logMessage('Error creating Poll: '.$e->getMessage());
            return false;
        }

        //Get the ID of the inserted row and save it back to the object
        try{
            $this->id = $wpdb->insert_id;
            $this->save();
        }catch(Exception $e){
            //DSCF_DLM_Utilities::logMessage('Error creating Poll: '.$e->getMessage());
            return false;
        }

        //Return the object
        return $this;
    }

    public function save(){
        global $wpdb;
        $table_name = self::getTableName();
        $this->setLastModified();
        $this->unsetVarsForSave();
        try{
            $wpdb->update(
                $table_name,
                array(
                    'blogId' => $this->blogId,
                    'linkId' => $this->linkId,
                    'hitType'=>$this->hitType,
                    'hitDate'=>$this->hitDate,
                    'browserId' => $this->browserId,
                    'platformId' => $this->platformId,
                    'data' => self::pack($this)
                ),
                array( 'ID' => $this->id ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                ),
                array( '%d' )
            );
        }catch(Exception $e){
            //DSCF_DLM_Utilities::logMessage('Error saving Object: '.$e->getMessage());
            return false;
        }
        return true;
    }

    public function unsetVarsForSave(){
        $vars = array("userId", "requestUri", "country", "region", "city", "userAgent", "name", "queryString", "salt", "status", "startDate", "endDate", "enforceStartEndDates", "hitCount", "createdDate", "lastHitDate", "cache", "cacheAll", "cacheByUser", "tags", "title", "lastModified");
        $this->unsetVars($vars);
    }

    // Controlled by Parent Class
    //public static function getTableName(){}

    // Controlled by Parent Class
    //public static function get($id){}

    // Controlled by Parent Class
    //public static function getAll($start=0,$limit=500){}

    // Controlled by Parent Class
    //public static function deleteById($id){}





}
