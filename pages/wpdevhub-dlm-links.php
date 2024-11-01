<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:28 PM
 * To change this template use File | Settings | File Templates.
 */

// Build the Header
echo DSCF_DLM_Utilities::buildHeader(array(
    'title'=>'Link Manager',
    'icon'=>WPDEVHUB_CONST_DLM_URL_IMAGES.'/link.png',
    'description'=>'Use this manager to create and maintain link forwarders.',
    'buttons'=>array(
        0=>array('text'=>'Create New Link','params'=>array('page'=>DSCF_DLM_Utilities::buildPageSlug(DSCF_DLM_Links_Main::PAGE_LINKS), 'id'=>'new')),
        1=>array('text'=>'View All','params'=>array('page'=>DSCF_DLM_Utilities::buildPageSlug(DSCF_DLM_Links_Main::PAGE_LINKS))),
    )
));

// Check for a delete request
echo DSCF_DLM_Links_Link::checkForDelete(array());

///////////////////////  Editor DISPLAY  ///////////////////////////
echo DSCF_DLM_StandardEditor::buildPageTemplate(DSCF_DLM_Utilities::buildAppClassName('DSCF_DLM_Links_Link'));

// If the ID field was removed or is not present that means we want the Manager
$id = DSCF_DLM_Utilities::getRequestVarIfExists('id');
if(empty($id)){
    ///////////////////////  MANAGER DISPLAY  ///////////////////////////
    $rows = DSCF_DLM_Links_Link::managerBuildOptions(DSCF_DLM_Links_Link::getAll());
    echo DSCF_DLM_StandardManager::buildManagerTable($rows);
}

// Close the wrapper
echo DSCF_DLM_Utilities::buildFooter();
