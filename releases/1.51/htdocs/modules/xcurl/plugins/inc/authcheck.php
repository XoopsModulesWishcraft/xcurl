<?php
if (!function_exists('check_auth_class')){
		function check_auth_class($authclass){
			$class_methods = get_class_methods($authclass);	
			foreach ($class_methods as $method_name) {
				switch ($method_name){
				case "validate":
				case "network_disclaimer":
				case "create_user":
				case "check_activation":
					$t++;
					break;
				default:
					break;
				}
			}
		
			if ($t>3)
				return true;
			else
				return false;
		
		}
	}
?>