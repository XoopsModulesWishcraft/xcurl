<?php
function ban_xsd(){
	$xsd = array();
	$i=0;
	$data = array();
			$data[] = array("name" => "username", "type" => "string");
			$data[] = array("name" => "password", "type" => "string");	
		$datab=array();
		$datab[] = array("name" => "category_id", "type" => "integer");
		$datab[] = array("name" => "uid", "type" => "integer");
		$datab[] = array("name" => "uname", "type" => "string");
		$datab[] = array("name" => "ip4", "type" => "string");
		$datab[] = array("name" => "ip6", "type" => "string");
		$datab[] = array("name" => "long", "type" => "string");
		$datab[] = array("name" => "proxy-ip4", "type" => "string");
		$datab[] = array("name" => "proxy-ip6", "type" => "string");
		$datab[] = array("name" => "network-addy", "type" => "string");
		$datab[] = array("name" => "mac-addy", "type" => "string");
		$datab[] = array("name" => "made", "type" => "integer");
			$data[] = array("items" => array("data" => $datab, "objname" => "bans"));
		$datac = array();	
		$datac[] = array("name" => "uid", "type" => "string");
		$datac[] = array("name" => "uname", "type" => "string");											 
		$datac[] = array("name" => "comment", "type" => "string");				
			$data[] = array("items" => array("data" => $datac, "objname" => "comments"));							 
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	
	$xsd['response'][] = array("name" => "ban_made", "type" => "boolean");
	$xsd['response'][] = array("name" => "made", "type" => "integer");
	return $xsd;
}

function ban_wsdl(){

}

function ban_wsdl_service(){

}

// Define the method as a PHP function
function ban($username, $password, $bans, $comments) {
	global $xoopsModuleConfig, $xoopsDB;

	$banmemberHandler =& xoops_getmodulehandler('members', 'ban');
	$comment_handler = & xoops_gethandler('comment');
	$module_handler = & xoops_gethandler('module');	
	
	$xoModule = $module_handler->getByDirname('ban');
	
	foreach ($bans as $key => $ban){
		if (
				$ban['network-addy']!='localhsst'  ||
				$ban['ip4']!='127.0.0.3' &&
			  (
				intval($ban['made'])<time()-(12*60*60)  &&
				intval($ban['made'])>time()+(12*60*60)
			  )
			){
			
			$criteria = new CriteriaCompo();
			foreach($ban as $key => $value)
				if ($key!='is_proxied'&&$key!='made'&&$key!='uid'&&$key!='uname')
					$criteria->add(new Criteria('`'.$key.'`', $value));

			if ($banmemberHandler->getCount($criteria)==0) {
				
				$banning = $banmemberHandler->create();
				foreach($ban as $key => $value)
					if ($key!='is_proxied')
						$banning->setVar($key, $value);
				if ($itemid = $banmemberHandler->insert($banning)) {
					
					$ii++;
					foreach($comments as $cmid => $commentor) {
	
						$sql = "INSERT INTO ".$xoopsDB->prefix('xoopscomments'). ' (com_created, com_pid, com_itemid, com_rootid, com_ip, com_title, com_text,  dohtml, dosmiley, doxcode, doimage, dobr, com_icon, com_modid) VALUES("'.time().'", "0", "'.$itemid.'","0","'.$_SERVER['REMOTE_ADDR'].'","Banning Comment :: '.$commentor['uname'].' :: '.date('d-M-Y H:i:s').'","'.addslashes($commentor['comment']).'",1,1,1,1,1,1,"'.$xoModule->getVar('mid').'")';
						$xoopsDB->queryF($sql);

					}
				}
			}
		}
	}
	return array("ban_made" => intval($ii), "made" => time());

}

?>