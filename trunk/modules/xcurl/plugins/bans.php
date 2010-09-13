<?php
function bans_xsd(){
	$xsd = array();
	$i=0;
	$data = array();
			$data[] = array("name" => "username", "type" => "string");
			$data[] = array("name" => "password", "type" => "string");	
			$data[] = array("name" => "records", "type" => "integer");	
											 
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	$i=0;
	$xsd['response'][$i] = array("name" => "bans", "type" => "integer");
	$i++;
	$xsd['response'][$i] = array("name" => "made", "type" => "integer");
	$datab=array();
	$datab[] = array("name" => "ip4", "type" => "string");
	$datab[] = array("name" => "ip6", "type" => "string");
	$datab[] = array("name" => "long", "type" => "string");
	$datab[] = array("name" => "proxy-ip4", "type" => "string");
	$datab[] = array("name" => "proxy-ip6", "type" => "string");
	$datab[] = array("name" => "network-addy", "type" => "string");
	$datab[] = array("name" => "mac-addy", "type" => "string");
		$data[] = array("items" => array("data" => $datab, "objname" => "data"));
	$i++;
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'data';
	return $xsd;
}

function bans_wsdl(){

}

function bans_wsdl_service(){

}

// Define the method as a PHP function
function bans($username, $password, $records) {
	global $xoopsModuleConfig, $xoopsDB;

	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}


	$records = ($records!=0)?intval($records):60*60*0.65;

	$sql = "SELECT * FROM ".$xoopsDB->prefix('ban_member'). ' order by `made` DESC limit '.intval($records);
	$result = $xoopsDB->query($sql);
	$ret = array();
	while($ban = $xoopsDB->fetchArray($result) ){
		$id++;
		foreach(array('ip4','ip6','proxy-ip4','proxy-ip6','network-addy','mac-addy','long') as $field)
			$ret[$id][$field] = $ban[$field];
		
	}
	return array("bans" => count($ret), "made" => time(), "data" => $ret);

}

?>