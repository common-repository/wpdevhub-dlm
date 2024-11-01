<?php
/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 9/26/17
 * Time: 4:38 PM
 * To change this template use File | Settings | File Templates.
 */


echo DSCF_DLM_Utilities::buildHeader(array(
    'title'=>'',
    'icon'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/logo_300.png',
    'description'=>'Manage custom link redirects within your website.',
));


$links = DSCF_DLM_Links_Link::getAll();
$linkArray = DSCF_DLM_Links_Link::getBasicArrayFromObjects($links);

$linkHtml = '';
$linkHtml .= '<form>';
$linkHtml .= '<input type="hidden" name="rt" value="1">';
$linkHtml .= '<select name="link_id">';
foreach($linkArray as $linkId=>$linkTitle){
    $linkHtml .= '<option value="'.$linkId.'">'.$linkTitle.'</option>';
}
$linkHtml .= '</select></form>';

$boxes[] = new DSCF_DLM_Box(array(
    'type'=>DSCF_DLM_Box::TYPE_TRIM,
    'size'=>DSCF_DLM_Box::SIZE_ONE_THIRD,
    'title'=>'Link Reports',
    'content'=>'Choose a unique link to view detailed reports.',
    'icon'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/document_layout.png',
));


////////////////////////

$rt = DSCF_DLM_Utilities::getRequestVarIfExists("rt");

switch($rt){
    case 1:
        // Link based report
        $linkId = DSCF_DLM_Utilities::getRequestVarIfExists("link_id");
        $link = DSCF_DLM_Links_Link::get($linkId);

        if(!empty($link)){

            $link->updateAllHitCounts(true);


        }


        break;
}




// Close the wrapper
echo DSCF_DLM_Utilities::buildFooter();







/*
 *
 *
 *
 * //////////////////////////////// DAILY CLICK COUNTS

// Get the data needed for the various reports
$dailyClickCounts = DSCF_DLM_Banners_Hit::getHitCount(array('hitType'=>DSCF_DLM_Banners_Hit::HIT_TYPE_CLICK, 'groupDaily'=>true, 'startDate'=>$startDate, 'endDate'=>$endDate));
$dailyClickCounts = DSCF_DLM_Banners_Hit::equalizeDailyHitCountArray($dailyClickCounts, $startDate, $endDate);

// Format the data for the chart
$chartDataArray=array();
$chartDataArray[] = array('Date', 'Count');
foreach($dailyClickCounts as $date=>$count){
    $chartDataArray[] = array($date, intval($count));
}

// Build the chart object
$dailyClickChart = new DSCF_DLM_Chart();
$dailyClickChart->data = $chartDataArray;
$dailyClickChart->elemId = "DSCF_DLM_daily_click_chart";
$dailyClickChart->type = DSCF_DLM_Chart::TYPE_LINE;
$dailyClickChart->options = array('title'=>'Daily Banner Clicks', 'hAxis'=>array('slantedText'=>true));
 *
 *
 *
 *
 */
