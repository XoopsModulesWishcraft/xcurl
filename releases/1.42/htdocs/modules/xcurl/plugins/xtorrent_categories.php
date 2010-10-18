<?
	
	function xtorrent_categories_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "passhash", "type" => "string");
		$xsd['request'][$i++] = array("name" => "rand", "type" => "integer");
		$xsd['request'][$i++] = array("name" => "time", "type" => "integer");	
					
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "cid", "type" => "integer");
			$data[] = array("name" => "title", "type" => "integer");
			$data[] = array("name" => "description", "type" => "string");		
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		$xsd['response'][$i++] = array("name" => "skey", "type" => "string");			
		return $xsd;
	}
	
	function xtorrent_categories_wsdl(){
	
	}
	
	function xtorrent_categories_wsdl_service(){
	
	}
	
	function xtorrent_categories($username, $password, $passhash, $rand, $time)
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
		
		if ($xTorrentConfig['xcurl_servers_send']!='1')
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

		
		$sql = "SELECT cid, title, description FROM ".$xoopsDB->prefix('xtorrent_cat')."";

		$ref = $xoopsDB->queryF($sql);				
		$row = array();
		
		while ($rec = $xoopsDB->fetchArray($ref))
		{
			$row['cats'][$u++] = $rec;
		}
		
		if (!empty($row)){
			$row['skey'] = $xTorrentConfig['response_key'];
			return array("ERRNUM" => 1, "RESULT" => $row);
		} else {
			return array("ERRNUM" => 3, "ERRTXT" => _ERR_FUNCTION_FAIL);
		}				

	}

?>