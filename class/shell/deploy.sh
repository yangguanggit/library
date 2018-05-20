#!/bin/bash

if [ -z "$GOPATH" ]; then
	source ~/.bash_profile
fi

# password for sudo
password='beego'
# program name
name='beego'
# nginx virtual host config file 
config="/etc/nginx/conf/vhost/www.$name.com.conf"
# log directory
log="/data/log/$name"
# program source file directory
src="$GOPATH/src/$name"
# program compile file
binary="$GOPATH/bin/$name"
# program log directory is a link file point to log directory
programLog="$src/log"
# program config file
programConfig="$src/conf/prod.app.conf"
port=0
port1=8080
port2=8090

# prepare something
function prepare() {
	echo "change work directory to $src"
	cd $src

	if [ ! -d $log ]; then
		echo "create directory $log"
		mkdir -pm 755 $log
	fi
	if [ ! -L $programLog ]; then
		echo "create link file $programLog -> $log"
		ln -s $log $programLog
	fi

	if [ ! -f $config ]; then
		echo "file $config not exist"
		exit 1
	fi
	port=`grep -io 'proxy_pass[ 	]\+https\?:[/.0-9]\+:[0-9]\+' $config | awk -F : '{print $3}'`
	if [ -z "$port" ]; then
		echo "file $config match proxy port empty"
		exit 2
	fi
	echo "nginx current proxy port is $port"

	if [ $port -eq $port1 ]; then
		port=$port2
	else
		port=$port1
	fi
	echo "nginx new proxy port is $port"

	pid=`lsof -i:$port | grep $name | awk '{print $2}'`
	if [ -n "$pid" ]; then
		echo "kill process $pid"
		kill -9 $pid
	fi

	sleep 1
}

# install program and run
function installProgram() {
	echo "update program listen port to $port"
	sed -i "s/\(httpPort[ =]\+\)[0-9]\+/\1$port/i" $programConfig
	echo 'install program'
	go install
	echo 'run program with nohup and backend'
	nohup $binary >> "$log/nohup.log" 2>&1 &

	sleep 1
}

# reload nginx
function reloadNginx() {
	echo "update nginx proxy port to $port"
	sed -i "s/\(proxy_pass[ 	]\+https\?:[/.0-9]\+:\)[0-9]\+/\1$port/i" $config
	echo 'reload nginx'
	echo $password | sudo -S systemctl reload nginx.service
}

echo ">>>>>>>>>> begin at $(date '+%Y-%m-%d %H:%M:%S') >>>>>>>>>>"

echo '1.prepare'
prepare
echo '2.install program'
installProgram
echo '3.reload nginx'
reloadNginx

echo "<<<<<<<<<< end   at $(date '+%Y-%m-%d %H:%M:%S') <<<<<<<<<<"
