<?php
function validateuser($username, $password){
	global $xoopsDB;
	$sql = "select * from ".$xoopsDB->prefix('users'). " WHERE uname = '$username' and pass = ".(strlen($password)==32&&strtolower($password)==$password?"'$password'":"md5('$password')");
	$ret = $xoopsDB->query($sql);
	if (!$xoopsDB->getRowsNum($ret)) {
		return false;
	} else {
		return true;
	}
}

function user_uid($username, $password){
	global $xoopsDB;
	$sql = "select uid from ".$xoopsDB->prefix('users'). " WHERE uname = '$username' and pass = ".(strlen($password)==32&&strtolower($password)==$password?"'$password'":"md5('$password')");
	$ret = $xoopsDB->query($sql);
	if (!$xoopsDB->getRowsNum($ret)) {
		return false;
	} else {
		$row = $xoopsDB->fetchArray($ret);
		return $row['uid'];
	}
}

function validate($tbl_id, $data, $function){
	global $xoopsDB;
	$sql = "select * from ".$xoopsDB->prefix('curl_tables'). " WHERE tablename = '".get_tablename($tbl_id)."' and $function = 1";
	$ret = $xoopsDB->query($sql);
	$pass=true;
	if (!$xoopsDB->getRowsNum($ret)) {
		$pass=false;	
	} else {
		foreach($data as $row){
			$sql = "select * from ".$xoopsDB->prefix('curl_fields'). " WHERE tbl_id = '$tbl_id' and $function = 1 and fieldname = '".$row['field']."'";
			$ret = $xoopsDB->query($sql);
			if (!$xoopsDB->getRowsNum($ret)&&!is_fieldkey($row['field'],$tbl_id)) {
				$pass=false;
			}
		}
	}
	
	return $pass;
}

