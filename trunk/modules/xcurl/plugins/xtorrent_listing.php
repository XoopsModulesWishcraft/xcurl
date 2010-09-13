<?
	
	function xtorrent_listing_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "passhash", "type" => "string");
		$xsd['request'][$i++] = array("name" => "rand", "type" => "integer");
		$xsd['request'][$i++] = array("name" => "time", "type" => "integer");
		$data = array();
			$data[] = array("name" => "cid", "type" => "string");
			$data[] = array("name" => "from", "type" => "string");
			$data[] = array("name" => "to", "type" => "string");
			$data[] = array("name" => "datefield", "type" => "string");		
		$xsd['request'][$i++]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'request';
		
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data = array();
			$data[] = array("name" => "lid", "type" => "integer");
			$data[] = array("name" => "cid", "type" => "integer");
			$data[] = array("name" => "title", "type" => "string");		
			$data[] = array("name" => "homepage", "type" => "string");
			$data[] = array("name" => "version", "type" => "string");
			$data[] = array("name" => "size", "type" => "integer");
			$data[] = array("name" => "platform", "type" => "string");
			$data[] = array("name" => "screenshot", "type" => "string");
			$data[] = array("name" => "submitter", "type" => "integer");
			$data[] = array("name" => "publisher", "type" => "string");
			$data[] = array("name" => "status", "type" => "integer");
			$data[] = array("name" => "date", "type" => "integer");
			$data[] = array("name" => "hits", "type" => "integer");		
			$data[] = array("name" => "user_icq", "type" => "string");
			$data[] = array("name" => "rating", "type" => "double");
			$data[] = array("name" => "votes", "type" => "integer");
			$data[] = array("name" => "comments", "type" => "integer");		
			$data[] = array("name" => "license", "type" => "string");
			$data[] = array("name" => "mirror", "type" => "string");
			$data[] = array("name" => "price", "type" => "string");
			$data[] = array("name" => "paypalemail", "type" => "string");		
			$data[] = array("name" => "features", "type" => "string");
			$data[] = array("name" => "requirements", "type" => "string");
			$data[] = array("name" => "homepagetitle", "type" => "string");
			$data[] = array("name" => "forumid", "type" => "integer");
			$data[] = array("name" => "limitations", "type" => "string");											
			$data[] = array("name" => "published", "type" => "integer");
			$data[] = array("name" => "expired", "type" => "integer");
			$data[] = array("name" => "offline", "type" => "integer");
			$data[] = array("name" => "description", "type" => "string");
			$data[] = array("name" => "currency", "type" => "string");

		$i++;
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'data';
		$i++;
		$xsd['response'][$i] = array("name" => "skey", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "sitename", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "adminemail", "type" => "string");
		$i++;
		$xsd['response'][$i] = array("name" => "xoops_url", "type" => "string");
		
		$data = array();
			$data[] = array("name" => "platform", "type" => "integer");
			$data[] = array("name" => "license", "type" => "string");											
			$data[] = array("name" => "status", "type" => "integer");
		$i++;
		
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'arrays';
		return $xsd;
	}
	
	function xtorrent_listing_wsdl(){
	
	}
	
	function xtorrent_listing_wsdl_service(){
	
	}
	
	function xtorrent_listing($username, $password, $passhash, $rand, $time, $request)
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
			return array('ErrNum'=> 9, "ErrDesc" => 'No torrent exchanged permitted, please enable in module.');
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
				$request['datefield'] = "published";
		}

		if ($request['to']==0)
		{ $request['to']=time(); 
		  $request['from']=0; }
		
		$sql = "SELECT * FROM ".$xoopsDB->prefix('xtorrent_downloads')." WHERE "
			   .$request['datefield']." > ".$request['from']." AND "
			   .$request['datefield']." < ".$request['to']."";
		if (is_array($request['cid']))
		{
			$sql .= " AND cid in ('".implode("','",$request['cid'])."')";
		} elseif ($request['cid']>0) {
			$sql .= " AND cid in ('".$request['cid']."')";
		}

		$ref = $xoopsDB->queryF($sql);				
		$row = array();
		ini_set('allow_url_fopen',true);
		
		while ($rec = $xoopsDB->fetchArray($ref))
		{
			
			$fn = str_replace(XOOPS_URL, XOOPS_ROOT_PATH, $rec['url']);
			$crc_arry = array("crc" => sha1_file($fn));
			$row["data"][] = array_merge(applyspecialchars($rec), $crc_arry);
		}
		
		if (!empty($row)){
			$row['skey'] = $xTorrentConfig['response_key'];
			$row['sitename'] = $xoopsConfig['sitename'];
			$row['adminemail'] = $xoopsConfig['adminemail'];
			
			$row['xoops_url'] = sprintf("%s",XOOPS_URL);
			
			$row['arrays']['platform'] = $xTorrentConfig['platform'];
			$row['arrays']['license'] = $xTorrentConfig['license'];
			$row['arrays']['status'] = $xTorrentConfig['status'];
			
			return array("ERRNUM" => 1, "RESULT" => $row);
		} else {
			return array("ERRNUM" => 3, "ERRTXT" => _ERR_FUNCTION_FAIL);
		}				

	}
	
	if (!function_exists('applyspecialchars'))
	{
		function applyspecialchars_b($rec)
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