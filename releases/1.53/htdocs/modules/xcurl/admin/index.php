<?php

include 'admin_header.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
include_once '../include/functions.php';

error_reporting(E_ALL);
global $xoopsDB;

  if (isset($_GET)) {
    foreach ($_GET as $k => $v) {
      ${$k} = $v;
    }
  }

  if (isset($_POST)) {
    foreach ($_POST as $k => $v) {
      ${$k} = $v;
    }
  }
  
switch ($op){

case "fields":
	
	if (!$tbl_id)
		$tbl_id=1;
			
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." where view = '0'";
	$ret = $xoopsDB->queryF($sql);
	
	$form_sel = new XoopsThemeForm(_XC_SELECTTABLE, "seltable", $_SERVER['PHP_SELF'] ."");
	$form_sel->setExtra( "enctype='multipart/form-data'" ) ;
	
	$table_sel = new XoopsFormSelect(_XC_SELECTTABLE.':', 'select');
	$table_sel->setExtra('onchange="window.location=\'\'+this.options[this.selectedIndex].value"');
	
    while($row = $xoopsDB->fetchArray($ret)) { 
		$table_sel->addOption("index.php?op=fields&tbl_id=".$row['tbl_id'], $row['tablename']);
		if ($tbl_id == $row['tbl_id'])
			$table_sel->setValue("index.php?op=fields&tbl_id=".$row['tbl_id']);
	}
	$form_sel->addElement($table_sel);
	
	$sql = "SHOW FIELDS FROM ".$xoopsDB->prefix(get_tablename($tbl_id));
	$ret = $xoopsDB->queryF($sql);

	$form_fld = new XoopsThemeForm(_XC_FIELDOPTIONSFOR.' '.get_tablename($tbl_id), "fields", $_SERVER['PHP_SELF'] ."");
	$form_fld->setExtra( "enctype='multipart/form-data'" ) ;
	
	$field=0;
	$tbldat = get_tableconfig(get_tablename($tbl_id));
	
	$ele_tray = array();
		
	while(list($fieldname, $type, $null, $keytype, $tmp, $tmp) = $xoopsDB->fetchRow($ret)){
	 	$field++;
		
		$int = 0;
		$string = 0;
		$float = 0;
		$text = 0;
		$other = 0;		
		$key = 0;
		if (strpos(' '.$type,'int')>0){
			$int = 1;
		} elseif (strpos(' '.$type,'char')>0){
			$string = 1;
		} elseif (strpos(' '.$type,'float')>0){
			$float = 1;
		} elseif (strpos(' '.$type,'text')>0){
			$text = 1;
		} else {
			$other = 1;
		}
		
		if ($keytype == "PRI"){
			$key = 1;
		}
		$tbldat = get_fieldconfig($fieldname, $tbl_id);

		if (!isset($tbldat)){
			$new++;
			$ele_tray[$field] = new XoopsFormElementTray($fieldname.' (new)','&nbsp;',$fieldname);
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", "new"));
			$ele_tray[$field]->addElement(new XoopsFormHidden("key[$field]", $key));
			$ele_tray[$field]->addElement(new XoopsFormHidden("string[$field]", $string));
			$ele_tray[$field]->addElement(new XoopsFormHidden("int[$field]", $int));
			$ele_tray[$field]->addElement(new XoopsFormHidden("float[$field]", $float));			
			$ele_tray[$field]->addElement(new XoopsFormHidden("text[$field]", $text));
			$ele_tray[$field]->addElement(new XoopsFormHidden("other[$field]", $other));
			$ele_tray[$field]->addElement(new XoopsFormHidden("fieldname[$field]", $fieldname));
			
			$post[$field] = new XoopsFormCheckBox("Post", "post[$field]");
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]");
			$update[$field] = new XoopsFormCheckBox("Update", "update[$field]");
			$visible[$field] = new XoopsFormCheckBox("Visible", "visible[$field]");
			$crc[$field] = new XoopsFormCheckBox("CRC", "crc[$field]");												

			$post[$field]->addOption(1, '&nbsp;');
			$retrieve[$field]->addOption(1, '&nbsp;');
			$update[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');
			$crc[$field]->addOption(1, '&nbsp;');

			if ($key==1) 
				$post[$field]->setExtra('disabled="disabled"');
			elseif ($tbldat['allowpost']==1)
				$post[$field]->setValue(1);			
			$ele_tray[$field]->addElement($post[$field]);
													
			if ($tbldat['allowretrieve']==1)
				$retrieve[$field]->setValue(1);
			$ele_tray[$field]->addElement($retrieve[$field]);
			
			if ($key==1) 
				$update[$field]->setExtra('disabled="disabled"');
			elseif ($tbldat['allowupdate']==1)
				$update[$field]->setValue(1);			
			$ele_tray[$field]->addElement($update[$field]);

			if ($tbldat['visible']==1)
				$visible[$field]->setValue(1);
			$ele_tray[$field]->addElement($visible[$field]);

			if ($key==1) 
				$crc[$field]->setExtra('disabled="disabled"');
			elseif ($tbldat['crc']==1)
				$crc[$field]->setValue(1);			
			$ele_tray[$field]->addElement($crc[$field]);
			
		} else { 

			$ele_tray[$field] = new XoopsFormElementTray($fieldname.'','&nbsp;',$fieldname);
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", $tbldat['fld_id']));
			$ele_tray[$field]->addElement(new XoopsFormHidden("key[$field]", $key));
			$ele_tray[$field]->addElement(new XoopsFormHidden("string[$field]", $string));
			$ele_tray[$field]->addElement(new XoopsFormHidden("int[$field]", $int));
			$ele_tray[$field]->addElement(new XoopsFormHidden("float[$field]", $float));			
			$ele_tray[$field]->addElement(new XoopsFormHidden("text[$field]", $text));
			$ele_tray[$field]->addElement(new XoopsFormHidden("other[$field]", $other));
			$ele_tray[$field]->addElement(new XoopsFormHidden("fieldname[$field]", $fieldname));
			
			$post[$field] = new XoopsFormCheckBox("Post", "post[$field]", $tbldat['allowpost']);
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]", $tbldat['allowretrieve']);
			$update[$field] = new XoopsFormCheckBox("Update", "update[$field]", $tbldat['allowupdate']);
			$visible[$field] = new XoopsFormCheckBox("Visible", "visible[$field]", $tbldat['visible']);
			$crc[$field] = new XoopsFormCheckBox("CRC", "crc[$field]", $tbldat['crc']);												

			$post[$field]->addOption(1, '&nbsp;');
			$retrieve[$field]->addOption(1, '&nbsp;');
			$update[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');
			$crc[$field]->addOption(1, '&nbsp;');

			if ($key==1) 
				$post[$field]->setExtra('disabled="disabled"');
			$ele_tray[$field]->addElement($post[$field]);
													
			$ele_tray[$field]->addElement($retrieve[$field]);
			
			if ($key==1) 
				$update[$field]->setExtra('disabled="disabled"');
			$ele_tray[$field]->addElement($update[$field]);

			$ele_tray[$field]->addElement($visible[$field]);

			if ($key==1) 
				$crc[$field]->setExtra('disabled="disabled"');

			$ele_tray[$field]->addElement($crc[$field]);
			 
 		} 
		
		$form_fld->addElement(	$ele_tray[$field] );	
	} 
 
 	$form_fld->addElement(new XoopsFormHidden("tbl_id", $tbl_id));
 	$form_fld->addElement(new XoopsFormHidden("op", "savefields"));
 	$form_fld->addElement(new XoopsFormHidden("new", $new));	 
	$form_fld->addElement(new XoopsFormButton('', 'send', _SEND, 'submit'));
	xoops_cp_header();
	adminMenu(2);
	$form_sel->display();
	echo "<div style='clear:both;'></div>";
	$form_fld->display();
	footer_adminMenu();
	xoops_cp_footer();
	break;
	
case "savefields":

	foreach ($id as $f){
		$tt++;	
		switch ($f){
		case "new":
			$sql = "INSERT INTO ".$xoopsDB->prefix('curl_fields')." (tbl_id, fieldname, allowpost, allowretrieve, allowupdate, visible, `key`, `string`, `int`, `float`, `text`, `other`, `crc`) VALUES ('$tbl_id','".addslashes($fieldname[$tt])."','".intval($post[$tt])."','".intval($retrieve[$tt])."','".intval($update[$tt])."','".intval($visible[$tt])."','".intval($key[$tt])."','".intval($string[$tt])."','".intval($int[$tt])."','".intval($float[$tt])."','".intval($text[$tt])."','".intval($other[$tt])."','".intval($crc[$tt])."')";
			$ty=$xoopsDB->queryF($sql);
			break;
		default:
			$sql = "UPDATE ".$xoopsDB->prefix('curl_fields')." SET allowpost ='".intval($post[$tt])."', allowupdate ='".intval($update[$tt])."',allowretrieve = '".intval($retrieve[$tt])."', visible='".intval($visible[$tt])."',`key` ='".intval($key[$tt])."', `string` = '".intval($string[$tt])."', `int`='".intval($int[$tt])."',`float` ='".intval($float[$tt])."', `text` = '".intval($text[$tt])."', `other`='".intval($other[$tt])."', crc = '".intval($crc[$tt])."' WHERE fld_id = ".$id[$tt]. " and tbl_id = ".$tbl_id;
			$ty=$xoopsDB->queryF($sql);
		}
	
	}
	redirect_header("index.php?op=fields&tbl_id=".$tbl_id,2,_XC_DATABASEUPDATED);
	break;
	
case "savetables":


	foreach ($id as $f){
		$tt++;	
		switch ($f){
		case "new":
			$sql = "INSERT INTO ".$xoopsDB->prefix('curl_tables')." (tablename, allowpost, allowretrieve, allowupdate, visible, view) VALUES ('".addslashes($tablename[$tt])."','".intval($post[$tt])."','".intval($retrieve[$tt])."','".intval($update[$tt])."','".intval($visible[$tt])."','0')";
			$ty=$xoopsDB->queryF($sql);
			break;
		default:
			$sql = "UPDATE ".$xoopsDB->prefix('curl_tables')." SET allowpost ='".intval($post[$tt])."', allowretrieve = '".intval($retrieve[$tt])."', allowupdate = '".intval($update[$tt])."', visible='".intval($visible[$tt])."' WHERE tbl_id = ".$id[$tt];
			$ty=$xoopsDB->queryF($sql);
		}
	
	}
	redirect_header("index.php?op=tables",2,_XC_DATABASEUPDATED);
	break;

case "saveviews":

	foreach ($id as $f){
		$tt++;	
		switch ($f){
		case "new":
			$sql = "INSERT INTO ".$xoopsDB->prefix('curl_tables')." (tablename, allowpost, allowretrieve, allowupdate, visible, view) VALUES ('".addslashes($tablename[$tt])."','".intval($post[$tt])."','".intval($retrieve[$tt])."','".intval($update[$tt])."','".intval($visible[$tt])."','1')";
			$ty=$xoopsDB->queryF($sql);
			break;
		default:
			$sql = "UPDATE ".$xoopsDB->prefix('curl_tables')." SET allowpost ='".intval($post[$tt])."', allowretrieve = '".intval($retrieve[$tt])."', allowupdate = '".intval($update[$tt])."', visible='".intval($visible[$tt])."' WHERE tbl_id = ".$id[$tt];
			$ty=$xoopsDB->queryF($sql);
		}
	
	}
	redirect_header("index.php?op=views",2,_XC_DATABASEUPDATED);
	break;
	
case "views":

	$sql = "SHOW VIEWS FROM ".XOOPS_DB_NAME."";
	$ret = $xoopsDB->queryF($sql);
	
	$ele_tray = array();
	$form_view = new XoopsThemeForm(_XC_VIEWSFOR.' '.XOOPS_DB_NAME, "views", $_SERVER['PHP_SELF'] ."");
	$form_view->setExtra( "enctype='multipart/form-data'" ) ;
			
	$field=0;
	while(list($table) = $xoopsDB->fetchRow($ret)){
		$field++;
		$tbldat = get_tableconfig($table);		
		if (!isset($tbldat)){

			$new++;
			$ele_tray[$field] = new XoopsFormElementTray($table.' (new)','&nbsp;',$table);
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", "new"));
			$ele_tray[$field]->addElement(new XoopsFormHidden("viewname[$field]", $table));
			
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]");
			$visible[$field] = new XoopsFormCheckBox("Visible", "post[$field]");

			$retrieve[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');

		} else { 

			$ele_tray[$field] = new XoopsFormElementTray(strip_prefix($table).'','&nbsp;',strip_prefix($table));
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", $tbldat['tbl_id']));
			$ele_tray[$field]->addElement(new XoopsFormHidden("viewname[$field]", strip_prefix($table)));
			
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]");
			$visible[$field] = new XoopsFormCheckBox("Visible", "post[$field]");

			$retrieve[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');

			if ($tbldat['visible']==1)
				$visible[$field]->setValue(1);			
			$ele_tray[$field]->addElement($visible[$field]);
													
			if ($tbldat['allowretrieve']==1)
				$retrieve[$field]->setValue(1);
			$ele_tray[$field]->addElement($retrieve[$field]);
  	
 		} 

		$form_view->addElement(	$ele_tray[$field] );	 
	 } 
	 
 	$form_view->addElement(new XoopsFormHidden("op", "saveviews"));
 	$form_view->addElement(new XoopsFormHidden("new", $new));	 
	$form_view->addElement(new XoopsFormButton('', 'send', _SEND, 'submit'));

	xoops_cp_header();
	adminMenu(3);
	$form_view->display();
	footer_adminMenu();
	xoops_cp_footer();
	break;	
	
case "saveplugins":
	

	foreach ($id as $f){
		$tt++;	
		switch ($f){
		case "new":
			$sql = "INSERT INTO ".$xoopsDB->prefix('curl_plugins')." (plugin_name, plugin_file, active) VALUES ('".addslashes($functionname[$tt])."','".addslashes($filename[$tt])."','".intval($active[$tt])."')";
			$ty=$xoopsDB->queryF($sql);
			break;
		default:
			$sql = "UPDATE ".$xoopsDB->prefix('curl_plugins')." SET active ='".intval($active[$tt])."' WHERE plugin_id = ".$id[$tt];
			$ty=$xoopsDB->queryF($sql);
		}
	
	}
	redirect_header("index.php?op=plugins",2, _XC_SAVEDSUCCESSFUL);
	break;

case "plugins":
	error_reporting(E_ALL);
	global $xoopsModuleConfig;
	require_once('../class/class.functions.php');
	$funct = new FunctionsHandler($xoopsModuleConfig['wsdl']);
	
	$FunctionDefine = array();
	foreach($funct->GetServerExtensions() as $extension){
		$phpcode= file_get_contents(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/'.$extension);
		ob_start();
		$r=eval("?>".$phpcode."<?php");
		$result = ob_get_contents();
		ob_end_clean();
		if (strpos(' '.$result,"Parse")==0){
			$FunctionDefine[] = $extension;
		}
		
	}
	
	$ele_tray = array();
	$form_plugin = new XoopsThemeForm(_XC_PLUGINAVAILABLE, "plugins", $_SERVER['PHP_SELF'] ."");
	$form_plugin->setExtra( "enctype='multipart/form-data'" ) ;
			
	$field=0;
	foreach($FunctionDefine as $func) { 

		$field++;

		$functdata = get_functionconfig($func);
		if (!isset($functdata)){
			$new++;
			
			$ele_tray[$field] = new XoopsFormElementTray($func.' (new)','&nbsp;',$func);
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", "new"));
			$ele_tray[$field]->addElement(new XoopsFormHidden("functionname[$field]", str_replace('.php','',$func)));
			$ele_tray[$field]->addElement(new XoopsFormHidden("filename[$field]", $func));
						
			$active[$field] = new XoopsFormCheckBox("Active", "active[$field]");
			$active[$field]->addOption(1, '&nbsp;');
			$ele_tray[$field]->addElement($active[$field]);

		} else { 

			$ele_tray[$field] = new XoopsFormElementTray($func.'','&nbsp;',$func);
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", $functdata['plugin_id']));
			$ele_tray[$field]->addElement(new XoopsFormHidden("functionname[$field]", str_replace('.php','',$func)));
			$ele_tray[$field]->addElement(new XoopsFormHidden("filename[$field]", $func));
						
			$active[$field] = new XoopsFormCheckBox("Active", "active[$field]");
			
			$active[$field]->addOption(1, '&nbsp;');

			if ($functdata['active']==1)
				$active[$field]->setValue(1);
			$ele_tray[$field]->addElement($active[$field]);

   		} 
		$form_plugin->addElement(	$ele_tray[$field] );	 
	 } 
	 
 	$form_plugin->addElement(new XoopsFormHidden("op", "saveplugins"));
 	$form_plugin->addElement(new XoopsFormHidden("new", $new));	 
	$form_plugin->addElement(new XoopsFormButton('', 'send', _SEND, 'submit'));

	xoops_cp_header();
	adminMenu(4);
	$form_plugin->display();
	footer_adminMenu();
	xoops_cp_footer();
	break;	

default:
	
	$sql = "SHOW TABLES FROM ".XOOPS_DB_NAME." LIKE '".XOOPS_DB_PREFIX."\_%'";
	$ret = $xoopsDB->queryF($sql);
	
	$ele_tray = array();
	$form_tables = new XoopsThemeForm(_XC_TABLESAVAILABLE.' '.XOOPS_DB_NAME, "tables", $_SERVER['PHP_SELF'] ."");
	$form_tables->setExtra( "enctype='multipart/form-data'" ) ;
	
	$field=0;
	while(list($table) = $xoopsDB->fetchRow($ret)){
		$field++;
		$tbldat = get_tableconfig($table);

		if (!isset($tbldat)){
			$new++;
			
			$ele_tray[$field] = new XoopsFormElementTray(strip_prefix($table).' (new)','&nbsp;',strip_prefix($table));
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", 'new'));
			$ele_tray[$field]->addElement(new XoopsFormHidden("tablename[$field]", strip_prefix($table)));
			
			$post[$field] = new XoopsFormCheckBox("Post", "post[$field]", 0);
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]", 0);
			$update[$field] = new XoopsFormCheckBox("Update", "update[$field]", 0);
			$visible[$field] = new XoopsFormCheckBox("Visible", "visible[$field]", 0);

			$post[$field]->addOption(1, '&nbsp;');
			$retrieve[$field]->addOption(1, '&nbsp;');
			$update[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');

			$ele_tray[$field]->addElement($post[$field]);
			$ele_tray[$field]->addElement($retrieve[$field]);
			$ele_tray[$field]->addElement($update[$field]);
			$ele_tray[$field]->addElement($visible[$field]);

		} else {
		
			$ele_tray[$field] = new XoopsFormElementTray(strip_prefix($table).'','&nbsp;',strip_prefix($table));
			$ele_tray[$field]->addElement(new XoopsFormHidden("id[$field]", $tbldat['tbl_id']));
			$ele_tray[$field]->addElement(new XoopsFormHidden("tablename[$field]", strip_prefix($table)));

			$post[$field] = new XoopsFormCheckBox("Post", "post[$field]", $tbldat['allowpost']);
			$retrieve[$field] = new XoopsFormCheckBox("Retrieve", "retrieve[$field]", $tbldat['allowretrieve']);
			$update[$field] = new XoopsFormCheckBox("Update", "update[$field]", $tbldat['allowupdate']);
			$visible[$field] = new XoopsFormCheckBox("Visible", "visible[$field]", $tbldat['visible']);

			$post[$field]->addOption(1, '&nbsp;');
			$retrieve[$field]->addOption(1, '&nbsp;');
			$update[$field]->addOption(1, '&nbsp;');
			$visible[$field]->addOption(1, '&nbsp;');

			$ele_tray[$field]->addElement($post[$field]);
			$ele_tray[$field]->addElement($retrieve[$field]);
			$ele_tray[$field]->addElement($update[$field]);
			$ele_tray[$field]->addElement($visible[$field]);

		} 
		$form_tables->addElement(	$ele_tray[$field] );	 
	 } 
	 
 	$form_tables->addElement(new XoopsFormHidden("op", "savetables"));
 	$form_tables->addElement(new XoopsFormHidden("new", $new));	 
	$form_tables->addElement(new XoopsFormButton('', 'send', _SEND, 'submit'));

	xoops_cp_header();
	adminMenu(1);
	$form_tables->display();
	footer_adminMenu();
	xoops_cp_footer();
	break;	
	
}

function strip_prefix($raw_tablename){
	return str_replace(XOOPS_DB_PREFIX."_",'',$raw_tablename);
}

function get_tableconfig($raw_tablename){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." WHERE tablename = '".strip_prefix($raw_tablename)."'";
	$ret = $xoopsDB->query($sql);
	if ($xoopsDB->getRowsNum($ret)){
		return $xoopsDB->fetchArray($ret);
	} else {

	}
}
function get_functionconfig($plugin_filename){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_plugins')." WHERE plugin_file = '".addslashes($plugin_filename)."'";
	$ret = $xoopsDB->query($sql);
	if ($xoopsDB->getRowsNum($ret)){
		return $xoopsDB->fetchArray($ret);
	} else {

	}
}

function get_fieldconfig($raw_fieldname, $tbl_id){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_fields')." WHERE fieldname = '$raw_fieldname' and tbl_id = $tbl_id";
	$ret = $xoopsDB->query($sql);
	if ($xoopsDB->getRowsNum($ret)){
		return $xoopsDB->fetchArray($ret);
	} else {

	}
}
function get_tableid($tablename){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." WHERE tablename = '$tablename'";
	$ret = $xoopsDB->query($sql);
	$row = $xoopsDB->fetchArray($ret);
	return $row['tbl_id'];
}

function get_tablename($tableid){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_tables')." WHERE tbl_id = '$tableid'";
	$ret = $xoopsDB->query($sql);
	$row = $xoopsDB->fetchArray($ret);
	return $row['tablename'];
}

function compile_wsdl(){
	return true;
}


?>