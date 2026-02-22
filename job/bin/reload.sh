#!/bin/bash
basepath=$(cd `dirname $0`; pwd)
cd $basepath

if [[ $1 = 'start' ]]; then
	if [ -f "../runtime/hyperf.pid" ]; then
		cat ../runtime/hyperf.pid | awk '{print $1}' | xargs kill && rm -rf ../runtime/hyperf.pid && rm -rf ../runtime/container
	fi
	lsof -i :9503 |grep -v PID| awk '{print "kill -9 "$2}'|sh
	php hyperf.php start

elif [[ $1 = 'stop' ]]; then
	if [ -f "../runtime/hyperf.pid" ]; then
		cat ../runtime/hyperf.pid | awk '{print $1}' | xargs kill && rm -rf ../runtime/hyperf.pid && rm -rf ../runtime/container
	fi
	lsof -i :9503 |grep -v PID| awk '{print "kill -9 "$2}'|sh

else
	echo "请输入stop|start"
fi

