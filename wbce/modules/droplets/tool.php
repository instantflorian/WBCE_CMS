<?php
/**
 * WBCE CMS
 * Way Better Content Editing.
 * Visit https://wbce.org to learn more and to join the community.
 *
 * @copyright Ryan Djurovich (2004-2009)
 * @copyright WebsiteBaker Org. e.V. (2009-2015)
 * @copyright WBCE Project (2015-)
 * @license GNU GPL2 (or any later version)
 */

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(!defined('WB_PATH')) {

	require_once(dirname(dirname(dirname(__FILE__))).'/framework/globalExceptionHandler.php');
	throw new IllegalFileException();
}
/* -------------------------------------------------------- */
// CodeMirror CSS
$aCssFiles = [
    // make use of theme_fallbacks CSS files 
    // if the THEME does not deliver them yet
    get_url_from_path($admin->correct_theme_source('../css/ACPI_backend.css')),
    get_url_from_path($admin->correct_theme_source('../css/ACPI_content.css')),
    get_url_from_path($admin->correct_theme_source('../css/ACPI_buttons.css'))
];
I::insertCssFile($aCssFiles, 'HEAD TOP+');

global $module_version;
require_once dirname(__FILE__).'/functions.inc.php';

// FTAN as a query-string part ("name=value") for the action links in the
// templates (delete, copy, restore, backup, ...), checked below
$oTwig->addGlobal('FTAN_GET', $admin->getFTAN(false));

// removes empty entries from the table; this may happen if someone adds a
// droplet but does not save any changes
if (!$admin->get_get("do")) {
    $database->query("DELETE FROM `{TP}mod_droplets` WHERE name=''");
}

$oMsgBox = new MessageBox();
$aToTwig = array();


// if there are exports, there will be an additional button, so check it out
$backup_files = wbce_find_backups( DROPLETS_BACKUP_DIR );
$backup_mgmt  = NULL;
if ( count( $backup_files ) > 0 ) {
    $aToTwig['backup_mgmt'] = true;
}

// convert some keys into actions
foreach(array_values(array('export','upload','delete')) as $key)
{
    if(isset($_REQUEST[$key]))
    {
        $_GET['do'] = $key;
        break;
    }
}

// ----- duplicate -----
if ( isset($_GET['copy']) )
{
    $id = $_GET['copy'];
    if ( is_numeric($id) ) {
        $_GET['do'] = 'copy';
    }
} elseif (isset($_GET['recover'])){
    // ----- recover from backup -----
    // only a zip that is actually listed in the backup directory
    // (DROPLETS_BACKUP_DIR, where the backups are written), never a path
    $sRecoverFile = droplets_backup_path($_GET['recover']);
    if ($sRecoverFile !== null){
        $_GET['do'] = 'recover';
    }
}

$aToTwig['TABS'] = renderTabs('overview');
$sToolURI = ADMIN_URL.'/admintools/tool.php?tool='.ADMIN_TOOL_DIR;

