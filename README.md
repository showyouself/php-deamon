# Resident
一个基于swoole编写的 ：Php常驻进程框架

## 一、目录及文件

```
/log  log目录

/bin  可执行文件目录

/bin/admin.sh           进程启动|重启|停止方法 使用：./admin usage:<restart|start|stop>

/bin/phpresident.php    主进程，即开始执行的地方

/bin/config.php         配置文件

/bin/misc.php           通用函数

/bin/router.php         资源调度类

/bin/trigger_processer.php 进程处理类

/bin/task.php           自编辑类必须继承这个类

/bin/db.php             mysql支持类

/bin/redis_proxy.php    redis支持类

/bin/curl.php           curl支持类

/bin/ExampleHttp.php      [自定义]自定义http类示例

/bin/ExampleProcess.php   [自定义]自定义进程类示例

/bin/WxProxy.php        [自定义]微信access_token、js_api_ticket维护支持类

```

## 二、环境支持
【必选】请检查当前环境是否支持 *swoole*
使用命令`php --info |grep swoole` 查看是否已经启动swoole模块；**[编译安装swoole](http://zengbingo.com/p/268.html)**

【可选】如果要使用 *redis* ，配置：/bin/config.php/redis_config
使用命令`php --info |grep redis` 查看是否已经启动swoole模块；**[安装配置redis(php)](http://zengbingo.com/p/392.html)**

【可选】如果要使用 *mysql* ，配置：/bin/config.php/db_config


## 三、使用方法
### 一、自定义http类
* 自定义自己需要的类和文件，并且配置加入/bin/config.php/router_config
* 自定义类必须继承类task，可参考：ExampleHttp.php
* 接收的请求后，router通过get->type区分加载哪个类，并且执行run方法
* 请求示例：curl "http://127.0.0.1:9502?type=wx&sub=wx_jsapi_ticket" **type【必选】:指定执行(加载)的类**，get中的其他变量存于task.php/request_data中

### 二、自定义进程类
* 自定义自己需要的类和文件，并且配置加入/bin/config.php/processer_config
* 自定义类必须继承类task，可参考：ExampleProcess.php
* 设置run_interval来使程序定时执行run函数
* run函数需返回true表示执行成功，返回false将不再继续循环执行

## 四、目前支持模块(以及需要的环境支持)
```
模块：[http]WxProxy.php
模块名称：微信token\jsTicket维护
需要环境：redis
```

## 五、服务启动
`./admin.sh start`

## 六、发布日志 
*tag v0.2* 主要特性：
* 添加微信公众号access_token、js_api_ticket维护进程
* 优化日志生成方式
* 添加curl支持类
* 修复redis_proxy.php中，设置timeout失败误报错误

*tag v0.3*主要特性：
* 修改TorrentTotal用于展示一个示例

*tag v20170514-add-process-func*主要特性：
* 新增进程处理类trigger_processer.php
* 新增ExampleProcess.php示例演示进程方法
* phpresident.php/config.php 修改以支持子进程处理
* 将config.php文件中的通用函数移动至misc.php
* 修改Torrent_Total的名称为ExampleHttp.php


