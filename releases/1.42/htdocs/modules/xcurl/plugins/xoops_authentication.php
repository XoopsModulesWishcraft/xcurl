<?php
function xoops_authentication_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$data = array();
			$data[] = array("name" => "username", "type" => "string");
			$data[] = array("name" => "password", "type" => "string");		
		$xsd['request'][$i++]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'auth';
		
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "uid", "type" => "integer");
			$data[] = array("name" => "uname", "type" => "string");		
			$data[] = array("name" => "email", "type" => "string");
			$data[] = array("name" => "user_from", "type" => "string");
			$data[] = array("name" => "name", "type" => "integer");
			$data[] = array("name" => "url", "type" => "string");		
			$data[] = array("name" => "user_icq", "type" => "string");
			$data[] = array("name" => "user_sig", "type" => "string");
			$data[] = array("name" => "user_viewemail", "type" => "integer");
			$data[] = array("name" => "user_aim", "type" => "string");		
			$data[] = array("name" => "user_yim", "type" => "string");
			$data[] = array("name" => "user_msnm", "type" => "string");
			$data[] = array("name" => "attachsig", "type" => "integer");
			$data[] = array("name" => "timezone_offset", "type" => "string");		
			$data[] = array("name" => "notify_method", "type" => "integer");
			$data[] = array("name" => "user_occ", "type" => "string");											
			$data[] = array("name" => "bio", "type" => "string");											
			$data[] = array("name" => "user_intrest", "type" => "string");	
			$data[] = array("name" => "user_mailok", "type" => "integer");																			
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		
		return $xsd;
	}
	
	function xoops_authentication_wsdl(){
	
	}
	
	function xoops_authentication_wsdl_service(){
	
	}
	
	function xoops_authentication($username, $password, $auth)
	{	

		global $xoopsModuleConfig, $xoopsConfig;
		
		if ($xoopsModuleConfig['site_user_auth']==1){
			if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
			if (!checkright(basename(__FILE__),$username,$password)) {
				mark_for_lock(basename(__FILE__),$username,$password);
				return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}


		if ($auth['passhash']!=''){
			if ($auth['passhash']!=sha1(($auth['time']-$auth['rand']).$auth['username'].$auth['password']))
				return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		} else {
			return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		}

		require_once XOOPS_ROOT_PATH.'/class/auth/authfactory.php';
		require_once XOOPS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/auth.php';
		$xoopsAuth =& XoopsAuthFactory::getAuthConnection(addslashes($auth['username']));
		$user = $xoopsAuth->authenticate(addslashes($auth['username']), addslashes($auth['password']));
		
		if(is_object($user))
			$row =array("uid" => $user->getVar('uid'),"uname" => $user->getVar('uname'),"email" => $user->getVar('email'), "user_from" => $user->getVar('user_from'), "name" => $user->getVar('name'), "url" => $user->getVar('url'), "user_icq" => $user->getVar('user_icq'), "user_sig" => $user->getVar('user_sig'), "user_viewemail" => $user->getVar('user_viewemail'), "user_aim" => $user->getVar('user_aim'), "user_yim" => $user->getVar('user_yim'), "user_msnm" => $user->getVar('user_msnm'), "attachsig" => $user->getVar('attachsig'), "timezone_offset" => $user->getVar('timezone_offset'), "notify_method" => $user->getVar('notify_method'), "user_occ" => $user->getVar('user_occ'), "bio" => $user->getVar('bio'), "user_intrest" => $user->getVar('user_intrest'), "user_mailok" => $user->getVar('user_mailok'));
						
		
		if (!empty($row)){
			return array("ERRNUM" => 1, "RESULT" => $row);
		} else {
			return array("ERRNUM" => 3, "ERRTXT" => _ERR_FUNCTION_FAIL);
		}				

	}

?>