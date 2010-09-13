<?php
include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/usercheck.php');
	include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/authcheck.php');
	include(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/inc/siteinfocheck.php');
		
	function xoops_check_activation_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$data = array();
			$data[] = array("name" => "uname", "type" => "string");
			$data[] = array("name" => "actkey", "type" => "string");		
			$data_b = array();
				$data_b[] = array("name" => "sitename", "type" => "string");
				$data_b[] = array("name" => "adminmail", "type" => "string");
				$data_b[] = array("name" => "xoops_url", "type" => "string");
			$data[] = array("items" => array("data" => $data_b, "objname" => "siteinfo"));
		$i++;
		$xsd['request'][$i]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'user';
		
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "uname", "type" => "integer");
			$data[] = array("name" => "actkey", "type" => "string");		
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		
		return $xsd;
	}
	
	function xoops_check_activation_wsdl(){
	
	}
	
	function xoops_check_activation_wsdl_service(){
	
	}
	
	function xoops_check_activation($username, $password, $user)
	{	

		global $xoopsModuleConfig, $xoopsConfig;

		if ($xoopsModuleConfig['site_user_auth']==1){
			if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
			if (!checkright(basename(__FILE__),$username,$password)) {
				mark_for_lock(basename(__FILE__),$username,$password);
				return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}


		if ($user['passhash']!=''){
			if ($user['passhash']!=sha1(($user['time']-$user['rand']).$user['uname'].$user['actkey']))
				return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		} else {
			return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		}
		
		foreach($user as $k => $l){
			${$k} = $l;
		}
		
		$siteinfo = check_siteinfo($siteinfo);
		
		include_once XOOPS_ROOT_PATH.'/class/auth/authfactory.php';
		include_once XOOPS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/auth.php';
		$xoopsAuth =& XoopsAuthFactory::getAuthConnection(addslashes($uname));

		if (check_auth_class($xoopsAuth)==true){
			
			$result = $xoopsAuth->check_activation($uname, $actkey, $siteinfo);
			return $result;
			
		} else {
	
			global $xoopsConfig, $xoopsConfigUser;

			global $xoopsDB;
			$sql = "SELECT uid FROM ".$xoopsDB->prefix('users')." WHERE uname = '$uname'";
			$ret = $xoopsDB->query($sql);
			$row = $xoopsDB->fetchArray($ret);
			
		    $member_handler =& xoops_gethandler('member');
			$thisuser =& $member_handler->getUser($row['uid']);
			if (!is_object($thisuser)) {
				exit();
			}
			if ($thisuser->getVar('actkey') != $actkey) {
				$return = array("state" => _US_STATE_ONE, "action" => "redirect_header", "url" => 'index.php', "opt" => 5, "text" => _US_ACTKEYNOT);
			} else {
				if ($thisuser->getVar('level') > 0 ) {
					$return = array("state" => _US_STATE_ONE, "action" => "redirect_header", "url" => 'user.php', "opt" => 5, "text" => _US_ACONTACT, "set" => false);
				} else {
					if (false != $member_handler->activateUser($thisuser)) {
						$config_handler =& xoops_gethandler('config');
						$xoopsConfigUser = $config_handler->getConfigsByCat(XOOPS_CONF_USER);
						if ($xoopsConfigUser['activation_type'] == 2) {
							$myts =& MyTextSanitizer::getInstance();
							$xoopsMailer =& xoops_getMailer();
							$xoopsMailer->useMail();
							$xoopsMailer->setTemplate('activated.tpl');
							$xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
							$xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
							$xoopsMailer->assign('SITEURL', $siteinfo['xoops_url']."/");
							$xoopsMailer->setToUsers($thisuser);
							$xoopsMailer->setFromEmail($siteinfo['adminmail']);
							$xoopsMailer->setFromName($siteinfo['sitename']);
							$xoopsMailer->setSubject(sprintf(_US_YOURACCOUNT,$siteinfo['sitename']));			
							if ( !$xoopsMailer->send() ) {
								$return = array("state" => _US_STATE_TWO, "text" => sprintf(_US_ACTVMAILNG, $thisuser->getVar('uname')));
							} else {
								$return = array("state" => _US_STATE_TWO, "text" => sprintf(_US_ACTVMAILOK, $thisuser->getVar('uname')));
							}
					
						} else {
							$local = explode(' @ ',$thisuser->getVar('user_intrest'));
							if ($local[0] == _US_USERREG){ 
								$return = array("state" => _US_STATE_ONE, "action" => "redirect_header", "url" => $local[1].'/user.php', "opt" => 5, "text" => _US_ACTLOGIN, "set" => false);
							} else {
								$return = array("state" => _US_STATE_ONE, "action" => "redirect_header", "url" => 'user.php', "opt" => 5, "text" => _US_ACTLOGIN, "set" => false);
							}
						}
					} else {
						$return = array("state" => _US_STATE_ONE, "action" => "redirect_header", "url" => 'index.php', "opt" => 5, "text" => 'Activation failed!');
					}
				}
			}
			
			return $return;	

		}
	}

	
?>