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

$admin = new Admin( 'admintools', 'admintools');
require_once __DIR__ . '/functions.inc.php';
// same tool right admintools/tool.php checks; the "Add Droplet" link in
// list.twig carries the FTAN in its query string
if ( $admin->get_permission( 'admintools' ) == true && droplets_may_use_tool() && $admin->checkFTAN('GET') )
{
    $admintool_link   = ADMIN_URL . '/admintools/index.php';
    $module_edit_link = ADMIN_URL . '/admintools/tool.php?tool=droplets';
    $modified_when    = time();
    $modified_by      = intval( $admin->get_user_id() );

    $database->insertRow('{TP}mod_droplets', [
        'name'          => '',
        'code'          => '',
        'description'   => '',
        'comments'      => '',
        'active'        => 1,
        'modified_when' => $modified_when,
        'modified_by'   => $modified_by,
    ]);

    if($database->hasError())
    {
        $admin->print_error( $database->getError(), $module_edit_link );
    }
    else
    {
        $droplet_id = intval( $database->lastInsertId() );
        $admin->print_success( $TEXT['SUCCESS'], ADMIN_URL.'/admintools/tool.php?tool=droplets&do=modify&droplet_id=' . $droplet_id );
    }
}
else
{
    $admin->print_error( $MESSAGE['GENERIC_SECURITY_ACCESS'], ADMIN_URL . '/admintools/tool.php?tool=droplets' );
}

// Print admin footer
$admin->print_footer();