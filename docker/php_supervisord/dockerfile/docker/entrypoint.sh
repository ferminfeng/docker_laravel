#!/bin/bash
/etc/init.d/nginx start
/etc/init.d/php7.2-fpm start
/etc/init.d/supervisor start
/etc/init.d/redis-server start
/etc/init.d/cron start
/etc/init.d/ssh start
/bin/bash
