<?php
class FunctionsHandler {

	var $functions=array();
	
	function __construct($wsdl){
	
	}
	
	function GetServerExtensions (){
		$files = array();
		$f = array();
		$files = $this->getFileListAsArray(XOOPS_ROOT_PATH.'/modules/xcurl/plugins/');
		static $f_count;
		static $f_buffer;
		
		if ($f_count != count($files)){
			$f_count = count($files);
			foreach($files as $k => $l){
				if (strpos($k,".php",1)== (strlen($k)-4)){
					$f[] = $k;
				}
			}
			$f_buffer = $f;
		}
		
		return $f_buffer;
			
	}
	
	function getDirListAsArray( $dirname ) {
			$ignored = array();
			$list = array();
			if ( substr( $dirname, -1 ) != '/' ) {
				$dirname .= '/';
			}
			if ( $handle = opendir( $dirname ) ) {
				while ( $file = readdir( $handle ) ) {
					if ( substr( $file, 0, 1 ) == '.' || in_array( strtolower( $file ), $ignored ) )	continue;
					if ( is_dir( $dirname . $file ) ) {
						$list[$file] = $file;
					}
				}
				closedir( $handle );
				asort( $list );
				reset( $list );
			}
			//print_r($list);
			return $list;
		}

		/*
		 *  gets list of all files in a directory
		 */
		function getFileListAsArray($dirname, $prefix="")
		{
			$filelist = array();
			if (substr($dirname, -1) == '/') {
				$dirname = substr($dirname, 0, -1);
			}
			if (is_dir($dirname) && $handle = opendir($dirname)) {
				while (false !== ($file = readdir($handle))) {
					if (!preg_match("/^[\.]{1,2}$/",$file) && is_file($dirname.'/'.$file)) {
						$file = $prefix.$file;
						$filelist[$file] = $file;
					}
				}
				closedir($handle);
				asort($filelist);
				reset($filelist);
			}
			return $filelist;
		}
		
	function __destruct(){
	
	}
	
}

?>