<?php
function viewsforretrieve_xsd(){
		$xsd=array();
	$i=0;
	$data = array();
		$data[] = array("name" => "username", "type" => "string");
		$data[] = array("name" => "password", "type" => "string");
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	
	$data=array();
		$data[] = array("name" => "id", "type" => "integer");
		$data[] = array("name" => "view", "type" => "string");
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'items';
	
	return $xsd;	
}

function viewsforretrieve_wsdl(){

}

function viewsforretrieve_wsdl_service(){

}
// Define the method as a PHP function
function viewsforretrieve($var) {
	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." WHERE allowretrieve = 1 and visible = 1 and view = 1";
	$ret = $xoopsDB->query($sql);
	$rtn = array();
	while ($row = $xoopsDB->fetchArray($ret)){
		$t++;
		$rtn[$t] = array( 'id' => $row['tbl_id'],
						'view' => $row['tablename']);
	}

	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if (!validateuser($var['username'],$var['password']))
			return false;
	}
	return $rtn;

}


?>