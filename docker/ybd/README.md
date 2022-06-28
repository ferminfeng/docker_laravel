### 一、简介
    nginx version: openresty/1.15.8.2
    php 7.4
    mysql 5.7
    redis 5.0
    mongo
    rabbmit
    jenkins
    
### 二、目录及文件说明（必读）：
```
[root@localhost]# tree
.
├── data                        # 数据目录
│   ├── mongo
│   ├── mysql57
│   └── redis
├── etc                         # 配置目录
│   ├── mysql57                 # mysql 配置目录
│   │   └── my.cnf              # mysql 配置文件
│   ├── nginx
│   │   └── conf.d              # nginx 配置目录
│   │       ├── ext
│   │       │   └── header.conf # header 通用配置
│   │       ├── nginx.conf      # nginx 公共配置
│   │       └── vhost           # 虚拟主机配置（必须修改的配置），已放置1个参考实例配置，仅供参考：
│   │           └── ybd.conf    # 站点配置文件，实际使用需要将ybd.conf.template改成实际域名。
│   ├── php
│   │   ├── dockerfile          # php-fpm 镜像编译配置
│   │   │   └── 74
│   │   │       ├── Dockerfile
│   │   ├── php-fpm.conf        # php-fpm 配置
│   │   └── php.ini             # php 配置
│   └── redis
│       └── redis.conf
│   ├── nginx_php
│   │   ├── nginx
│   │   │   ├── conf.d
│   │   │   │   ├── default.conf
│   │   │   │   ├── upload-limit.conf
│   │   │   │   └── ybd.conf.template
│   │   │   └── nginx.conf
│   │   ├── php
│   │   │   ├── cli
│   │   │   │   ├── conf.d
│   │   │   │   └── php.ini
│   │   │   └── fpm
│   │   │       ├── conf.d
│   │   │       ├── php-fpm.conf
│   │   │       └── php.ini
│   │   └── supervisor
│   │       ├── supervisor.d
│   │       │   └── ybd.ini.template
│   │       └── supervisord.conf
└── log                         日志目录
    ├── mysql
    │   ├── mysql_error.log
    │   └── mysql_query.log
    │   └── mysql_slow.log
    ├── nginx
    │   ├── access.log
    │   └── error.log
    ├── php-fpm
    │   ├── fpm_access.log
    │   ├── fpm_error.log
    │   └── fpm_slow.log
    └── redis
        └── redis.log
├── docker-compose.mac.yml.template          # docker 编排配置
├── docker-compose.win.yml.template          # docker 编排配置
├── clear.bat                   # 清除日志文件、缓存等
├── clear.sh                    # 清除日志文件、缓存等
├── init.bat                    # 初始化并启动的脚本
├── init.sh                     # 初始化并启动的脚本
├── README.md
```


### 三、配置网站

按照系统类型选择 docker-compose.mac.yml 或者 docker-compose.win.yml 文件 改为 docker-compose.yml,并更改nginx、php挂载的项目路径为自己真实项目路径
例如自己项目根目录在 F:/abc/def 下
则把【- D:/www:/var/www】 改为 【- F:/abc/def:/var/www】

默认已经自带虚拟主机配置：`ypd.conf`。然后参考这个配置文件来定制自己网站的配置文件。若看不懂这个配置文件，可以直接拷贝网站原来的`vhost`配置文件也可以。


### 四、创建默认文件
    

## 附录
### 非centos环境使用参考

1、安装docker，参考：https://docs.docker.com/install/

2、安装 docker-compose，参考：https://docs.docker.com/compose/install/

Ps：此处提供linux通用安装命令：
```
curl -L "https://github.com/docker/compose/releases/download/1.23.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/bin/docker-compose
chmod +x /usr/bin/docker-compose
```
3、启动domp
```
docker-compose up -d
```
