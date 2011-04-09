<?php
include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/usercheck.php');
	include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/authcheck.php');	
	include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/siteinfocheck.php');		
	include(XOOPS_ROOT_PATH.'/class/xoopsmailer.php');
	include(XOOPS_ROOT_PATH.'/class/xoopsuser.php');
	
	function xoops_create_user_xsd(){
		$xsd = array();
		$i=0;
		$data = array();
		$data[] = array("name" => "username", "type" => "string");
		$data[] = array("name" => "password", "type" => "string");	
		$datab = array();
			$datab[] = array("name" => "user_viewemail", "type" => "integer");
			$datab[] = array("name" => "uname", "type" => "string");		
			$datab[] = array("name" => "email", "type" => "string");
			$datab[] = array("name" => "url", "type" => "string");		
			$datab[] = array("name" => "actkey", "type" => "string");
			$datab[] = array("name" => "pass", "type" => "string");
			$datab[] = array("name" => "timezone_offset", "type" => "string");
			$datab[] = array("name" => "user_mailok", "type" => "integer");
			$datab[] = array("name" => "passhash", "type" => "string");
			$datab[] = array("name" => "rand", "type" => "integer");
		$data[] = array("items" => array("data" => $datab, "objname" => "user"));
			$data_c = array();
			$data_c[] = array("name" => "sitename", "type" => "string");
			$data_c[] = array("name" => "adminmail", "type" => "string");
			$data_c[] = array("name" => "xoops_url", "type" => "string");
		$data[] = array("items" => array("data" => $datab, "objname" => "siteinfo"));
		
		$i++;
		$xsd['request'][$i]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'var';
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "id", "type" => "integer");
			$data[] = array("name" => "user", "type" => "string");		
			$data[] = array("name" => "text", "type" => "string");
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		
		return $xsd;
	}
	
	function xoops_create_user_wsdl(){
	
	}
	
	function xoops_create_user_wsdl_service(){
	
	}
	
	function xoops_create_user($username, $password, $user, $siteinfo)
	{	

		xoops_load("userUtility");
		
		global $xoopsModuleConfig, $xoopsConfig;
		
		if ($xoopsModuleConfig['site_user_auth']==1){
			if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
			if (!checkright(basename(__FILE__),$username,$password)) {
				mark_for_lock(basename(__FILE__),$username,$password);
				return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}

		
		if ($user['passhash']!=''){
			if ($user['passhash']!=sha1(($user['time']-$user['rand']).$user['uname'].$user['pass']))
				return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		} else {
			return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		}
		
		foreach($user as $k => $l){
			${$k} = $l;
		}
		

		include_once XOOPS_ROOT_PATH.'/class/auth/authfactory.php';
		include_once XOOPS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/auth.php';
		$xoopsAuth =& XoopsAuthFactory::getAuthConnection($uname);



		if (check_auth_class($xoopsAuth)==true){
			
			$result = $xoopsAuth->create_user($user_viewemail, $uname, $email, $url, $actkey, 
				 						  $pass, $timezone_offset, $user_mailok, $siteinfo);
			return $result;
			
		} else {
			if (strlen(userCheck($uname, $email, $pass, $pass))==0){

				global $xoopsConfig;
				$config_handler =& xoops_gethandler('config');
				$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);
				
				$member_handler =& xoops_gethandler('member');
				$newuser =& $member_handler->createUser();
				$newuser->setVar('user_viewemail',$user_viewemail, true);
				$newuser->setVar('uname', $uname, true);
				$newuser->setVar('email', $email, true);
				if ($url != '') {
					$newuser->setVar('url', formatURL($url), true);
				}
				$newuser->setVar('user_avatar','blank.gif', true);
	
				if (empty($actkey))
					$actkey = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
					
				$newuser->setVar('actkey', $actkey, true);
				$newuser->setVar('pass', md5($pass), true);
				$newuser->setVar('timezone_offset', $timezone_offset, true);
				$newuser->setVar('user_regdate', time(), true);
				$newuser->setVar('uorder',$xoopsConfig['com_order'], true);
				$newuser->setVar('umode',$xoopsConfig['com_mode'], true);
				$newuser->setVar('user_mailok',$user_mailok, true);
				$newuser->setVar('user_intrest',_US_USERREG.' @ '.$xoops_url, true);
				if ($xoopsConfigUser['activation_type'] == 1) {
					$newuser->setVar('level', 1, true);
				}
		
				if (!$member_handler->insertUser($newuser, true)) {
					$return = array('state' => 1, "text" => _US_REGISTERNG);
				} else {
					$newid = $newuser->getVar('uid');
					if (!$member_handler->addUserToGroup(XOOPS_GROUP_USERS, $newid)) {
						$return = array('state' => 1, "text" => _US_REGISTERNG);
					}
					if ($xoopsConfigUser['activation_type'] == 1) {
						$return = array('state' => 2,  "user" => $uname);
					}
					// Sending notification email to user for self activation
					if ($xoopsConfigUser['activation_type'] == 0) {
						$xoopsMailer =& xoops_getMailer();
						$xoopsMailer->useMail();
						$xoopsMailer->setTemplate('register.tpl');
						$xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
						$xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
						$xoopsMailer->assign('SITEURL', XOOPS_URL."/");
						$xoopsMailer->setToUsers(new XoopsUser($newid));
						$xoopsMailer->setFromEmail($siteinfo['adminmail']);
						$xoopsMailer->setFromName($siteinfo['sitename']);
						$xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $uname));
						if ( !$xoopsMailer->send() ) {
							$return = array('state' => 1, "text" => _US_YOURREGMAILNG);
						} else {
							$return = array('state' => 1, "text" => _US_YOURREGISTERED);
						}
					// Sending notification email to administrator for activation
					} elseif ($xoopsConfigUser['activation_type'] == 2) {
						$xoopsMailer =& xoops_getMailer();
						$xoopsMailer->useMail();
						$xoopsMailer->setTemplate('adminactivate.tpl');
						$xoopsMailer->assign('USERNAME', $uname);
						$xoopsMailer->assign('USEREMAIL', $email);
						if ($siteinfo['xoops_url']==XOOPS_URL)
							$xoopsMailer->assign('USERACTLINK', $siteinfo['xoops_url'].'/register.php?op=actv&id='.$newid.'&actkey='.$actkey);
						} else {
							$xoopsMailer->assign('USERACTLINK', $siteinfo['xoops_url'].'/register.php?op=actv&uname='.$uname.'&actkey='.$actkey);
						}
						$xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
						$xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
						$xoopsMailer->assign('SITEURL', $siteinfo['xoops_url']."/");
						$member_handler =& xoops_gethandler('member');
						$xoopsMailer->setToGroups($member_handler->getGroup($xoopsConfigUser['activation_group']));
						$xoopsMailer->setFromEmail($siteinfo['adminmail']);
						$xoopsMailer->setFromName($siteinfo['sitename']);
						$xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $uname));
						if ( !$xoopsMailer->send() ) {
							$return = array('state' => 1, "text" => _US_YOURREGMAILNG);
						} else {
							$return = array('state' => 1, "text" => _US_YOURREGISTERED2);
						}
					}
					if ($xoopsConfigUser['new_user_notify'] == 1 && !empty($xoopsConfigUser['new_user_notify_group'])) {
						$xoopsMailer =& xoops_getMailer();
						$xoopsMailer->useMail();
						$member_handler =& xoops_gethandler('member');
						$xoopsMailer->setToGroups($member_handler->getGroup($xoopsConfigUser['new_user_notify_group']));
						$xoopsMailer->setFromEmail($siteinfo['adminmail']);
						$xoopsMailer->setFromName($siteinfo['sitename']);
						$xoopsMailer->setSubject(sprintf(_US_NEWUSERREGAT,$xoopsConfig['sitename']));
						$xoopsMailer->setBody(sprintf(_US_HASJUSTREG, $uname));
						$xoopsMailer->send();
					}

					if (strpos(strtolower($_SERVER['HTTP_HOST']), 'xortify.com')) {
						define('XORTIFY_API_LOCAL', 'http://xortify.chronolabs.coop/soap/');
						define('XORTIFY_API_URI', 'http://xortify.chronolabs.coop/soap/');
					} else {
						define('XORTIFY_API_LOCAL', 'http://xortify.com/soap/');
						define('XORTIFY_API_URI', 'http://xortify.com/soap/');
					}
			
					$soap_client = @new soapclient(NULL, array('location' => XORTIFY_API_LOCAL, 'uri' => XORTIFY_API_URI));
					$result = @$soap_client->__soapCall('xoops_create_user', array("username"=> $username, "password"=> $password, "user" => $user, "siteinfo" => $siteinfo));
								
				return array("ERRNUM" => 1, "RESULT" => $return);
			} else {

			return array("ERRNUM" => 1, "RESULT" => array('state' => 1, 'text' => userCheck($uname, $email, $pass, $pass)));
			}
		}				
	}

?>