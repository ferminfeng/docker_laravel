#!/bin/bash
# 清空各种数据、缓存

# 日志
cat /dev/null > ./log/nginx/access.log
cat /dev/null > ./log/nginx/error.log

cat /dev/null > ./log/php74/fpm_access.log
cat /dev/null > ./log/php74/fpm_error.log
cat /dev/null > ./log/php74/fpm_slow.log

cat /dev/null > ./log/redis/redis.log

cat /dev/null > ./log/mysql57/mysql_error.log
cat /dev/null > ./log/mysql57/mysql_query.log
cat /dev/null > ./log/mysql57/mysql_slow.log

rm -rf ./log/supervisor/*.log

# 数据
# rm -rf ./data/mysql57/*

# rm -rf ./data/redis/*

# rm -rf ./data/mongo/*



