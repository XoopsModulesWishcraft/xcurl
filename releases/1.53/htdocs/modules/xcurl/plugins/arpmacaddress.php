<?php 

	function arpmacaddress_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "remoteaddress", "type" => "string");
				
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$xsd['response'][$i++] = array("name" => "RESULT", "type" => "string");
		$xsd['response'][$i++] = array("name" => "MACADDRESS", "type" => "string");
				
		return $xsd;
	}
	
	function arpmacaddress_wsdl(){
	
	}
	
	function arpmacaddress_wsdl_service(){
	
	}
	
	$ret= explode(" ",XOOPS_VERSION);
	$ver= explode(".",$ret[1]);
	
	if ($ret[0]>=2&&$ret[1]>=3){


		function arpmacaddress($username, $password, $remoteaddress)
		{	
	
			global $xoopsModuleConfig, $xoopsConfig;

			if ($xoopsModuleConfig['site_user_auth']==1){
				if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
				if (!checkright(basename(__FILE__),$username,$password)) {
					mark_for_lock(basename(__FILE__),$username,$password);
					return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
				}
			}

			error_reporting(0);
			exec('arping -c 1 '.$remoteaddress,$user_mac);
			$macaddress = substr($user_mac[1],strpos($user_mac[1],':')-2, '17');

			return array('MACADDRESS' => $macaddress);
			
		}
	}
	
?>