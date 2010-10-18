<?
	require_once(XOOPS_ROOT_PATH.'/modules/xtorrent/include/bittorrent.php');				
	require_once XOOPS_ROOT_PATH.'/modules/xtorrent/include/benc.php';
	if (!class_exists('qcp71'))
	{
		require (XOOPS_ROOT_PATH.'/modules/xtorrent/class/qcp71.class.php');
	}

	function xtorrent_send_xsd(){
		$xsd = array();
		$i=0;
		$xsd['request'][$i] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");
		$xsd['request'][$i++] = array("name" => "passhash", "type" => "string");
		$xsd['request'][$i++] = array("name" => "rand", "type" => "integer");
		$xsd['request'][$i++] = array("name" => "time", "type" => "integer");		
		$data = array();
			$data_b[] = array("name" => "lid", "type" => "integer");
		$data[] = array("items" => array("data" => $data_b, "objname" => "selection"));
				
		$xsd['request'][$i++]['items']['data'] = $data;
		$xsd['request'][$i]['items']['objname'] = 'request';
		
		$i=0;
		$xsd['response'][$i] = array("name" => "ERRNUM", "type" => "integer");
		$data_b = array();
			$data_b[] = array("name" => "lid", "type" => "integer");
			$data_b[] = array("name" => "cid", "type" => "integer");
			$data_b[] = array("name" => "title", "type" => "string");		
			$data_b[] = array("name" => "homepage", "type" => "string");
			$data_b[] = array("name" => "version", "type" => "string");
			$data_b[] = array("name" => "size", "type" => "integer");
			$data_b[] = array("name" => "platform", "type" => "string");
			$data_b[] = array("name" => "screenshot", "type" => "string");
			$data_b[] = array("name" => "submitter", "type" => "integer");
			$data_b[] = array("name" => "publisher", "type" => "string");
			$data_b[] = array("name" => "status", "type" => "integer");
			$data_b[] = array("name" => "date", "type" => "integer");
			$data_b[] = array("name" => "hits", "type" => "integer");		
			$data_b[] = array("name" => "user_icq", "type" => "string");
			$data_b[] = array("name" => "rating", "type" => "double");
			$data_b[] = array("name" => "votes", "type" => "integer");
			$data_b[] = array("name" => "comments", "type" => "integer");		
			$data_b[] = array("name" => "license", "type" => "string");
			$data_b[] = array("name" => "mirror", "type" => "string");
			$data_b[] = array("name" => "price", "type" => "string");
			$data_b[] = array("name" => "paypalemail", "type" => "string");		
			$data_b[] = array("name" => "features", "type" => "string");
			$data_b[] = array("name" => "requirements", "type" => "string");
			$data_b[] = array("name" => "homepagetitle", "type" => "string");
			$data_b[] = array("name" => "forumid", "type" => "integer");
			$data_b[] = array("name" => "limitations", "type" => "string");											
			$data_b[] = array("name" => "published", "type" => "integer");
			$data_b[] = array("name" => "expired", "type" => "integer");
			$data_b[] = array("name" => "offline", "type" => "integer");
			$data_b[] = array("name" => "description", "type" => "string");
			$data_b[] = array("name" => "currency", "type" => "string");	
			$data_b[] = array("name" => "crc", "type" => "string");		

		$data_c = array();
		$data_c[] = array("name" => "benc", "type" => "string");		
		$data[] = array("items" => array("data" => $data_b, "objname" => "content"));
		$data[] = array("items" => array("data" => $data_c, "objname" => "benc"));		
		$data[] = array("name" => "skey", "type" => "string");

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
		
		$data = array();
			$data[] = array("name" => "platform", "type" => "string");
			$data[] = array("name" => "license", "type" => "string");											
			$data[] = array("name" => "status", "type" => "integer");
		$i++;
		
		$xsd['response'][$i]['items']['data'] = $data;
		$xsd['response'][$i]['items']['objname'] = 'arrays';		
		return $xsd;
	}
	
	function xtorrent_send_wsdl(){
	
	}
	
	function xtorrent_send_wsdl_service(){
	
	}
	
	function xtorrent_send($username, $password, $passhash, $rand, $time, $request)
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


		$result = $xoopsDB -> query("SELECT * FROM " . $xoopsDB -> prefix('xtorrent_downloads') . " WHERE lid in ('".implode("','",$request['lid'])."')");

		$row = array();
   // include XOOPS_ROOT_PATH . '/header.php';
 //   echo "<br /><div align='center'>" . xtorrent_imageheader() . "</div>";
 //   $url = $myts -> htmlSpecialChars(preg_replace('/javascript:/si' , 'java script:', $url), ENT_QUOTES);
		while ($rrow = $xoopsDB -> fetchArray($result))
		{
			$url = $rrow['url'];
			$lid = $rrow['lid'];
			
			if (!empty($url))
			{
						
				ini_set('allow_url_fopen',true);
				$passkey = passkey_paypal($lid, $username, $password);


				// Begin Download
				
				$fn = str_replace(XOOPS_URL, XOOPS_ROOT_PATH, $url);
				$crc = sha1_file($url);
				$dict = bdec_file($fn, (1024*1024));

				if (empty($dict['value']['announce'])){
					$dict['value']['announce']['type'] = "string";
					$dict['value']['announce']['value'] = str_replace('{XOOPS_URL}', XOOPS_URL, $xTorrentConfig['announce_url'])."?passkey=$passkey";
					$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
					$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
				} else {
					$tracker = array();
					$buffer = array();					
					$tracker['type'] = "list";
					$buffer['type'] = "string";
					$buffer['value'] = str_replace('{XOOPS_URL}', XOOPS_URL, $xTorrentConfig['announce_url'])."?passkey=$passkey";
					if (!empty($dict['value']['announce-list'])){
						
						$buffer['string'] = strlen($buffer['value']).":".$buffer['value'];
						$buffer['strlen'] = strlen($buffer['string']);
						$tracker['value'] = array($buffer);
						$tracker['string'] = "l".$buffer['string']."e";
						$tracker['strlen'] = strlen($tracker['string']);
						$dict['value']['announce-list']['value'][count($dict['value']['announce-list']['value'])] = $tracker;
						$dict['value']['announce-list']['string'] = substr($dict['value']['announce-list']['string'],0,strlen($dict['value']['announce-list']['string'])-2)."l".$buffer['string']."ee";
						$dict['value']['announce-list']['strlen'] = strlen($dict['value']['announce-list']['string']);
					} else {
						$dict['value']['announce-list']['type'] = "list";
						$buffer2 = array();
						
						$buffer2['type'] = "string";
						$buffer2['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
						$buffer2['value'] = $dict['value']['announce']['value'];
						$buffer2['strlen'] = strlen($buffer2['string']);
						$tracker['value'] = array($buffer2);
						$tracker['string'] = "l".$buffer2['string']."e";
						$tracker['strlen'] = strlen($tracker['string']);
						$dict['value']['announce-list']['value'][count($dict['value']['announce-list']['value'])] = $tracker;
						
						$buffer['string'] = strlen($buffer['value']).":".$buffer['value'];
						$buffer['strlen'] = strlen($buffer['string']);
						$tracker['value'] = array($buffer);
						$tracker['string'] = "l".$buffer['string']."e";
						$tracker['strlen'] = strlen($tracker['string']);
						
						$dict['value']['announce-list']['value'][count($dict['value']['announce-list']['value'])] = $tracker;
						$dict['value']['announce-list']['string'] = "ll".$buffer2['string']."".$buffer['string']."ee";
						$dict['value']['announce-list']['strlen'] = strlen($dict['value']['announce-list']['string']);
					}							
					//@array_walk($rrow, "walk_uuencode");
					$rrow['crc'] = $crc;
					$row['data'][] = array("content" => $rrow,
	 						         	   "benc" => convert_uuencode(benc($dict)));
				}
			}
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
	if (!function_exists('passkey_paypal'))
	{
		function passkey_paypal($lid, $uname, $pass)
		{
		
			global $xoopsUser, $xoopsDB, $xoopsModuleConfig, $myts;
			
			$sql = "SELECT cid, price, paypalemail, currency, title, description, ipaddress FROM ".$xoopsDB->prefix("xtorrent_downloads")." where lid = $lid";
			$result = $xoopsDB->queryF($sql);
			list($cid, $price, $paypalemail, $currency, $title, $description, $ipaddress) = $xoopsDB->fetchRow($result);
			
			if (!empty($price)&&(float)$price>0&&!empty($paypalemail)&&$ipaddress!=$_SERVER['REMOTE_ADDR'])
			{
				$sql = "select id, passkey from ".$xoopsDB->prefix('xtorrent_users'). " where username='".$uname."' and uid='".$uid."' and lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') and enabled = 'yes' order by last_access, id";
				$rt = $xoopsDB->queryF($sql);				
				if ($xoopsDB->getRowsNum($rt)){			
					$sql = "select id, passkey from ".$xoopsDB->prefix('xtorrent_users'). " where lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') and enabled = 'yes' order by last_access, id";
					$rt = $xoopsDB->queryF($sql);				
				}
				$rt = $xoopsDB->queryF($sql);				
				if ($xoopsDB->getRowsNum($rt)){
					if ($made=="yes")
					{
						list($id, $passkey) = $xoopsDB->fetchRow($rt);
						$sql = "UDPATE ".$xoopsDB->prefix('xtorrent_users'). " SET enabled = 'yes', last_access = '".date("Y-m-d H:i:s")."' WHERE id = '".$id."'";
						$rt = $xoopsDB->queryF($sql);				
						$payment_made = true;
					} else {
						list($id, $passkey) = $xoopsDB->fetchRow($rt);
						$sql = "SELECT id FROM ".$xoopsDB->prefix('xtorrent_payments'). " WHERE custom = '".$passkey."'";
						$rt = $xoopsDB->queryF($sql);				
						if ($xoopsDB->getRowsNum($rt)){
							$sql = "UDPATE ".$xoopsDB->prefix('xtorrent_users'). " SET enabled = 'yes', last_access = '".date("Y-m-d H:i:s")."' WHERE id = '".$id."'";
							$rt = $xoopsDB->queryF($sql);
							$payment_made = true;
						}				
					}
				} else {
					$sql = "select id, $passkey from ".$xoopsDB->prefix('xtorrent_users'). " where username='".$uname."' and uid='".$uid."' and lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') order by last_access, id";
					$rt = $xoopsDB->queryF($sql);				
					if (!$xoopsDB->getRowsNum($rt)){			
						$sql = "select id, $passkey from ".$xoopsDB->prefix('xtorrent_users'). " where lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') order by last_access, id";
						$rt = $xoopsDB->queryF($sql);				
					}
					if (!$xoopsDB->getRowsNum($rt)){
						$sql = "delete from ".$xoopsDB->prefix('xtorrent_users'). " where uid=".$uid." and username=".$uname." and lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') and enabled = 'no'";
						$rt = $xoopsDB->queryF($sql);
						$sql = "insert into ".$xoopsDB->prefix('xtorrent_users'). " (username, uid, old_password, secret, lid, enabled) VALUES ('".$uname."', ".$uid.", '".$pass."', sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."'),'$lid', 'no')";
						$rt = $xoopsDB->queryF($sql);
					} else {
						$sql = "delete from ".$xoopsDB->prefix('xtorrent_users'). " where uid=".$uid." and username=".$uname." and lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') and enabled = 'no'";
						$rt = $xoopsDB->queryF($sql);
						$sql = "insert into ".$xoopsDB->prefix('xtorrent_users'). " (username, uid, old_password, secret, lid, enabled) VALUES ('".$uname."', ".$uid.", '".$pass."', sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."'),'$lid', 'no')";
						$rt = $xoopsDB->queryF($sql);
					}
					if($rt){
						$kid = $xoopsDB->getInsertId();
						$sql = "update ".$xoopsDB->prefix('xtorrent_users'). " set passhash = md5(concat(secret, old_password, secret, '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."')), last_access = '".date("Y-m-d H:i:s")."' where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
						$sql = "select * from ".$xoopsDB->prefix('xtorrent_users'). " where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
						$row = $xoopsDB->fetchArray($rt); 
						$crc = new qcp71($lid.$kid.$row['username'].get_date_time().$row['passhash'], mt_rand(17,245), mt_rand(31,121));
						$passkey = $crc->crc;
						$sql = "update ".$xoopsDB->prefix('xtorrent_users'). " set passkey = '".$passkey ."', last_access = '".date("Y-m-d H:i:s")."' where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
					}
		
					$payment_made = false;
				}
		
			} else {	
				$sql = "select id, passkey from ".$xoopsDB->prefix('xtorrent_users'). " where username='".$uname."' and uid='".$uid."' and lid = $lid and secret = sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."') and enabled = 'yes'";
				$rt = $xoopsDB->queryF($sql);				
				if (!$xoopsDB->getRowsNum($rt)){
					$sql = "insert into ".$xoopsDB->prefix('xtorrent_users'). " (username, uid, old_password, secret, lid, enabled) VALUES ('".$uname."', ".$uid.", '".$pass."', sha1('".xtorrent_get_base_domain(gethostbyaddr($_SERVER['REMOTE_ADDR']))."'),'$lid', 'yes')";
					$rt = $xoopsDB->queryF($sql);
		
					if($rt){
						$kid = $xoopsDB->getInsertId();
						$sql = "update ".$xoopsDB->prefix('xtorrent_users'). " set passhash = md5(concat(secret, old_password, secret, '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."')), last_access = '".date("Y-m-d H:i:s")."' where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
						$sql = "select * from ".$xoopsDB->prefix('xtorrent_users'). " where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
						$row = $xoopsDB->fetchArray($rt); 
						$crc = new qcp71($lid.$kid.$row['username'].get_date_time().$row['passhash'], mt_rand(17,245), mt_rand(31,121));
						$passkey = $crc->crc;
						$sql = "update ".$xoopsDB->prefix('xtorrent_users'). " set passkey = '".$passkey ."', last_access = '".date("Y-m-d H:i:s")."' where id = ".$kid ;
						$rt = $xoopsDB->queryF($sql);
					}
					
				} else {
					list($id, $passkey) = $xoopsDB->fetchRow($rt); 
				}
				$payment_made = true;
			}
			
			return $passkey;
		
		}
	}
	
	if (!function_exists('xtorrent_get_base_domain'))
	{
		function xtorrent_get_base_domain($url) 
		{
		  
		  // generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
		  $G_TLD = array(
			'biz','com','edu','gov','info','int','mil','name','net','org',
			'aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel',
			'arpa','root','berlin','bzh','cym','gal','geo','kid','kids','lat','mail',
			'nyc','post','sco','web','xxx','nato', 'example','invalid','localhost','test',
			'bitnet','csnet','ip','local','onion','uucp','geek','co','go','spy','travel','int','asia'
		  );
		  
		  // country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
		  $C_TLD = array(
			// active
			'ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az',
			'ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bw','by','bz',
			'ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz',
			'de','dj','dk','dm','do','dz','ec','ee','eg','er','es','et','eu','fi','fj','fk','fm','fo',
			'fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw',
			'gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je',
			'jm','jo','jp','ke','kg','kh','ki','km','kn','kr','kw','ky','kz','la','lb','lc','li','lk',
			'lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq',
			'mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np',
			'nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pn','pr','ps','pt','pw','py','qa',
			're','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si','sk','sl','sm','sn','sr','st',
			'sv','sy','sz','tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tr','tt','tv','tw',
			'tz','ua','ug','uk','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yu',
			'za','zm','zw','io','eh','kp','me','rs','um','bv','gb','pm','sj','so','yt','su','tp',
			'bu','cs','dd','zr'
			);
		  
			// break up domain, reverse
			$domain = explode('.', $url);
			$domain = array_reverse($domain);
			
			// first check for ip address
			if ( count($domain) == 4 && is_numeric($domain[0]) && is_numeric($domain[3]) )
			{
			  return implode('.', $domain);
			}
			
			// if only 2 domain parts, that must be our domain
			if ( count($domain) <= 2 ) return $url;
			
			if ( in_array($domain[0], $C_TLD) && in_array($domain[1], $G_TLD) && $domain[2] != 'www' )
			{
			  $full_domain = $domain[2] . '.' . $domain[1] . '.' . $domain[0];
			}
			else
			{
			  $full_domain = $domain[1] . '.' . $domain[0];;
			}
		  
		  // did we succeed?  
		  return $domain;
		} 
	}
	
	if (!function_exists("walk_uuencode"))
	{
		function walk_uuencode(&$item1, $key, $prefix)
		{
			$item1 = convert_uuencode($item1);
		}
	}
?>