<?php
//日志打印
function logger($level, $msg, $trace_arr = array()) { 
	do {
		if (log_config()['log_level'] == 0 AND strtoupper($level) != "ERROR") { break; }
		$message = "[$level][" . implode("][",$trace_arr) . "] - " . date("Y-m-d H:i:s",time()) . ' -->' . $msg . PHP_EOL; 
		if ( $fp = @fopen(log_config()['log_file'].'/phpdeamon.log', 'a+'))
		{
			flock($fp, LOCK_EX);
			fwrite($fp, $message);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}while(0);
}


function nonce_str($length = 16) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str = "";
	for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	return $str;
}
