<?php

include_once("admin_header.php");
include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';

$op = '';

foreach ($_POST as $k => $v) {
    ${$k} = $v;
} 

foreach ($_GET as $k => $v) {
    ${$k} = $v;
} 

switch ($op) {
    case "default":
    default:
        global $xoopsDB, $xoopsModule;

        xoops_cp_header();
		adminmenu(5);
        // View Categories permissions
        $item_list_view = array();
        $block_view = array(); 
     
        $result_view = $xoopsDB->query("SELECT plugin_id, plugin_name FROM " . $xoopsDB->prefix("curl_plugins") . " ");
        if ($xoopsDB->getRowsNum($result_view)) {
            while ($myrow_view = $xoopsDB->fetcharray($result_view)) {
                $item_list_view['cid'] = $myrow_view['plugin_id'];
                $item_list_view['title'] = $myrow_view['plugin_name'];
                $form_view = new XoopsGroupPermForm("", $xoopsModule->getVar('mid'), "plugin_call", "<img id='toptableicon' src=" . XOOPS_URL . "/modules/" . $xoopsModule->dirname() . "/images/close12.gif alt='' /></a>" . _XC_PERMISSIONSVIEWMAN . "</h3><div id='toptable'><span style=\"color: #567; margin: 3px 0 0 0; font-size: small; display: block; \">" . _XC_VIEW_FUNCTION . "</span>");
                $block_view[] = $item_list_view;
                foreach ($block_view as $itemlists) {
                    $form_view->addItem($itemlists['cid'], $itemlists['title']);
                } 
            } 
            echo $form_view->render();
        } else {
			echo "<img id='toptableicon' src=" . XOOPS_URL . "/modules/" . $xoopsModule->dirname() . "/images/close12.gif alt='' /></a>&nbsp;" . _XCOAP_PERMISSIONSVIEWMAN . "</h3><div id='toptable'><span style=\"color: #567; margin: 3px 0 0 0; font-size: small; display: block; \">" . _XC_NOPERMSSET . "</span>";

        } 
        echo "</div>";

        echo "<br />\n";
} 
footer_adminMenu();
xoops_cp_footer();

?>