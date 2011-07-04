<?php
function post_xsd(){
	$xsd = array();
	$i=0;
	$data_a=array();
		$data_a[$i] = array("name" => "username", "type" => "string");
		$data_a[$i++] = array("name" => "password", "type" => "string");
		$data_a[$i++] = array("name" => "tablename", "type" => "string");
	$data = array();
		$data[] = array("name" => "field", "type" => "string");
		$data[] = array("name" => "value", "type" => "string");
	$i++;
		$data_a[$i]['items']['data'] = $data;
		$data_a[$i]['items']['objname'] = 'data';
	$i=0;
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';
	$xsd['response'][] = array("name" => "insert_id", "type" => "double");
	
	return $xsd;
}

function post_wsdl(){

}

function post_wsdl_service(){

}
// Define the method as a PHP function
function post($var) {
	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}
	global $xoopsDB;
	if (strlen($var['tablename'])>0) {
		$tbl_id = get_tableid($var['tablename']);
	} elseif ($var['id']>0) {
		$tbl_id = $var['id'];
	} else {
		return array('ErrNum'=> 2, "ErrDesc" => 'Table Name or Table ID not specified');
	}

	if (!validate($tbl_id,$var['data'], "allowpost")){
		return array('ErrNum'=> 1, "ErrDesc" => 'Not all fields are allowed posting');
	} else {
		$sql = "INSERT INTO ".$xoopsDB->prefix(get_tablename($tbl_id));
		foreach ($var['data'] as $data){
			$sql_b .= "`". $data['field']."`,";
			$sql_c .= "'". addslashes($data['value'])."',";
		}
		global $xoopsModuleConfig;
		if ($xoopsModuleConfig['site_user_auth']==1){
			if (!validateuser($var['username'],$var['password']))
				return false;
		}
//		echo $sql." (".substr($sql_b,0,strlen($str_b)-1).") VALUES (".substr($sql_c,0,strlen($str_c)-1).")";
		$rt = $xoopsDB->queryF($sql." (".substr($sql_b,0,strlen($str_b)-1).") VALUES (".substr($sql_c,0,strlen($str_c)-1).")");
		return array("insert_id" => $xoopsDB->getInsertId($rt));
	}

}
?>