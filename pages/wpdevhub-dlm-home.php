<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */

echo DSCF_DLM_Utilities::buildHeader(array(
    'title'=>'',
    'icon'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/logo_300.png',
    'description'=>'Manage custom link redirects within your website.',
));


$boxes[] = new DSCF_DLM_Box(array(
    'type'=>DSCF_DLM_Box::TYPE_TRIM,
    'size'=>DSCF_DLM_Box::SIZE_ONE_THIRD,
    'title'=>'Settings',
    'content'=>'View settings to control how your plugin works.',
    'icon'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/document_layout.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>DSCF_DLM_Utilities::buildPageSlug(DSCF_DLM_Links_Main::PAGE_SETTINGS)),'text'=>'Settings')
    )
));


// Render the boxes
echo DSCF_DLM_Box::renderBoxes($boxes);


// Close the wrapper
echo DSCF_DLM_Utilities::buildFooter();
?>
