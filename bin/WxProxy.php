<?php
define("REDIS_WX_ACCESS_TOKEN", 'wx_access_token');
define("REDIS_WX_ACCESS_TOKEN_EXPIRE", 7000);
define("REDIS_WX_JSAPI_TICKET", 'wx_jsapi_ticket');
define("REDIS_WX_JSAPI_TICKET_EXPIRE", 7000);

class WxProxy extends task{

	public $config = array(
			'appid' => 'wxff3ee0fea37b509b',
			'secret' => 'd4624c36b6795d1d99dcf0547af5443d',
			);

	public $redis;
	public $curl = NULL;
	public function __construct($request) { 
		parent::__construct($request);
		$this->init();
	}

	public function init() { $this->redis = new redis_proxy(); }

	private function tryInitCurl() 
	{ 
		if (empty($this->curl)) { $this->curl = new Curl(); }
	}

	public function run(&$ret)
	{
		if (empty($this->request_data['sub'])) { logger("ERROR", "WxProxy empty sub", array(__CLASS__, __FUNCTION__)); return; }
		logger("DEBUG", "WxProxy request sub is::".$this->request_data['sub']);
		switch($this->request_data['sub'])
		{
			case REDIS_WX_ACCESS_TOKEN : $ret = $this->tryGetAccessToken(); break;
			case REDIS_WX_JSAPI_TICKET: $ret = $this->tryGetJsApiTicket(); break;
			default: $ret = "";
		}
		return true;
	}

	private function tryGet($key, $expire, $get_func, $force = false)
	{
		$token = $this->redis->get_string($key);	
		if (empty($token) OR $force) {
			$new = $this->$get_func();
			if (empty($new)) { logger("ERROR", "get new {$get_func} failed", array(__CLASS__, __FUNCTION__)); return ""; }

			if (!$this->redis->set_string($key, $new, $expire)) {
				logger("ERROR", "set redis key failed：".$key, array(__CLASS__, __FUNCTION__));
			}
		}
		return $this->redis->get_string($key);
	}

	private function tryGetAccessToken($force = false)
	{
		logger("DEBUG", "tryGetAccessToken...");
		$get_func = 'getToken';
		return $this->tryGet(REDIS_WX_ACCESS_TOKEN, REDIS_WX_ACCESS_TOKEN_EXPIRE, $get_func, $force);
	}

	private function tryGetJsApiTicket($force = false)
	{
		logger("DEBUG", "tryGetJsApiTicket...");
		$get_func = 'getJsApiTicket';
		return $this->tryGet(REDIS_WX_JSAPI_TICKET, REDIS_WX_JSAPI_TICKET_EXPIRE, $get_func, $force);
	}

	private function getToken(){
		logger("DEBUG", "get new access_token");
		$this->tryInitCurl();
		$result = $this->curl->rapid("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['appid']}&secret={$this->config['secret']}");

		$ret = "";
		do {
			if (empty($result)) { $ret = ""; break; }
			$result = json_decode($result,true);
			if (!empty($result['errcode'])) { $ret = ""; break; }
			$ret = $result['access_token'];
		}while(0);

		if ($ret == "") { logger("ERROR", "get new access_token failed..." . print_r($result, true) , array(__CLASS__, __FUNCTION__));}
		return $ret;
	}

	private function getJsApiTicket() { 
		logger("DEBUG", "get new js api ticket");
		$accessToken = $this->tryGetAccessToken();
		if (empty($accessToken)) { 
			logger("ERROR", "tryGetAccessToken failed", array(__CLASS__, __FUNCTION__));
			return "";
		}
		$this->tryInitCurl();
		$newTicket = $this->curl->rapid("https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken");

		$ret = "";
		do {
			if (empty($newTicket)) { $ret = ""; break;}
			$newTicket = json_decode($newTicket, true);

			//TODO::token 失效
			if ($newTicket['errcode'] == 40001) { 
				$this->tryGetAccessToken(true);
				return $this->getJsApiTicket();
			}

			if (!empty($newTicket['errcode'])) { $ret = ""; break; }
			$ret =  $newTicket['ticket'];
		}while(0);

		if ($ret == "") { logger("ERROR", "get new js api ticket failed..." . print_r($newTicket, true), array(__CLASS__, __FUNCTION__));}
		return $ret;
	}
}


