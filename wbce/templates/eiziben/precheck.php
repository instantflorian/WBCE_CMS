<?php

/**
 *
 * @category		modules
 * @package			oneforall
 * @author			WBCE Community
 * @copyright		2004-2009, Ryan Djurovich
 * @copyright		2009-2010, Website Baker Org. e.V.
 * @copyright		2019-, WBCE Community
 * @link			https://www.wbce.org/
 * @license			http://www.gnu.org/licenses/gpl.html
 * @platform		WBCE
 *
 */


// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: ../../index.php'));

$PRECHECK = array();


/**
 * Specify required WBCE version
 * You need to provide at least the Version number (Default operator:= >=)
 */
$PRECHECK['WBCE_VERSION'] = array('VERSION' => '1.7.0', 'OPERATOR' => '>=');




?>