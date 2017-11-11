#!/bin/bash 
#数据库自动备份脚本
#设置数据库名，数据库登录名，密码，备份路径，日志路径，数据文件位置以及备份方式
DBHost=127.0.0.1

DBName="test"

DBUser=root

DBPasswd=root

BackupPath=/root/dump/

LogFile=/root/dump/db.log

DBPath=/var/lib/mysql/

BackupMethod=mysqldump

#Setting End

NewFile="$BackupPath"alldb$(date +%y%m%d).tgz
DumpFile="$BackupPath"alldb$(date +%y%m%d)
OldFile="$BackupPath"alldb$(date +%y%m%d --date='100 days ago').tgz
echo "-------------------------------------------" >> $LogFile
echo $(date +"%y-%m-%d %H:%M:%S") >> $LogFile
echo "-------------------------------------------" >> $LogFile
#Delete Old File
if [ -f $OldFile ]
then
   rm -f $OldFile >> $LogFile 2>&1
   echo "[$OldFile]Delete Old File Success!" >> $LogFile
else
   echo "[$OldFile]No Old Backup File!" >> $LogFile
fi
if [ -f $NewFile ]
then
   echo "[$NewFile]The Backup File is exists,Can't Backup!" >> $LogFile
else
   case $BackupMethod in
   mysqldump)
		echo "use:" $BackupMethod >> $LogFile
      if [ -z $DBPasswd ]
      then
         mysqldump -u $DBUser --opt $DBName > $DumpFile
      else
         mysqldump -u $DBUser -p$DBPasswd -h$DBHost --all-databases > $DumpFile
      fi
      tar czvf $NewFile $DumpFile >> $LogFile 2>&1
      echo "[$NewFile]Backup Success!" >> $LogFile
      rm -rf $DumpFile
		;;
   mysqlhotcopy)
      rm -rf $DumpFile
      mkdir $DumpFile
      if [ -z $DBPasswd ]
      then
         mysqlhotcopy -u $DBUser $DBName $DumpFile >> $LogFile 2>&1
      else
         mysqlhotcopy -u $DBUser -p $DBPasswd $DBName $DumpFile >>$LogFile 2>&1
      fi
      tar czvf $NewFile $DumpFile >> $LogFile 2>&1
      echo "[$NewFile]Backup Success!" >> $LogFile
      rm -rf $DumpFile
		;;
   *)
      /etc/init.d/mysqld stop >/dev/null 2>&1
      tar czvf $NewFile $DBPath$DBName >> $LogFile 2>&1
      /etc/init.d/mysqld start >/dev/null 2>&1
      echo "[$NewFile]Backup Success!" >> $LogFile
   esac
fi
echo "-------------------------------------------" >> $LogFile