<?php 
define('REDIS_TORRENT_KEY', 'torrent_total');
define('REDIS_TORRENT_EXPIRE', 300);
/*
   自定义[http]类示例
   1、需要加载的类必须现在config.php/router_config中定义 
   2、自定义类必须继承类task，并且重写run方法,将返回结果赋值给$ret。
   3、run方法执行成功只需返回true，表示执行成功，程序会将$ret的内容输出
   4、get获取的所有变量存储于task.php/request_data中
   5、usage::curl "http://127.0.0.1:9502?type=torrent_total&test=1"
*/
class ExampleHttp extends task {
	public function __construct($request)
	{
		parent::__construct($request);
	}

	public function run(&$ret)
	{
		if (isset($this->request_data['test'])) { $ret = 'success'; }
		else {
			$ret = $this->tryGetTotal();
		}
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