function checkright($function_file, $username, $password){
	$uid = user_uid($username,$password);
	$module_handler = xoops_gethandler('module');
	$xoModule = $module_handler->getByDirname('xcurl');
	if ($uid <> 0){
		global $xoopsDB, $xoopsModule;
		$rUser = new XoopsUser($uid);
		$gperm_handler =& xoops_gethandler('groupperm');
		$groups = is_object($rUser) ? $rUser->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
		$sql = "select plugin_id from ".$xoopsDB->prefix('curl_plugins')." where plugin_file = '".addslashes($function_file)."'";
		$ret = $xoopsDB->queryF($sql);
		$row = $xoopsDB->fetchArray($ret);
		$item_id = $row['plugin_id'];
		$modid = $xoModule->getVar('mid');
		$online_handler =& xoops_gethandler('online');
		$online_handler->write($uid, $username, time(), $modid, (string)$_SERVER["REMOTE_ADDR"]);
		$member_handler =& xoops_gethandler('member');
		@ini_set( 'session.gc_maxlifetime', $xoopsConfig['session_expire'] * 60 );
		session_set_save_handler( array( &$sess_handler, 'open' ), array( &$sess_handler, 'close' ), array( &$sess_handler, 'read' ), array( &$sess_handler, 'write' ), array( &$sess_handler, 'destroy' ), array( &$sess_handler, 'gc' ) );
		session_start();
		$_SESSION['xoopsUserId'] = $uid;
		$GLOBALS['xoopsUser'] = &$member_handler->getUser( $uid );
		$_SESSION['xoopsUserGroups'] = $GLOBALS['xoopsUser']->getGroups();
		$GLOBALS['sess_handler']->update_cookie();
				
		return $gperm_handler->checkRight('plugin_call',$item_id,$groups, $modid);
	} else {
		global $xoopsDB, $xoopsModule;
		$gperm_handler =& xoops_gethandler('groupperm');
		$groups = array(XOOPS_GROUP_ANONYMOUS);
		$sql = "select plugin_id from ".$xoopsDB->prefix('curl_plugins')." where plugin_file = '".addslashes($function_file)."'";
		$ret = $xoopsDB->queryF($sql);
		$row = $xoopsDB->fetchArray($ret);
		$item_id = $row['plugin_id'];
		$modid = $xoModule->getVar('mid');
		return $gperm_handler->checkRight('plugin_call',$item_id,$groups, $modid);
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

function get_fieldname($fld_id, $tbl_id){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_fields')." WHERE tbl_id = '$tbl_id' and fld_id = '$fld_id'";
	$ret = $xoopsDB->query($sql);
	$row = $xoopsDB->fetchArray($ret);
	return $row['fieldname'];

}

function is_fieldkey($fieldname, $tbl_id){
	global $xoopsDB;
	$sql = "SELECT * FROM ".$xoopsDB->prefix('curl_fields')." WHERE tbl_id = '$tbl_id' and fieldname = '$fieldname' and `key` = 1";
	//echo $sql."\n";
	$ret = $xoopsDB->query($sql);
	if (!$xoopsDB->getRowsNum($ret)){
		return false;
	} else {
		return true;
	}

}
if (!function_exists('xoops_isIPv6')) {
	function xoops_isIPv6($ip = "")  
	{  
		if ($ip == "")  
			return false; 
			 
		if (substr_count($ip,":") > 0){  
			return true;  
		} else {  
			return false;  
		}  
	} 
}

if (!function_exists('xoops_getUserIP')) {
	function xoops_getUserIP($ip=false){ 
		$ret = array(); 
		if (is_object($GLOBALS['xoopsUser'])) { 
			$ret['uid'] = $GLOBALS['xoopsUser']->getVar('uid'); 
			$ret['uname'] = $GLOBALS['xoopsUser']->getVar('uname'); 
		} else { 
			$ret['uid'] = 0; 
			$ret['uname'] = $GLOBALS['xoopsConfig']['anonymous']; 
		} 
		$ret['sessionid'] = session_id(); 
		if (!$ip) { 
			if ($_SERVER["HTTP_X_FORWARDED_FOR"] != ""){  
				$ip = (string)$_SERVER["HTTP_X_FORWARDED_FOR"];  
				$ret['is_proxied'] = true; 
				$proxy_ip = $_SERVER["REMOTE_ADDR"];  
				$ret['network-addy'] = @gethostbyaddr($ip);  
				$ret['long'] = @ip2long($ip); 
				if (xoops_isipv6($ip)) { 
					$ret['ip6'] = $ip; 
					$ret['proxy-ip6'] = $proxy_ip; 
				} else { 
					$ret['ip4'] = $ip; 
					$ret['proxy-ip4'] = $proxy_ip; 
				} 
			}else{  
				$ret['is_proxied'] = false; 
				$ip = (string)$_SERVER["REMOTE_ADDR"];  
				$ret['network-addy'] = @gethostbyaddr($ip);  
				$ret['long'] = @ip2long($ip); 
				if (xoops_isipv6($ip)) { 
					$ret['ip6'] = $ip; 
				} else { 
					$ret['ip4'] = $ip; 
				} 
			}  
		} else { 
			$ret['is_proxied'] = false; 
			$ret['network-addy'] = @gethostbyaddr($ip);  
			$ret['long'] = @ip2long($ip); 
			if (xoops_isipv6($ip)) { 
				$ret['ip6'] = $ip; 
			} else { 
				$ret['ip4'] = $ip; 
			} 
		} 
		$ret['md5'] = md5($ip.$ret['long'].$ret['network-addy'].$ret['is_proxied']); 
		$ret['sha1'] = sha1($ip.$ret['long'].$ret['network-addy'].$ret['is_proxied'].$ret['uid']. $ret['uname']);     
		$ret['made'] = time();                 
		return $ret; 
	}
}

function check_for_lock($function_file, $username, $password) {
	xoops_load('cache');
	$userip = xoops_getUserIP();
	$retn = false;
	if ($result = XoopsCache::read('lock_'.$function_file.'_'.$username)) {
		foreach($result as $id => $ret) {
			if ($ret['made']<time()-$GLOBALS['xoopsModuleConfig']['lock_seconds'] || 
				$ret['made']<((time()-$GLOBALS['xoopsModuleConfig']['lock_seconds'])+mt_rand(1, $GLOBALS['xoopsModuleConfig']['lock_random_seed']))) {
				unset($result[$id]);
			} elseif ($ret['md5']==$userip['md5']) {
				$retn = array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $result, $GLOBALS['cache_seconds']);
		return $retn;
	}
}

function mark_for_lock($function_file, $username, $password) {
	xoops_load('cache');
	$userip = xoops_getUserIP();
	$result = array();
	if ($result = XoopsCache::read('lock_'.$function_file.'_'.$username)) {
		$result[] = $userip;
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $result, $GLOBALS['cache_seconds']);
		return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
	} else {
		$result[] = $userip;
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $result, $GLOBALS['cache_seconds']);
		return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
	}
}
?>