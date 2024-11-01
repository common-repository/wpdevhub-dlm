<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */


$isEnabled = DSCF_DLM_StandardSetting::getSetting(DSCF_DLM_Links_Main::SETTINGS_PLUGIN_ENABLED);
if(empty($isEnabled)){
    DSCF_DLM_Utilities::logMessage("Dimbal Link Manager : Link Forwarders Disabled - redirecting to Homepage.");
    wp_redirect( site_url() );
    exit;
}


$linkHash = DSCF_DLM_Utilities::getRequestVarIfExists('hash_key');
$link = DSCF_DLM_Links_Link::getByHashKey($linkHash);
if(empty($link)){
    DSCF_DLM_Utilities::logError("Could not find link forwarder with hash key [$linkHash].  Redirecting to homepage");
    wp_redirect( site_url() );
    exit;
}


$useAltUrl = false;
//$link->validateStartEndDates();

//Check whether the link has hit the Max number of Hits
$maxReached = $link->maxHitsReached();
if($maxReached){	//Max Hit Count is Being Used
    DSCF_DLM_Utilities::logMessage("Max Hit for Link IS TRUE");
    $useAltUrl = true;
}

//Check whether the Link is not active.
if($link->status != DSCF_DLM_Links_Link::STATUS_ACTIVE){
    DSCF_DLM_Utilities::logMessage("LINK STATUS is not ACTIVE");
    $useAltUrl = true;	//Code is not active for whatever reason
}

$url = $link->url;
if($useAltUrl && strlen($link->urlAlt)>2){		//If an alt url is not specified then do not do anything
    // Use the Alt Url
    $link->recordHit(DSCF_DLM_Links_Hit::HIT_TYPE_ALTERNATE);
    $url = $link->urlAlt;
}else{
    // Use the Primary URL
    $link->recordHit(DSCF_DLM_Links_Hit::HIT_TYPE_PRIMARY);
    if($link->randomizeDestination){
        $url = $link->getRandomizedDestination();
    }else{
        $url = $link->url;
    }
}

// Just a safety step I guess.
if(empty($url)){
    $url = $link->url;
}

wp_redirect( $url );
exit;
