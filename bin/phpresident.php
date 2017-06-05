<?php
require_once("config.php");
require_once("router.php");
require_once('misc.php');

//加载路由类
$router = new Router();
if (!$router->init(router_config())) { return false; }

//创建Server对象，监听 127.0.0.1:9502端口
$serv = new swoole_http_server("127.0.0.1", 9502); 

//daemonize 设置为1，及在后台运行
$serv->set( swoole_config() );

//监听数据接收事件[http]
$serv->on('request', function ($data, $resp) use ($router) {
			global $serv;
			do {
				$request = $data->get;
		 		logger("DEBUG", "[request] from {$serv->host}:{$serv->port} dump data::".print_r($request, true));	

				$ret = "";
				if (empty($request['type'])) { $ret = "missing request type"; logger("ERROR", $ret); break; }

				if (!$router->go($request, $ret)) { $ret = "router go failed::" . $ret; logger("ERROR", $ret);  break;}

				logger("DEBUG", "[response] {$serv->host}:{$serv->port} echo data::".print_r($ret, true));
			}while(0);
			$resp->end($ret); 

			});
//启动服务器
logger("DEBUG", "服务器启动成功");
//自定义进程[processer]
require_once("trigger_processer.php");
if (!empty(processer_config())) {
	foreach (processer_config() as $p_v) {
		$process = new swoole_process(function ($process) use ($serv, $p_v){
				$task_tmpl = new ReflectionClass('trigger_processer');
				logger("DEBUG", "添加自定义进程 ".$p_v['name']);
				call_user_func_array(array($task_tmpl->newInstance($p_v), 'run'), array($serv, $process));
				}); 
		$serv->addProcess($process);
	}    
}   

$serv->start(); 
?>
