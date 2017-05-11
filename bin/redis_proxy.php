<?php 
class redis_proxy{
	public $redis;
	public function __construct() { $this->init();	}

	public function init(){ 
		$this->redis = new Redis(); 
		if (!$this->redis->connect(redis_config()['host'], redis_config()['port'])) { logger("ERROR", "connect to redis failed， check host and port first"); }

	}

	public function get_ins() { return $this->redis; }

	public function get_string($key) { return $this->redis->get($key);	}

	public function set_string($key, $string, $expire = NULL) { 
		if (!$this->redis->set($key, $string)) 
		{ logger("ERROR", "reids key is already set：$key"); return false; } 

		if (!empty($expire) AND is_numeric($expire) AND $this->set_expire($key, $expire))
		{ logger("ERROR", "reids set expire failed key：$key"); return false;}

		return true;
	}

	public function set_expire($key, $expire){ return $this->redis->expire($key, $expire); }

}

