<?
	
	function xtorrent_key_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "passhash", "type" => "string");
		$xsd['request'][$i++] = array("name" => "time", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "rand", "type" => "string");

		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "response_key", "type" => "string");
			$data[] = array("name" => "xoops_url", "type" => "string");		
			$data[] = array("name" => "sitename", "type" => "string");					
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		
		return $xsd;
	}
	
	function xtorrent_key_wsdl(){
	
	}
	
	function xtorrent_key_wsdl_service(){
	
	}
	
	function xtorrent_key($username, $password, $passhash, $rand, $time)
	{	
		global $xoopsModule, $xoopsDB;
		
		$config_handler =& xoops_gethandler('config');
		$module = $xoopsModule->getByDirName('xtorrent');

		$config_handler =& xoops_gethandler('config');
		$criteria = new CriteriaCompo(new Criteria('conf_modid', $module->getVar('mid')));
		$configs =& $config_handler->getConfigs($criteria);
		foreach(array_keys($configs) as $i){
			$xTorrentConfig[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
		}
		
		if ($xTorrentConfig['xcurl_servers_exchange']!='1')
		{
			return array('ErrNum'=> 9, "ErrDesc" => 'No torrent exchange permitted, please enable in module.');
		}
		
		global $xoopsModuleConfig, $xoopsConfig;
		
		if ($xoopsModuleConfig['site_user_auth']==1){
			if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
			if (!checkright(basename(__FILE__),$username,$password)) {
				mark_for_lock(basename(__FILE__),$username,$password);
				return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}


		if ($passhash!=''){
			if ($passhash!=sha1(($time-$rand).$username.$password))
				return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		} else {
			return array("ERRNUM" => 4, "ERRTXT" => 'No Passhash');
		}
		
		return array("ERRNUM" => 1, "RESULT" => array("response_key" => $xTorrentConfig['response_key']),
													  "xoops_url" => sprintf("%s",XOOPS_URL),
													  "sitename" => sprintf("%s",$xoopsConfig['sitename']));

	}

?>