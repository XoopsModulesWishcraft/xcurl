<?php
function tablesforupdate_xsd(){
		$xsd=array();
	$i=0;
	$data = array();
		$data[] = array("name" => "username", "type" => "string");
		$data[] = array("name" => "password", "type" => "string");
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	
	$data=array();
		$data[] = array("name" => "id", "type" => "integer");
		$data[] = array("name" => "table", "type" => "string");
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'items';
	
	return $xsd;	

}

function tablesforupdate_wsdl(){

}

function tablesforupdate_wsdl_service(){

}

function tablesforupdate($var) {
	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." WHERE allowupdate = 1 and visible = 1";
	$ret = $xoopsDB->query($sql);
	$rtn = array();
	while ($row = $xoopsDB->fetchArray($ret)){
		$t++;
		$rtn[$t] = array( 'id' => $row['tbl_id'],
						'table' => $row['tablename']);
	}

	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if (!validateuser($var['username'],$var['password']))
			return false;
	}		
	return $rtn;

}
?>