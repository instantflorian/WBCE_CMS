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

require_once '../../config.php';

// Include WB admin wrapper script
$admin = new Admin('admintools', 'admintools', false);
require_once dirname(__FILE__) . '/functions.inc.php';
Lang::loadLanguage(__DIR__);

$sBackToList = ADMIN_URL.'/admintools/tool.php?tool=droplets';
// check permission: same tool right admintools/tool.php checks, plus FTAN
if ( !$admin->get_permission('admintools') || !droplets_may_use_tool() || !$admin->checkFTAN() ) {
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $sBackToList );
    $admin->print_footer();
    exit();
}

// Get id — must be an existing droplet (add_droplet.php creates the row)
$droplet_id = isset($_POST['droplet_id']) && is_numeric($_POST['droplet_id']) ? intval($_POST['droplet_id']) : 0;
$aExisting  = $droplet_id > 0
    ? $database->fetchRow("SELECT `admin_edit`, `admin_view` FROM `{TP}mod_droplets` WHERE `id` = ?", [$droplet_id])
    : null;
if ($aExisting === null) {
    header( "Location: " . $sBackToList );
    exit();
}

// admin_edit is enforced here, not only by hiding the save buttons
$bIsAdmin = droplets_is_admin();
if (!$bIsAdmin && (int) $aExisting['admin_edit'] === 1) {
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_SECURITY_ACCESS'], $sBackToList );
    $admin->print_footer();
    exit();
}
// only admins may change the admin flags themselves
$iAdminEdit = $bIsAdmin ? (int) $admin->get_post('admin_edit') : (int) $aExisting['admin_edit'];
$iAdminView = $bIsAdmin ? (int) $admin->get_post('admin_view') : (int) $aExisting['admin_view'];

$sBackURL = ADMIN_URL.'/admintools/tool.php?tool=droplets&do=modify&droplet_id='.$droplet_id;
// Validate all fields
$sName = $admin->get_post('title');
if($sName == '') {
    $admin->print_header();
    $admin->print_error($MESSAGE['GENERIC_FILL_IN_ALL'], $sBackURL);
    $admin->print_footer();
} else {
    $tags = array('<?php', '?'.'>' , '<?');
    $sCode = str_replace($tags, '', $_POST['savecontent']);

    // ── Name + PHP syntax + security check — block save if invalid or unsafe ──
    $bNameOk     = droplets_valid_name($sName);
    $syntaxError = $bNameOk ? CodeVet::checkSyntax($sCode, $syntaxLine) : null;
    $findings    = $bNameOk && $syntaxError === null ? CodeVet::scan($sCode, CodeVetProfile::Droplet) : [];

    if (!$bNameOk || $syntaxError !== null || $findings !== []) {
        if ($findings !== []) {
            CodeVet::logEvent('droplet_save_blocked', CodeVetProfile::Droplet, $findings, ['droplet_id' => $droplet_id]);
        }
        $sMessage = !$bNameOk
                  ? L_('DR_TEXT:INVALID_NAME')
                  : (function_exists('L_') ? L_('DR_TEXT:INVALIDCODE') : 'Invalid PHP code')
                    . ': ' . ($syntaxError ?? $findings[0]->message);

        // Never discard the admin's unsaved edit — stash it for one read by
        // tool.php?do=modify, which overlays it on top of the DB row and
        // clears it immediately after. Redirect straight back (same pattern
        // Code2's save.php uses) instead of rendering a standalone error page
        // the admin then has to click "back" from.
        $_SESSION['codevet_draft']['droplet_' . $droplet_id] = [
            'name'        => $sName,
            'description' => $admin->get_post('description'),
            'active'      => (int) $admin->get_post('active'),
            'admin_edit'  => $iAdminEdit,
            'admin_view'  => $iAdminView,
            'code'        => $sCode,
            'comments'    => $admin->get_post('comments'),
            'line'        => $syntaxError !== null ? $syntaxLine : ($findings[0]->line ?? -1),
        ];
        (new Alerts())->sessionToast($sMessage, 'error');
        header('Location: ' . $sBackURL);
        exit();
    }

    $aUpdate = array(
        'id'            => $droplet_id,
        'name'          => $sName,
        'active'        => (int) $admin->get_post('active'),
        'admin_view'    => $iAdminView,
        'admin_edit'    => $iAdminEdit,
        'show_wysiwyg'  => (int) $admin->get_post('show_wysiwyg'),
        'description'   => $admin->get_post('description'),
        'code'          => $sCode,
        'comments'      => $admin->get_post('comments'),
        'modified_when' => time(),
        'modified_by'   => (int) $admin->get_user_id(),
    );

    $database->upsertRow('{TP}mod_droplets', 'id', $aUpdate);

    if ($database->hasError()) {
        (new Alerts())->sessionToast($database->getError(), 'error');
    } else {
        (new Alerts())->sessionToast($MESSAGE['RECORD_MODIFIED_SAVED'] ?? 'Saved', 'success');
    }

    $sGoto = isset($_POST['save_back']) ? $sBackToList.'&hilite='.rawurlencode($sName) : $sBackURL;
    header('Location: ' . $sGoto);
    exit;
}
