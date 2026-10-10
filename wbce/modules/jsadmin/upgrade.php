<?php
/**
 *
 * @category        modules
 * @package         jsadmin
 * @author          WebsiteBaker Project
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link            http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: upgrade.php 1537 2011-12-10 11:04:33Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/tags/2.8.3/wb/modules/jsadmin/upgrade.php $
 * @lastmodified    $Date: 2011-12-10 12:04:33 +0100 (Sa, 10. Dez 2011) $
 *
 */
// Must include code to stop this file being access directly
/* -------------------------------------------------------- */
if(defined('WB_PATH') == false)
{
	// Stop this file being access directly
		die('<head><title>Access denied</title></head><body><h2 style="color:red;margin:3em auto;text-align:center;">Cannot access this file directly</h2></body></html>');
}
/* -------------------------------------------------------- */

$msg = '';
$sTable = TABLE_PREFIX.'mod_jsadmin';
if(($sOldType = $database->getTableEngine($sTable))) {
	if(('myisam' != strtolower($sOldType))) {
		$database->query('ALTER TABLE `'.$sTable.'` Engine = \'MyISAM\' ');
		if ($database->hasError()) {
			$msg = $database->getError();
		}
	}
} else {
	$msg = $database->getError();
}
// ------------------------------------

// 2.0.2: move_to.php is no longer used (drag & drop posts to admin/pages/ajax/ since 2.0.0)
// and was a GET write endpoint without FTAN or page/section ownership check. The addon
// installer copies over the existing directory, so remove the stale file explicitly.
$sLegacyFile = __DIR__ . '/move_to.php';
if (is_file($sLegacyFile) && !@unlink($sLegacyFile)) {
	$msg .= ($msg !== '' ? '<br>' : '') . 'jsadmin: could not remove obsolete file modules/jsadmin/move_to.php, please delete it manually.';
}
