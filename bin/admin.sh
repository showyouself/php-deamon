#!/bin/bash
#移动到运行命令
cd /home/ben/work/swoole/phpdeamon/bin
process="phpresident.php"

usage(){
	echo "usage:<restart|start|stop>"
}

if [ ! -n "$1" ]
then
	usage;
	exit;
fi

#接受信号
if [ $1 == "stop" ]
then
	`kill -9 $(ps -ef|grep $process|grep -v "grep"|awk '{print $2}')`
	echo "stop success"
elif [ $1 == "start" ]
then	
	php $process
	echo "start success"
elif [ $1 == "restart" ]
then 
	`kill -9 $(ps -ef|grep $process|grep -v "grep"|awk '{print $2}')`
	php $process
	echo "restart success"
elif [ $1 == "status" ]
then 
	ps -ef|grep $process
else
	usage;
fi

