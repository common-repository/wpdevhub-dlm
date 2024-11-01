<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DSCF_DLM_Links_Link extends DSCF_DLM_StandardObjectRecord{

    public $title='';               // A title for the link
    public $hashKey='';             // A hask key to identify the link by
    public $url='';			        // The url that shoudl be forwarded to
    public $urlAlt='';			    // An alternate url to forward to when expired or reached max settings
    public $maxHits;                // max number of hits to support
    public $hitCountPrimary=0;
    public $hitCountAlternate=0;
    public $lastHitDate=0;
    public $lastHitCounterUpdate=0;
    public $additionalDestinations=array();
    public $randomizeDestination=false;

    const TABLE_NAME = "links";

    const COUNTER_UPDATE_INTERVAL = 3600;	//Every Hour

    public function __construct(){

        global $wpdb;
        $table_name = self::getTableName();

        //Pass it to the parent to setup some common items
        $this->create();

        $this->validateHashKey();

        //Save in the DB
        try{
            $wpdb->insert(
                $table_name,
                array(
                    'blogId' => $this->blogId,
                    'hashKey' => $this->hashKey,
                    'data' => self::pack($this)
                ),
                array(
                    '%d',
                    '%s',
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
        try{
            $wpdb->update(
                $table_name,
                array(
                    'hashKey'=>$this->hashKey,
                    'data' => self::pack($this)
                ),
                array( 'ID' => $this->id ),
                array(
                    '%s',
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

    // Controlled by Parent Class
    //public static function getTableName(){}

    // Controlled by Parent Class
    //public static function get($id){}

    // Controlled by Parent Class
    //public static function getAll($start=0,$limit=500){}

    // Controlled by Parent Class
    //public static function deleteById($id){}

    public static function editorBuildOptions($object){

        $options=array();
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Basic Link Properties',
        );
        $options[]=array(
            'title'=>'ID',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'id',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_READONLY,
            'value'=>($object)?$object->id:'',
            'help'=>''
        );
        $options[]=array(
            'title'=>'Title',
            'objectType'=>DSCF_DLM_StandardEditor::OT_STRING,
            'objectName'=>'title',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT,
            'value'=>($object)?$object->title:'',
            'help'=>'The title for this link.  Used when referencing the link via shortcode.',
            'size'=>50,
        );
        $options[]=array(
            'title'=>'Destination URL',
            'objectType'=>DSCF_DLM_StandardEditor::OT_STRING,
            'objectName'=>'url',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT,
            'value'=>($object)?$object->url:'',
            'help'=>'The destination URL the forwarder should take users to.',
            'size'=>50,
        );
        $options[]=array(
            'title'=>'Status',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'status',
            'formType'=>DSCF_DLM_StandardEditor::ET_MENU_STATUS,
            'formOptions'=>DSCF_DLM_StandardObjectRecord::getAllStatusMarks(),
            'value'=>($object)?$object->status:DSCF_DLM_StandardObjectRecord::STATUS_ACTIVE,
            'help'=>'Select whether this Plugin is active or inactive'
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Additional Destinations',
        );
        $options[]=array(
            'title'=>'Randomize Additional Destinations',
            'objectType'=>DSCF_DLM_StandardEditor::OT_BOOLEAN,
            'objectName'=>'randomizeDestination',
            'formType'=>DSCF_DLM_StandardEditor::ET_CHECKBOX,
            'value'=>($object)?$object->randomizeDestination:false,
            'help'=>'If checked, the final destination for active and valid redirects will be randomly selected from the primary defined URL above and any additional urls specified in the list below.',
        );
        $options[]=array(
            'title'=>'Additional Destinations',
            'objectType'=>DSCF_DLM_StandardEditor::OT_ARRAY,
            'objectName'=>'additionalDestinations',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_ADDITIONAL,
            'value'=>($object)?$object->additionalDestinations:array(),
            'help'=>'Additional Urls that the link controller should randomly select from, when choosing the final forwarding destination.  Uncheck a Url to remove it from the list.',
            'size'=>75,
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Alternate URL Settings and Rules',
        );
        $options[]=array(
            'title'=>'Alternate Url',
            'objectType'=>DSCF_DLM_StandardEditor::OT_STRING,
            'objectName'=>'urlAlt',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT,
            'value'=>($object)?$object->urlAlt:'',
            'help'=>'An alternate destination URL to send users to for this link when it is either inactive, expired, hit the maximum number of hits, or otherwise closed.  If none is specified then the user will be forwarded to the primary url on file regardless of status.',
            'size'=>75,
        );
        /*
        $options[]=array(
            'title'=>'Enforce Start and End Dates',
            'objectType'=>DSCF_DLM_StandardEditor::OT_BOOLEAN,
            'objectName'=>'enforceStartEndDates',
            'formType'=>DSCF_DLM_StandardEditor::ET_CHECKBOX,
            'value'=>($object)?$object->enforceStartEndDates:'',
            'help'=>'Check to make the primary Link active only within the Start and End Dates specified below.  When it is not active the Alternate URL will be used.  This setting is only useful if the Alternate URL above is specified.'
        );
        $options[]=array(
            'title'=>'Start Date',
            'objectType'=>DSCF_DLM_StandardEditor::OT_DATE,
            'objectName'=>'startDate',
            'formType'=>DSCF_DLM_StandardEditor::ET_DATE,
            'value'=>($object)?$object->startDate:'',
            'help'=>'You can optionally keep the primary Link closed until a designated Start Date.  You must check the Enforce option above to turn this on.'
        );
        $options[]=array(
            'title'=>'End Date',
            'objectType'=>DSCF_DLM_StandardEditor::OT_DATE,
            'objectName'=>'endDate',
            'formType'=>DSCF_DLM_StandardEditor::ET_DATE,
            'value'=>($object)?$object->endDate:'',
            'help'=>'You can optionally close the primary Link after a designated End Date.   You must check the Enforce option above to turn this on.'
        );
        */
        $options[]=array(
            'title'=>'Maximum Hits',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'maxHits',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT,
            'value'=>($object)?$object->maxHits:0,
            'help'=>'Close the Link once the maximum number of hits have been recorded.  Use 0 or leave blank for unlimited.  This setting is only useful if the Alternate URL above is specified.',
            'size'=>10,
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Detailed Link Statistics',
        );
        $options[]=array(
            'title'=>'Total Hits',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'hitCount',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_READONLY,
            'value'=>($object)?number_format($object->getTotalHitCount()):'',
            'help'=>'The number of hits the Link has received.  NOTE: The broken down totals are provided below for reference.  The totals below are aggregated several times daily and will not always equal the total hit count.'
        );
        $options[]=array(
            'title'=>'Last Hit Date',
            'objectType'=>DSCF_DLM_StandardEditor::OT_DATE,
            'objectName'=>'lastHitDate',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_READONLY,
            'value'=>($object)?DSCF_DLM_StandardObjectRecord::formatDate($object->lastHitDate,"M j, Y, g:i a"):'',
            'help'=>'The date the Link was last accessed.'
        );
        $options[]=array(
            'title'=>'Total Primary Hits',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'hitCountMinified',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_READONLY,
            'value'=>($object)?number_format($object->hitCountPrimary):'',
            'help'=>'The number of hits the Link has received through the primary URL.'
        );
        $options[]=array(
            'title'=>'Total Alternate Hits',
            'objectType'=>DSCF_DLM_StandardEditor::OT_NUMERIC,
            'objectName'=>'hitCountMinifiedMissed',
            'formType'=>DSCF_DLM_StandardEditor::ET_TEXT_READONLY,
            'value'=>($object)?number_format($object->hitCountAlternate):'',
            'help'=>'The number of hits the Link has received through the Alternate URL.  This occurs when the Link is marked as not Active, has hit its max hit count or is not within the specified start and end date range.'
        );





        return $options;
    }

    public static function managerBuildOptions($objects){

        $rows = array();
        foreach($objects as $object){

            $row = array();
            $row[] = array(
                'title'=>'ID',
                'content'=>$object->id,
            );
            $row[] = array(
                'title'=>'Status',
                'content'=>$object->getFormattedStatusImage($object->status, '24px'),
            );
            $row[] = array(
                'title'=>'Edit',
                'url'=>DSCF_DLM_Utilities::getPageUrl(DSCF_DLM_Links_Main::PAGE_LINKS, array('id'=>$object->id)),
                'image'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/page_edit.png',
                'image_tooltip'=>'Edit Link',
            );
            $row[] = array(
                'title'=>'Title',
                'content'=>$object->title,
            );
            $row[] = array(
                'title'=>'Primary Url',
                'content'=>$object->url,
            );
            $row[] = array(
                'title'=>'Minified Url',
                'content'=>'<input type="text" value="'.$object->getUrl().'" />',
            );
            $shortcodeValue = $object->getInsertLinkShortcode();
            //DSCF_DLM_Utilities::logMessage("Shortcode value: ". $shortcodeValue);
            $row[] = array(
                'title'=>'Shortcode',
                'content'=>'<input type="text" value=\''.$shortcodeValue.'\' />',
            );
            $row[] = array(
                'title'=>'Hit Count',
                'content'=>number_format($object->getTotalHitCount()),
            );
            $row[] = array(
                'title'=>'Created Date',
                'content'=>DSCF_DLM_StandardObjectRecord::formatSiteDate($object->createdDate),
            );
            $row[] = array(
                'title'=>'Delete',
                'url'=>DSCF_DLM_Utilities::getPageUrl(DSCF_DLM_Links_Main::PAGE_LINKS, array('delete'=>1,'id'=>$object->id)),
                'image'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/delete.png',
                'image_tooltip'=>'Delete Link',
            );
            $rows[]=$row;
        }
        return $rows;
    }

    public function getTotalHitCount(){
        return $this->hitCountPrimary + $this->hitCountAlternate;
    }

    public function getUrl(){
        return DSCF_DLM_Links_Main::getBaseUrl().$this->hashKey;
    }

    /*
     * Builds insertion code for a given tracked link.
     *      Will use a title override first if one is present
     *      Will use the saved title next if one is present
     *      Will use the destination URL last
     */
    public function buildLinkInsertionCode($linkTitle=''){
        if(empty($linkTitle)){
            $linkTitle = $this->title;
        }
        if(empty($linkTitle)){
            $linkTitle = $this->getUrl();
        }
        $html = '<a href="'.$this->getUrl().'" title="'.$linkTitle.'">'.$linkTitle.'</a>';
        return $html;
    }

    public function getInsertLinkShortcode(){
        return self::buildShortcodeHelper(array('sc_id'=>DSCF_DLM_Links_Main::SC_ID_INSERT_LINK, 'link_id'=>$this->id));
    }

    /*
    * This short code handler will display all of the available plugins
    */
    public static function shortcodeHandlerLinkDisplay($atts){

        $html = '';
        $link = null;
        $link_id = 0;
        $link_title = '';
        extract( shortcode_atts( array(
            'link_id' => 0,
            'link_title' => ''
        ), $atts ) );


        if(!empty($link_id)){
            $link = self::get($link_id);
        }

        if(!empty($link)){
            $html .= $link->buildLinkInsertionCode($link_title);
        }

        return $html;

    }

    public function maxHitsReached(){
        //DSCF_DLM_Utilities::logMessage("LINK OBJECT: ".print_r($this,true));
        if($this->maxHits==0 || trim($this->maxHits) == ''){
            return false;
        }
        if($this->hitCountPrimary >= $this->maxHits){
            return true;
        }
        return false;
    }

    public function getRandomizedDestination(){
        $urls = $this->additionalDestinations;
        $urls[] = $this->url;
        return $urls[array_rand($urls)];
    }

    public function recordHit($hitType, $overrideHitDate=false, $browser=null, $platform=null){
        $this->increaseHitCount();
        $hit = false;
        switch($hitType){
            case DSCF_DLM_Links_Hit::HIT_TYPE_PRIMARY:
                $this->hitCountPrimary++;
                $hit = new DSCF_DLM_Links_Hit($this->id, DSCF_DLM_Links_Hit::HIT_TYPE_PRIMARY, $overrideHitDate, $browser, $platform);
                break;
            case DSCF_DLM_Links_Hit::HIT_TYPE_ALTERNATE:
                $this->hitCountAlternate++;
                $hit = new DSCF_DLM_Links_Hit($this->id, DSCF_DLM_Links_Hit::HIT_TYPE_ALTERNATE, $overrideHitDate, $browser, $platform);
                break;
        }
        $this->save();
        return $hit;
    }

    public function updateAllHitCounts($force=false){
        //Check to see if it has been updated in the last 24 hours... if not then update it
        $now = time();
        $checkpoint = $now - self::COUNTER_UPDATE_INTERVAL;
        if($force || $this->lastHitCounterUpdate < $checkpoint){
            //It is ready for an update.  I should also update the "BY DAY" counts as well for the last 3 days

            //Impresions
            $minifiedGood = DSCF_DLM_Links_Hit::getCount(array('linkId'=>$this->id, 'hitType'=>DSCF_DLM_Links_Hit::HIT_TYPE_PRIMARY));
            $this->hitCountPrimary = $minifiedGood;

            //Impresions
            $minifiedBad = DSCF_DLM_Links_Hit::getCount(array('linkId'=>$this->id, 'hitType'=>DSCF_DLM_Links_Hit::HIT_TYPE_ALTERNATE));
            $this->hitCountAlternate = $minifiedBad;

            //Update the time stamp
            $this->lastHitCounterUpdate = $now;

            $this->save();
        }
    }

}
