# Resident
演示如何使用swoole做一个Php常驻进程

## 一、目录及文件

```
/log  log目录

/bin  可执行文件目录

/bin/TorrentTotal.php   自编辑类方法

/bin/admin.sh           进程启动|重启|停止方法 使用：./admin usage:<restart|start|stop>

/bin/config.php         配置文件

/bin/db.php             mysql支持类

/bin/phpresident.php    主进程，即开始执行的地方

/bin/redis_proxy.php    redis支持类

/bin/router.php         资源调度类

/bin/task.php           自编辑类必须基础这个类
```

## 二、环境支持

请检查当前环境是否支持 *swoole*
使用命令`php --info |grep swoole` 查看是否已经启动swoole模块；[编译安装swoole](http://zengbingo.com/p/268.html)

如果要使用 *redis* ，配置：/bin/config.php/redis_config
使用命令`php --info |grep redis` 查看是否已经启动swoole模块；[安装配置redis(php)](http://zengbingo.com/p/392.html)

如果要使用 *mysql* ，配置：/bin/config.php/db_config

## 三、使用方法
* 自定义自己需要的类和文件，并且配置加入/bin/config.php/router_config
* 自定义类必须继承类task
* 接收的请求router通过get->type区分加载哪个类，并且执行run方法
* 请求的数据存在$this->request_data中
* 可参考TorrentTotal

## 服务启动
`./admin start`