// ----- CSRF: every action that changes something needs a valid FTAN -----
// (the upload and manage_backups tabs only change something when a file is
// posted / a backup is to be deleted; otherwise they just show a page)
$sDo = isset($_GET['do']) ? (string) $_GET['do'] : '';
if (in_array($sDo, array('show_date', 'export', 'delete', 'recover', 'copy', 'backup_droplets'), true)
    && !droplets_check_ftan()) {
    $oMsgBox->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
    unset($_GET['do']);
} elseif ($sDo === 'upload' && !empty($_FILES['userfile']['name']) && !droplets_check_ftan()) {
    $oMsgBox->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
    $_FILES = array();
} elseif ($sDo === 'manage_backups' && isset($_GET['del']) && !droplets_check_ftan()) {
    $oMsgBox->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
    unset($_GET['del']);
}
// action
if(isset($_GET['do']))
{
    switch($_GET['do'])
    {
        // ----- change show_date -----
        case 'show_date':
            debug_dump($_GET);
            $value = ($_GET['val'] == '0') ? true : false;
            debug_dump($value);
            Settings::Set('droplets_show_by_date', (bool) $value);
            $oMsgBox->redirect($sToolURI.'#show_date');
            break;
            
        // ----- export -----
        case 'export':
            if (!empty($_REQUEST['markeddroplet'])){
                $list = $_REQUEST['markeddroplet'];
                if(is_array($list) AND count($list)) {
                    $aToTwig['info'] = wbce_export_droplets($list);
                }
            } 
            break;
        // ----- import -----
        case 'upload':
            $aToTwig['content'] = wbce_handle_upload();
            $aToTwig['more_header_links'] = $DR_TEXT['UPLOAD'];
            $aToTwig['TABS'] = renderTabs('upload');
           # $oMsgBox->redirect($sToolURI.'&hilite='.implode(',',$result['imported']));
            
            break;

        // ----- delete -----
        case 'delete':
           if(isset($_GET['droplet_id']) && ! isset($_POST['markeddroplet']))
           {
               $_POST['markeddroplet'] = array($_GET['droplet_id']);
           }
           wbce_delete_droplets();
           break;

        

        // ----- recover -----
         case 'recover':
            $result = importDropletFromZip(
                    $sRecoverFile,
                    droplets_temp_dir('restore')
                );
                // show errors
                if ( isset( $result['errors'] ) && is_array( $result['errors'] )
                        && count( $result['errors'] ) > 0 ) {
                    $sErrors = '';
                    foreach ( $result['errors'] as $droplet => $error ) {
                        $sErrors .= 'Droplet: '.htmlspecialchars($droplet).'<br />'
                                .  '<b>- '.htmlspecialchars($error).'</b><br />';
                    }
                    $oMsgBox->error($sErrors);
                } else {
                    $oMsgBox->success($result['count']. " ".$DR_TEXT['IMPORTED']);
                    $oMsgBox->redirect($sToolURI.'&hilite='.implode(',',$result['imported']));
                }
                $aToTwig['content'] = 'nottin at all';
                $aToTwig['more_header_links'] = $DR_TEXT['IMPORTED'];
            break;
        
        // ----- show_help -----
         case 'copy':        
            $iID = wbce_copy_droplet($id);
            if(is_numeric($iID)){
                $oMsgBox->success('<b>'.$DR_TEXT['DUPLICATE'].'</b><br>'.strtolower($TEXT['SUCCESS']).'!');
                $oMsgBox->redirect($sToolURI.'&do=modify&droplet_id='.$iID);
            } else {
                $oMsgBox->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
            }
            break;
            
        // ----- modify droplet -----
        case 'modify':

            $modified_when     = time();
            $modified_by       = $admin->get_user_id();
            $sOverviewDroplets = $TEXT['LIST_OPTIONS'].' '.$DR_TEXT['DROPLETS'];
            $droplet_id        = intval($_GET['droplet_id']);

            // Get header and footer
            $data = $database->fetchRow(
                "SELECT * FROM `{TP}mod_droplets` WHERE `id` = ?", [$droplet_id]
            );

            // admin_view / admin_edit are enforced here and in the save
            // endpoints, not only by hiding things in modify.twig
            $bIsAdmin = droplets_is_admin();
            if ($data === null || ((int) $data['admin_view'] === 1 && !$bIsAdmin)) {
                $oMsgBox->error($MESSAGE['GENERIC_SECURITY_ACCESS']);
                break;
            }
            $bCanEdit = $bIsAdmin || (int) $data['admin_edit'] !== 1;

            // A blocked save (CodeVet or syntax error) stashes the unsaved
            // edit here instead of discarding it — one-shot read, cleared
            // immediately so it never leaks into a later, unrelated visit.
            $errorLine  = 0;
            $draftKey   = 'droplet_' . $droplet_id;
            if (isset($_SESSION['codevet_draft'][$draftKey])) {
                $draft = $_SESSION['codevet_draft'][$draftKey];
                unset($_SESSION['codevet_draft'][$draftKey]);
                $data = array_merge($data, [
                    'name'        => $draft['name'],
                    'description' => $draft['description'],
                    'active'      => $draft['active'],
                    'admin_edit'  => $bIsAdmin ? $draft['admin_edit'] : $data['admin_edit'],
                    'admin_view'  => $bIsAdmin ? $draft['admin_view'] : $data['admin_view'],
                    'code'        => $draft['code'],
                    'comments'    => $draft['comments'],
                ]);
                // Message itself already arrives as a session toast (set by
                // save_droplet.php before the redirect that landed us here).
                $errorLine = max(0, (int) $draft['line']);
            }

            $aToTwig['content'] = wbce_twig_display(
                [
                    'data'       => $data,
                    'idKey'      => $admin->getIDKEY($droplet_id),
                    'ajax_url'   => WB_URL . '/modules/droplets/ajax_save_droplet.php',
                    'error_line' => $errorLine,
                    'is_admin'   => $bIsAdmin,
                    'can_edit'   => $bCanEdit,
                ],
                'modify',
                true
            );
            break;

        // ----- create full backup -----
        case 'backup_droplets':
            // a backup reveals the code: admin_view droplets for admins only
            $list = $database->fetchAll(
                "SELECT * FROM `{TP}mod_droplets`" . (droplets_is_admin() ? "" : " WHERE `admin_view` <> 1")
            );
            // backup
            $aToTwig['content'] = wbce_backup_droplets($list);
            $aToTwig['more_header_links'] = $DR_TEXT['BACKUP'];
            $aToTwig['TABS'] = renderTabs('backup_droplets');
            $oMsgBox->success($TEXT['SUCCESS']);
            $oMsgBox->redirect($sToolURI.'&do=manage_backups');
            break;

        // ----- manage backups -----
        case 'manage_backups':
            require_once( WB_PATH . '/include/pclzip/pclzip.lib.php' );
            $backup_files = wbce_find_backups( DROPLETS_BACKUP_DIR );
            // file to delete?
            if ( isset( $_GET['del'] ) ) {
                if ( $_GET['del'] !== 'all' ) {
                    // only a zip that is actually listed in the backup directory, never a path
                    $sFile = droplets_backup_path($_GET['del']);
                    if ( $sFile !== null ) {
                        if(unlink($sFile)){
                            $oMsgBox->success('<b>'.htmlspecialchars(basename($sFile)).'</b><br>'.$MESSAGE['MEDIA_DELETED_FILE']);
                        }
                    }
                } else {
                    $sFiles = '';
                    foreach ( $backup_files as $file ) {                        
                        if ( file_exists( $sFile = DROPLETS_BACKUP_DIR.$file)) {
                            if(unlink($sFile)){
                                $sFiles .= '<br>&nbsp;'.$file;
                            }
                        }
                    }
                    $oMsgBox->success('<b>'.$MESSAGE['MEDIA_DELETED_FILE'].'</b>:'.$sFiles);
                }
                
            }
            $backup_files = wbce_find_backups( DROPLETS_BACKUP_DIR );
            $data = array();
            if ( count( $backup_files ) > 0 )
            {
                // sort by name
                sort($backup_files);
                foreach( $backup_files as $i => $file )
                {
                    // stat
                    $stat  = stat(DROPLETS_BACKUP_DIR.$file);
                    // get zip contents
                    $zip   = new PclZip(DROPLETS_BACKUP_DIR.$file);
                    // get file count in zip
                    $count = $zip->listContent();

                    $data[] = array(
                        'stat'  => $stat,
                        'count' => count($count),
                        'name'  => $file,
                        'list'  => implode( ", ", array_map( function($cnt) { return $cnt["filename"]; }, $count )),
                    );
                }
            }
            $aToTwig['content'] = wbce_twig_display(array('backup_files'=>$data),'backups',true);
            $aToTwig['more_header_links'] = $DR_TEXT['MANAGE_BACKUPS'];            
            $aToTwig['TABS'] = renderTabs('manage_backups');
            break;
             
            // ----- show_help -----
            case 'show_help':        
                $aToTwig['TABS'] = renderTabs('show_help');
                ob_start();
                $sReadmeFile = 'readme_EN.php';
                if(file_exists(__DIR__.'/readme/readme_'.LANGUAGE.'.php')){
                     $sReadmeFile = 'readme_'.LANGUAGE.'.php';
                }
                include __DIR__.'/readme/'.$sReadmeFile;
                $sModUrl = get_url_from_path(__DIR__);
                $sRetVal = ob_get_clean();
                $sRetVal = str_replace('../img', $sModUrl.'/img', $sRetVal);
                $aToTwig['content'] = wbce_twig_display(array('content'=>$sRetVal),'help',true);
            break;
    }
}

$bShowDate = Settings::Get('droplets_show_by_date', TRUE) == 1 ? true: false;
$aToTwig['show_date'] = $bShowDate;
$aToTwig['hilite'] = isset($_GET['hilite']) ? explode(',', $_GET['hilite']) : [];
if(! isset($aToTwig['content'])) {
    $aToTwig['droplets'] = wbce_list_droplets($bShowDate);
}
$aToTwig['MESSAGE_BOX'] = $oMsgBox->fetchDisplay();

// print result
wbce_twig_display($aToTwig);
