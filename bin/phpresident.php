<?php
require_once("config.php");
require_once("router.php");

//加载路由类
$router = new Router();
if (!$router->init(router_config())) { return false; }

//创建Server对象，监听 127.0.0.1:9502端口
$serv = new swoole_http_server("127.0.0.1", 9502); 

//daemonize 设置为1，及在后台运行
$serv->set( swoole_config() );

//监听数据接收事件
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

$serv->start(); 
?>
