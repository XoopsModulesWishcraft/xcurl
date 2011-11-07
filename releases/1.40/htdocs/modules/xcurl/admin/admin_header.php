<?php
/**
 * $Id$
 * Module: WF-Downloads
 * Version: v2.0.5a
 * Release Date: 26 july 2004
 * Author: WF-Sections
 * Licence: GNU
 */
 error_reporting(E_ALL);
include '../../../mainfile.php';
include '../../../include/cp_header.php';
include '../include/functions.php';

include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
	
if (is_object($xoopsUser)) {
    $xoopsModule = XoopsModule::getByDirname("xcurl");
    if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
        redirect_header(XOOPS_URL . "/", 3, _NOPERM);
        exit();
    } 
} else {
    redirect_header(XOOPS_URL . "/", 1, _NOPERM);
    exit();
}
$myts = &MyTextSanitizer::getInstance();
error_reporting(E_ALL);
?>