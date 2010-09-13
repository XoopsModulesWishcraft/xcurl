<?
	
	function xtorrent_peers_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "passhash", "type" => "string");
		$xsd['request'][$i++] = array("name" => "rand", "type" => "integer");
		$xsd['request'][$i++] = array("name" => "time", "type" => "integer");
		$data = array();
			$data[] = array("name" => "lid", "type" => "string");
			$data[] = array("name" => "seed", "type" => "string");
			$data[] = array("name" => "connectable", "type" => "string");
			
		$xsd['request'][$i++]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'request';
		
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "id", "type" => "integer");
			$data[] = array("name" => "torrent", "type" => "integer");
			$data[] = array("name" => "peer_id", "type" => "string");		
			$data[] = array("name" => "ip", "type" => "string");
			$data[] = array("name" => "port", "type" => "string");
			$data[] = array("name" => "uploaded", "type" => "integer");
			$data[] = array("name" => "downloaded", "type" => "string");
			$data[] = array("name" => "to_go", "type" => "string");
			$data[] = array("name" => "seeder", "type" => "integer");
			$data[] = array("name" => "started", "type" => "string");
			$data[] = array("name" => "last_action", "type" => "integer");
			$data[] = array("name" => "connectable", "type" => "integer");
			$data[] = array("name" => "userid", "type" => "integer");		
			$data[] = array("name" => "agent", "type" => "string");
			$data[] = array("name" => "finishedat", "type" => "double");
			$data[] = array("name" => "downloadoffset", "type" => "integer");
			$data[] = array("name" => "uploadoffset", "type" => "integer");		
		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'RESULT';
		$i++;
		$xsd['response'][$i] = array("name" => "skey", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "sitename", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "adminemail", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "xoops_url", "type" => "string");

		
		return $xsd;
	}
	
	function xtorrent_peers_wsdl(){
	
	}
	
	function xtorrent_peers_wsdl_service(){
	
	}
	
	function xtorrent_peers($username, $password, $passhash, $rand, $time, $request)
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

		switch ($request['datefield'])
		{
			case "date":
			case "published":
			case "expired":
			case "updated":
				break;
			default:
				$request['datefield'] = "date";
		}

	
		$sql = "SELECT * FROM ".$xoopsDB->prefix('xtorrent_peers')." WHERE ".
			   " torrent IN ('".implode("','",$request['id'])."')";

		switch ($request['seed'])
		{
			case "yes":
			case "no":
				$sql .= " AND seed = '".$request['seed']."'";
			default:
		}
		
		switch ($request['seed'])
		{
			case "yes":
			case "no":
				$sql .= " AND connectable = '".$request['connectable']."'";
			default:
		}
		
		$ref = $xoopsDB->query($sql);				
		$row = array();
		
		while ($rec = $xoopsDB->fetchArray($ref))
		{
			$row["RESULT"][] = applyspecialchars($rec);
		}
		
		global $xoopsConfig;
		if (!empty($row)){
			$row['skey'] = $xTorrentConfig['response_key'];
			$row['sitename'] = $xoopsConfig['sitename'];
			$row['adminemail'] = $xoopsConfig['adminemail'];
			$row['xoops_url'] = sprintf("%s",XOOPS_URL);
			return array("ERRNUM" => 1, $row);//$row);
		} else {
			return array("ERRNUM" => 3, "ERRTXT" => _ERR_FUNCTION_FAIL);
		}				
			

	}
	
	if (!function_exists('applyspecialchars'))
	{
		function applyspecialchars($rec)
		{
			$res = array();
			foreach ($rec as $k => $l)
			{
				$res[$k] = convert_uuencode($l);
			}
			return $res;
		}
	}	
?>