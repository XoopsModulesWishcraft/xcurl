<?php
function retrieve_xsd(){
	$xsd = array();
	$i=0;
	$data = array();
			$data[] = array("name" => "username", "type" => "string");
			$data[] = array("name" => "password", "type" => "string");	
			$data[] = array("name" => "tablename", "type" => "string");
			$data[] = array("name" => "clause", "type" => "string");
		$datab=array();
		$datab[] = array("name" => "field", "type" => "string");
			$data[] = array("items" => array("data" => $datab, "objname" => "data"));
											 
	$xsd['request'][$i]['items']['data'] = $data;
	$xsd['request'][$i]['items']['objname'] = 'var';	
	
	$xsd['response'][] = array("name" => "total_records", "type" => "double");
	$data = array();
		$data[] = array("name" => "field", "type" => "string");
		$data[] = array("name" => "value", "type" => "string");		
	$i++;
	$xsd['response'][$i]['items']['data'] = $data;
	$xsd['response'][$i]['items']['objname'] = 'data';
	return $xsd;
}

function retrieve_wsdl(){

}

function retrieve_wsdl_service(){

}

// Define the method as a PHP function
function retrieve($var) {
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

	if (!validate($tbl_id,$var['data'], "allowretrieve")){
		return array('ErrNum'=> 4, "ErrDesc" => 'Not all fields are allowed retrieve');
	} else {
		$sql = "SELECT ";
		foreach ($var['data'] as $data){
			if ($data['field']=='*')
				return array('ErrNum'=> 7, "ErrDesc" => 'Wildcard not accepted');				
				
			$sql_b .= "`". $data['field']."`,";
		}
		if (strlen($var['clause'])>0){
			if (strpos(' '.strtolower($var['clause']),'union')>0)
				return array('ErrNum'=> 8, "ErrDesc" => 'Union not accepted');					
			$sql_c .= 'WHERE '.$var['clause'] ."";
		}

		global $xoopsModuleConfig;
		if ($xoopsModuleConfig['site_user_auth']==1){
			if (!validateuser($var['username'],$var['password']))
				return false;
		}
		//echo $sql." ".substr($sql_b,0,strlen($str_b)-1)." FROM ".$xoopsDB->prefix(get_tablename($tbl_id))." ".$sql_c;
		
		$rt = $xoopsDB->queryf($sql." ".substr($sql_b,0,strlen($str_b)-1)." FROM ".$xoopsDB->prefix(get_tablename($tbl_id))." ".$sql_c);

		if (!$xoopsDB->getRowsNum($rt)){
			return array('ErrNum'=> 3, "ErrDesc" => 'No Records Returned from Query');
		} else {
			$rtn = array();
			while($row = $xoopsDB->fetchArray($rt)){
				$rdata = array();
				foreach ($var['data'] as $data){
					$rdata[] = array("fieldname"=> $data['field'], "value"=>$row[$data['field']]);
				}
				$rtn[] = $rdata;
			}
		
		}

		return array("total_records" => $xoopsDB->getRowsNum($rt), "items" => $rtn);
	
	}

}

?>