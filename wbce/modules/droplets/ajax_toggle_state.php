<?php
isset($_POST['action']) or header( 'Location: ../../index.php' );

// json_respond array wich is sent back to the backend
// ('success' starts false: every early exit below is a failure, and the
// overview's JS flips the row's active/inactive class on success == true)
$aRspnd = array();
$aRspnd['message'] = 'failed to change status';
$aRspnd['success'] = false;
#exit(json_encode($aRspnd));

if (!isset($_POST['idkey'])){
    trigger_error(1);
     exit(json_encode($aRspnd));
}
$iFilterID = $_POST['idkey'];

if(is_readable($sConfigFile = '../../config.php')) {
    $aRspnd['message'] = 'L: '.__LINE__;
    require_once($sConfigFile);
} else {
    $aRspnd['message'] = 'L: '.__LINE__;
    exit(json_encode($aRspnd));
}

// check if user has permissions to access the droplets admin tool
// (get_permission('droplets', 'module') used to be checked here, which is
// always true: the tool right is the allow entry 'droplets_tool')
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new Admin('admintools', 'admintools', false, false);
if (!$admin->is_authenticated()) {
    $aRspnd['message'] = 'insuficcient rights';
    exit(json_encode($aRspnd));
}
require_once __DIR__ . '/functions.inc.php';
if (!$admin->get_permission('admintools') || !droplets_may_use_tool()) {
    $aRspnd['message'] = 'insuficcient rights';
    exit(json_encode($aRspnd));
}

$purpose = $_POST['purpose'];
if ($purpose == "toggle_status") {
    $iId = $admin->checkIDKEY($iFilterID, 0, 'POST', true);
    if($iId == 0){
        $aRspnd['message'] = 'ID '.$iId.' can\'t work';
        $aRspnd['success'] = false;
        exit(json_encode($aRspnd));
    }
    $iActive = (int) $_POST['action'];

    // admin_edit droplets can only be switched on/off by admins
    $iAdminEdit = $database->fetchValue(
        "SELECT `admin_edit` FROM `{TP}mod_droplets` WHERE `id` = ?", [(int) $iId]
    );
    if ($iAdminEdit === '' || ((int) $iAdminEdit === 1 && !droplets_is_admin())) {
        $aRspnd['message'] = 'insuficcient rights';
        exit(json_encode($aRspnd));
    }

    $database->upsertRow('{TP}mod_droplets', 'id', [
        'id' => $iId,
        'active' => $iActive,
    ]);
    if($database->hasError()) {
        $aRspnd['success'] = false;
        $aRspnd['message'] = 'db query failed: '.$database->getError();
        $aRspnd['message'] = 'id: '.$iId.' active: '.$iActive;
        $aRspnd['icon'] = 'cancel.gif';
        exit(json_encode($aRspnd));
    } else {
        $aRspnd['message'] = ('id: #'.$iId.' status: '.$iActive);
        $aRspnd['message'] .= '. Status was changed properly.';
        $aRspnd['success'] = true;
        exit(json_encode($aRspnd));
    }


}else{
    $aRspnd['message'] = 'wrong arguments "$action"';
    exit(json_encode($aRspnd));
}
