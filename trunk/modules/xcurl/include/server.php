<?php

global $xoopsModuleConfig,$xoopsModule;

require_once(XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->dirname().'/class/class.functions.php');
require_once('common.php');
require_once('JSON.php');
	

$funct = new FunctionsHandler();

$FunctionDefine = array();
foreach($funct->GetServerExtensions() as $extension){
	global $xoopsDB;
	$sql = "SELECT count(*) rc FROM ".$xoopsDB->prefix('curl_plugins'). " where active = 1 and plugin_file = '".$extension."'";
	$ret = $xoopsDB->query($sql);
	$row = $xoopsDB->fetchArray($ret);
	if ($row['rc']==1){
		require_once(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/'. $extension);
		$FunctionDefine[] = substr( $extension,0,strlen( $extension)-4);
	}	
}

$FunctionDefine = array_unique($FunctionDefine);

foreach($FunctionDefine as $id => $func)  {
	if (!empty($_REQUEST[$func])) {
		$opfunc = $func;
		$opxsd = $func.'_xsd';	
	}
}

if (!empty($opfunc)) {
	$fields=0;
	foreach($opxsd['request'] as $ii => $request) {
		foreach($request['items']['data'] as $iu => $field)
		{
			if (isset($field['items'])) {
				$tmp[$fields] = $_REQUEST[$opfunc][$field['items']['objname']]		
				$fields++;				
			} elseif (!empty($field['name'])&&!empty($field['type'])) {
				switch($field['type']) {
				default:
				case "string":
					$tmp[$fields] = (string)$_REQUEST[$opfunc][$field['name']]
					break;
				case "integer":
					$tmp[$fields] = (integer)$_REQUEST[$opfunc][$field['name']]
					break;
				}
				$fields++;				
			}
		}
	}
	$fields--;	
	
	switch($fields) {
	case 0:
		$result = $opfunc();
		break;
	case 1:
		$result = $opfunc($tmp[0]);
		break;
	case 2:
		$result = $opfunc($tmp[0], $tmp[1]);
		break;
	case 3:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2]);
		break;
	case 4:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3]);
		break;
	case 5:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4]);
		break;
	case 6:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5]);
		break;
	case 7:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);
		break;
	case 8:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7]);
		break;
	case 9:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
		break;
	case 10:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9]);
		break;
	case 11:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10]);
		break;
	case 12:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11]);
		break;		
	case 13:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12]);
		break;		
	case 14:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13]);
		break;		
	case 15:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14]);
		break;		
	case 16:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15]);
		break;		
	case 17:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16]);
		break;		
	case 18:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17]);
		break;		
	case 19:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17], $tmp[18]);
		break;		
	case 20:
		$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17], $tmp[18], $tmp[19]);
		break;		
	}
	
	$json = new Services_JSON();
	echo $json->encode($result);
	exit(0);
}
?>