<?php
function tableschemer_xsd(){
	$xsd=array();
	$i=0;
	$data = array();
		$data[] = array("name" => "username", "type" => "string");
		$data[] = array("name" => "password", "type" => "string");
		$data[] = array("name" => "update", "type" => "integer");
		$data[] = array("name" => "post", "type" => "integer");
		$data[] = array("name" => "retrieve", "type" => "integer");
		$data[] = array("name" => "tablename", "type" => "string");			
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	
	$data=array();
		$data[] = array("name" => "table_id", "type" => "integer");
		$data[] = array("name" => "field", "type" => "string");
		$data[] = array("name" => "allowpost", "type" => "integer");
		$data[] = array("name" => "allowretrieve", "type" => "integer");
		$data[] = array("name" => "allowupdate", "type" => "integer");
		$data[] = array("name" => "string", "type" => "integer");
		$data[] = array("name" => "int", "type" => "integer");
		$data[] = array("name" => "float", "type" => "integer");
		$data[] = array("name" => "text", "type" => "integer");
		$data[] = array("name" => "other", "type" => "integer");
		$data[] = array("name" => "key", "type" => "integer");
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'items';					
	return $xsd;
}

function tableschemer_wsdl(){

}

function tableschemer_wsdl_service(){

}

// Define the method as a PHP function
function tableschemer($var) {
	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
		if (!checkright(basename(__FILE__),$username,$password)) {
			mark_for_lock(basename(__FILE__),$username,$password);
			return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
		}
	}
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_fields')." WHERE visible = 1 ";
	if ($var['post']=1){
		$sql .= "and allowpost = 1 ";
	} elseif ($var['retrieve']=1) {
		$sql .= "and allowretrieve = 1 ";
	} elseif ($var['update']=1) {
		$sql .= "and allowupdate = 1 ";
	}
	if (strlen($var['tablename'])>0) {
		$sql .= "and tbl_id = ".get_tableid($var['tablename']);		
	} elseif ($var['id']>0) {
		$sql .= "and tbl_id = ".$var['id'];
	} else {
		return array('ErrNum'=> 2, "ErrDesc" => 'Table Name or Table ID not specified');
	}
	
	$ret = $xoopsDB->query($sql);
	$rtn = array();
	while ($row = $xoopsDB->fetchArray($ret)){
		$rtn[] = array( 'table_id' => $row['tbl_id'],
						'field' => $row['fieldname'],
						'allowpost'=> $row['allowpost'],
						'allowretrieve'=> $row['allowretrieve'],
						'allowupdate'=> $row['allowupdate'],
						'string'=> $row['string'],
						'int'=> $row['int'],
						'float'=> $row['float'],
						'text'=> $row['text'],
						'key'=> $row['key'],
						'other'=> $row['other']);
	}

	global $xoopsModuleConfig;
	if ($xoopsModuleConfig['site_user_auth']==1){
		if (!validateuser($var['username'],$var['password']))
			return false;
	}		
	return $rtn;
	

}

?>