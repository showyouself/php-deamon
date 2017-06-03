#!/bin/bash
#移动到运行命令
cd /home/ben/work/swoole/phpdeamon/bin
process="phpresident.php"
log="/home/ben/work/swoole/phpdeamon/log/phpdeamon.log"

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
	php $process >> $log
	echo "start success"
elif [ $1 == "restart" ]
then 
	`kill -9 $(ps -ef|grep $process|grep -v "grep"|awk '{print $2}')`
	php $process >> $log
	echo "restart success"
elif [ $1 == "status" ]
then 
	ps -ef|grep $process
#ps -ef|grep phpresident.php|grep -v "grep"|sed "/phpresident.php/t;a not;"|grep -v "sed"|sed -n "a running"  
else
	usage;
fi

