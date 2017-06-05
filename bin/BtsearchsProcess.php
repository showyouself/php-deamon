<?php 
require_once("phpQuery/QueryList.php");

define("BTSEARCHS_IS_RUN",'BtsearchsProcess_is_run');
define("BTSEARCHS_TAGS",'BtsearchsProcess_tags');
define("BTSEARCHS_TAGS_ID",'BtsearchsProcess_tags_id');
class BtsearchsProcess{
	public function __construct() {
		$this->init();
		$this->setRunFina();
	}

	public function getURL($title, $page = 1)
	{
		return 'http://www.btsearchs.org/search/' . $title . '-' . $page . '-time.html';
	}

	public function init() { 
		$this->try_init_redis();
		$this->try_init_curl();
		$this->try_init_db();
	}

	public function try_init_redis()
	{
		if (empty($this->redis)) { $this->redis = new redis_proxy(); }
	}

	public function try_init_curl()
	{
		if (empty($this->curl)) { $this->curl = $curl = new Curl(); }
	}

	public function try_init_db()
	{
		if(empty($this->db)) { $this->db = new db(db_config()); }	
	}

	public function run($ret)
	{
		$this->init();

		$this->arg = $ret;

		if (!$this->canRun()) { 
			logger("ERROR", "there has other BtsearchsProcess was running");
			return true; 
		}
		$this->setRunStart();
		
		$tags = $this->getSearchTag();
		if (!$tags) { return false; }
		logger("ERROR", "[NOTICE]do scrawl：".print_r($tags, true));
		foreach($tags as $t) 
		{
			$page = 1;
			$t = $this->strToHex($t);
			$retry = 0;
			do{
				if (!$this->doScrawl($t, $page)) {
					if ($retry > 3) { break; }
					if ($retry > 2) { $page ++ ;}
					$retry ++ ;
					logger("DEBUG", "retry for {$t}：{$page}");
				}else { $page++; }	
			}while(1);
		}

		$this->setRunFina();
		return true;
	}

	private function doScrawl($t, $page)
	{
		$url = $this->getURL($t, $page);
		$regRange = '';
		$reg = array(
				'benyecili' => array('#benyecili','text'),
				);
		$hj = new QueryList($url, $reg, $regRange, 'curl', 'UTF-8');
		logger("DEBUG", "doScrawl for {$url} ");
		if ($hj->html and !empty($hj->jsonArr[0]['benyecili'])) {
			$arr = explode("\n", $hj->jsonArr[0]['benyecili']);
			foreach($arr as $a) 
			{
				$tmp = explode('&' , $a);
				if (count($tmp) < 2) { continue; }
				$data = $this->build(end(explode(":", $tmp[0])), str_replace("dn=", "", $tmp[1]));
				$this->sync_post($data);
			} 
			return true;
		}
/*		logger("ERROR", "fail to doscrawl for {$url} "
				."\nreg : ".print_r($reg, true)
				."regRange : ".$regRange
				."scrwal raw: \n".print_r($hj->html, true)
				);*/
		return false;
	}

	private function build($hash_value, $title)
	{
		return array(	
				'hash_value' => strtolower($hash_value),
				'title' => $title,
				);
	}

	public function sync_post($ret)
	{
		if (!empty($ret) AND !empty($ret['hash_value']) AND !empty($ret['title'])) {
			$bak = $this->curl->rapid($this->arg['sync_url'], 'POST', json_encode($ret));
			$bak = json_decode($bak, true);
			if ($bak['err'] == 0) {  return true; }
		}
		return false;
	}

	private function setRunStart()
	{
		logger("DEBUG", "setRunStart");
		$this->redis->set_string(BTSEARCHS_IS_RUN, 1);
	}

	private function setRunFina()
	{
		logger("DEBUG", "setRunFina");
		$this->redis->set_string(BTSEARCHS_IS_RUN, 0);
	}

	private function canRun()
	{
		if ($this->redis->get_string(BTSEARCHS_IS_RUN) == 1) { return false; }
		else { return true; }
	}

	private function getSearchTag()
	{
		$ret = $this->getFromRedis();
		if ($ret) {
			return array( $ret );
		}
		return false;
	}

	private function getFromRedis()
	{
		$tag = $this->redis->get_ins()->rpop(BTSEARCHS_TAGS);
		if (empty($tag)) {
			if (!$this->setToRedis()) { return false; }
			$tag = $this->redis->get_ins()->rpop(BTSEARCHS_TAGS);
		}
		logger("DEBUG", "get tag".$tag);
		return $tag;
	}

	private function setToRedis()
	{
		$id = $this->redis->get_string(BTSEARCHS_TAGS_ID);		
		if (!empty($id)) { $id = $id + 10; }
		else { $id = 1; }

		$this->redis->set_string(BTSEARCHS_TAGS_ID, $id);
		logger("ERROR", "[NOTICE]select $id from mysql");

		if ($this->db->sql("SELECT * FROM `tags`  where id > $id  ORDER BY `id` ASC limit 10 ")) 
		{ $result = $this->db->result_array(); }

		if (empty($result)) { logger('ERROR','do select from mysql failed'); return false; }

		foreach ($result as $r) { $this->redis->get_ins()->lpush(BTSEARCHS_TAGS, $r['name']); }

		return true;
	}

	public function strToHex($string)//字符串转十六进制
	{ 
		$hex="";
		for($i=0;$i<strlen($string);$i++)
			$hex.=dechex(ord($string[$i]));
		$hex=strtolower($hex);
		return $hex;
	}   

	public function hexToStr($hex)//十六进制转字符串
	{   
		$string=""; 
		for($i=0;$i<strlen($hex)-1;$i+=2)
			$string.=chr(hexdec($hex[$i].$hex[$i+1]));
		return  $string;
	}

}
/*
$test = new  BtsearchsProcess();
$test->init();
$ret = array('sync_url' => 'http://torrent.zengbingo.com/Home/Api/sync_magnet/sign/9BF4D5BC9A62');
$test->run($ret);
*/
