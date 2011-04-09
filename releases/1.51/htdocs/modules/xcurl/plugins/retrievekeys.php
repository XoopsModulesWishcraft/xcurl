<?php
function retrievekeys_xsd(){
	$xsd = array();
	$i=0;
	$data = array();
			$data[] = array("name" => "username", "type" => "string");
			$data[] = array("name" => "password", "type" => "string");	
			$data[] = array("name" => "tablename", "type" => "string");
			$data[] = array("name" => "clause", "type" => "string");
			$data[] = array("name" => "fieldid", "type" => "integer");			
			$data[] = array("name" => "tablename", "type" => "string");
			$data[] = array("name" => "id", "type" => "integer");			
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	

	$data = array();
		$data[] = array("name" => "id", "type" => "integer");
	$data_b = array();
		$data_b[] = array("name" => "field", "type" => "string");
		$data_b[] = array("name" => "value", "type" => "string");		
		$data[] = array("items" => array("data" => $data_b, "objname" => "data"));		
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'items';
	
	return $xsd;
}

function retrievekeys_wsdl(){

}

function retrievekeys_wsdl_service(){

}

// Define the method as a PHP function
function retrievekeys($var) {
	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_fields')." WHERE `key` = 1 and visible = 1 ";
	if (strlen($var['tablename'])>0) {
		$sql .= "and tbl_id = ".get_tableid($var['tablename']);		
		$tbl_id = get_tableid($var['tablename']);
	} elseif ($var['id']>0) {
		$sql .= "and tbl_id = ".$var['id'];
		$tbl_id = $var['id'];
	} else {
		return array('ErrNum'=> 2, "ErrDesc" => 'Table Name or Table ID not specified');
	}

	$ret = $xoopsDB->query($sql);
	$sql = "SELECT ";
	$tmp = array();
	while ($row = $xoopsDB->fetchArray($ret)){
		$sql .= '`'.$row['fieldname'].'`';
		$tmp[] = $row['fieldname'];
		$t++;
		if ($t<$xoopsDB->getRowsNum($ret)){
 			$sql .= ', ';
		}
	}
	if (strlen($var['tablename'])>0) {
		$sql .= ' FROM '.$xoopsDB->prefix($var['tablename']);		
	} elseif ($var['id']>0) {
		$sql .= ' FROM '.$xoopsDB->prefix(get_tablename($var['id']));
	}
	if ($var['clause']==1){
		if (strpos(' '.strtolower($var['clause']),'union')>0)
			return array('ErrNum'=> 8, "ErrDesc" => 'Union not accepted');				
		$sql .= ' WHERE `'.get_fieldname($var['fieldid'], $tbl_id).'` '.$var['clause'];
	}

	$ret = $xoopsDB->query($sql);
	$rtn = array();

	while ($row = $xoopsDB->fetchArray($ret)){
		$id++;
		$tmp_b = array();
		foreach ($tmp as $result){
			$tmp_b[] = array("field" => $result, "value" => $row[$result]);
		}
		$rtn[] = array( 'id' => $id,
						'data'=> $tmp_b);
		
	}

	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if (!validateuser($var['username'],$var['password']))
			return false;
	}
	return $rtn;
	
}

?>