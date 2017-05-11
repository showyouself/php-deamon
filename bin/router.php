<?php 
include_once("task.php");
include_once("db.php");
include_once("redis_proxy.php");
class Router{
	public $file_base_path = './';

	//key：为请求的type值，value为实例化的类(文件名与类名相同)
	public $router_way = array(
			'torrent_total' => 'TorrentTotal', 
			);

	public function instance() {
		$router = new Router();
		return $router;
	}

	public function init($router_way = array())
	{
		if (!empty($router_way) AND is_array($router_way)) { $this->router_way = $router_way; }


		if (empty($this->router_way) OR !is_array($this->router_way)) {
			logger("ERROR", "nothing for router");
		}

		foreach($this->router_way as $v)
		{
			if (!file_exists($this->file_base_path . $v . '.php')) {
				logger("ERROR", "missing file ::" . $this->file_base_path . $v . '.php');
				return false;
			}		
		}
		return true;
	}

	public function go($request, &$ret)
	{
		$request['type'] = strtolower($request['type']);
		foreach ($this->router_way as $k => $v) {
				if (strcmp($request['type'], $k) == 0 ) { 
					require_once($v . '.php');
					$task = new $v($request);
					$task->run($ret);
					return true;
				}
		}
		return false;
	}
}
