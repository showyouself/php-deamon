# Resident
演示如何使用swoole做一个Php常驻进程

##一、目录及文件

`
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
`
