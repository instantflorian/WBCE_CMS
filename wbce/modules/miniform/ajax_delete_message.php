<?php

// initialize json_respond array  (will be sent back)
$aJsonRespond = array();
$aJsonRespond['message'] = 'Nothing to do!';
$aJsonRespond['success'] = false;

// require config for initialisation.
require '../../config.php';

// test admin access for this
require_once(WB_PATH.'/framework/class.admin.php');
require_once(__DIR__.'/functions.php');
$admin = new admin('Modules', 'modules_view', false, true);

// CSRF: the delete is triggered from the submissions list (messages_loop.php),
// which sends a FTAN token with the request.
if(!$admin->checkFTAN('POST')) {
	$aJsonRespond['message'] = 'Security check failed. Please reload.';
	die(json_encode($aJsonRespond));
}

// check for the iRecordID parameter. Preventing warnings in the errorlogs
if(!isset($_POST['iRecordID']) || is_array($_POST['iRecordID'])) die(json_encode($aJsonRespond));

// get record_id to delete
$iRecordID      = intval($_POST['iRecordID']);

if($iRecordID > 0) {
	// Authorisation: only delete a submission whose section lives on a page the
	// user may edit (this endpoint used to delete ANY message_id for anyone
	// with modules_view).
	$iSectionID = (int) $database->fetchValue(
		"SELECT `section_id` FROM `{TP}mod_miniform_data` WHERE `message_id` = ?", [$iRecordID]
	);
	if(!$iSectionID || !miniform_may_manage_section($admin, $iSectionID)) {
		$aJsonRespond['message'] = 'insufficient rights';
		die(json_encode($aJsonRespond));
	}
	// excecute query
	$database->deleteRow('{TP}mod_miniform_data', 'message_id', $iRecordID);
	// test for errors
	if($database->hasError()) {
		$aJsonRespond['message'] = 'Deleting record failed!';
	} else {
		$aJsonRespond['message'] = 'Record deleted successfully!';
		$aJsonRespond['success'] = true;
	}
}
// return json data
die(json_encode($aJsonRespond));

