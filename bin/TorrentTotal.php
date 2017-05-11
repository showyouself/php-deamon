<?php 
class TorrentTotal extends task {
	public function __construct($request)
	{
		parent::__construct($request);
	}

	public function run(&$ret)
	{
		$ret = $this->tryGetTotal();
		return true;
	}

	private function tryGetTotal()
	{
		$redis = new redis_proxy();
		$total = $redis->get_string(REDIS_TORRENT_KEY);
		if (empty($total)) { $redis->set_string(REDIS_TORRENT_KEY, $this->getTotalDB(), REDIS_TORRENT_EXPIRE); }
		return $redis->get_string(REDIS_TORRENT_KEY);
	}

	private function getTotalDB()
	{
		$sql = "select count(id) as total from magnet";
		$db = new db(db_config());
		if ($db->sql($sql)) { $result = $db->result_row(); }
		return $result['total'];
	}
}
