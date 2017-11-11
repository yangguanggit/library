#!/bin/sh
# 自动备份脚本
rsync -avzP --delete --progress --password-file=/backup/www.secrets root@116.228.195.187::www /backup/www