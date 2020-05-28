# 生产环境部署方案

由于新系统变化较大，且需要兼容旧系统。修改较为复杂，请按照本方案逐步操作

### 服务器文件结构

本节所述目录基准为`/home/wwwroot/`

汉购网商城代码部署目录为`/home/wwwroot/hangowa.com`，

相关子目录描述如下
```shell
v1/         旧版商城系统（ecStore）
v2/         新版商城系统（shopNC）
```

### 代码部署


1.  配置代码同步（使用sersync）

    + 修改客户机（101~104）
    ```shell
    mkdir -p /home/wwwroot/hangowa.com/v2/
    chown -R nobody:nobody /home/wwwroot/hangowa.com/v2/
    chmod -R 755 /home/wwwroot/hangowa.com/v2/
    vi /etc/rsyncd.conf
    #新增以下代码
    [hango2]
    path = /home/wwwroot/hangowa.com/v2/
    # 重启rsync服务
     ps aux | egrep "rsync"
     kill -9 pid
     rm /var/run/rsyncd.pid
     /usr/bin/rsync --daemon
    ```
    + 修改服务器
    ```shell
    cp doc/pro/sersync_hango2.xml /usr/local/sersync/conf/
    vi /etc/rc.d/rc.local #编辑，添加一行
    sersync -d -r -o  /usr/local/sersync/conf/sersync_hango2.xml
    :wq
    #运行
    sersync -d -r -o  /usr/local/sersync/conf/sersync_hango2.xml
    ```
    
2. 切换到部署目录，从git clone最新代码
```shell
cd /home/wwwroot
mkdir hangowa.com
cd hangowa.com
git clone git@192.168.12.113:hango2.git v2
```
3. 配置运行相关数据，具体配置参数查看相关开发文档
```shell
cd src
cp -rf sample/data/cache/* data/cache/
cp -rf sample/data/upload/* data/upload/
cp -rf sample/data/* data/
vi data/config/config.ini.php
cp fenxiao/Application/Common/Conf/config-sample.php fenxiao/Application/Common/Conf/config.php
vi fenxiao/Application/Common/Conf/config.php
cp migrate/Common/Conf/config-sample.php migrate/Common/Conf/config.php
vi migrate/Common/Conf/config.php
cp wap/js/config-sample.js wap/js/config.js
vi wap/js/config.js
```
4. 数据同步（配置nfs）
    + 安装nfs
    ```shell
    rpm -qa | grep nfs          #查看是否安装NFS组件
    rpm -qa | grep portmap      #查看是否安装portmap组件
    #若以上两个组件不存在则执行下面命令安装组件
    yum install nfs-utils rpcbind 
    
    mkdir -p /home/wwwroot/hangowa.com/v2/cache
    mkdir -p /home/wwwroot/hangowa.com/v2/upload 
    ln -s /home/wwwroot/hangowa.com/v2/cache /home/wwwroot/hangowa.com/v2/src/data/cache
    ln -s /home/wwwroot/hangowa.com/v2/upload /home/wwwroot/hangowa.com/v2/src/data/upload

    vi /etc/exports   
    /home/wwwroot/hangowa.com/v2/cache 192.168.12.*(rw,sync,root_squash,all_squash)
    /home/wwwroot/hangowa.com/v2/upload  192.168.12.*(rw,sync,root_squash,all_squash) #设置允许挂在的目录
    /etc/init.d/nfs restart  # 重启NFS服务   
    ```
    + 配置客户机
    ```shell
    showmount -e 192.168.12.113
     #创建挂在目录
    mkdir -p /home/wwwroot/hangowa.com/v2/data/cache  
    mkdir -p /home/wwwroot/hangowa.com/v2/data/upload  
    chown -R nobody:nobody /home/wwwroot/hangowa.com/v2/data/cache 
    chown -R nobody:nobody /home/wwwroot/hangowa.com/v2/data/upload
    chmod -R 777 /home/wwwroot/hangowa.com/v2/data/cache
    chmod -R 777 /home/wwwroot/hangowa.com/v2/data/upload
    mount -t nfs -o rw,soft,timeo=30,retry=20 192.168.12.113:/home/wwwroot/hangowa.com/v2/cache  /home/wwwroot/hangowa.com/v2/data/cache
    mount -t nfs -o rw,soft,timeo=30,retry=20 192.168.12.113:/home/wwwroot/hangowa.com/v2/upload   /home/wwwroot/hangowa.com/v2/data/upload

    #umount /home/wwwroot/hangowa.com/v2/data/cache
    #umount /home/wwwroot/hangowa.com/v2/data/upload
    ```
5. 配置nginx
```shell
#创建旧版本软连接
# 101~104
ln -s /home/wwwroot/default /home/wwwroot/hangowa.com/v2/v1 
# 105
ln -s /home/wwwroot/hango/www.hangowa.com /home/wwwroot/hangowa.com/v2/src/v1 
#创建配置文件软连接
# 注意修改web root目录、server id
#ln -s doc/prod/nginx/hango2.conf /usr/local/webserver/nginx/conf/vhost/hango2.conf 
cp doc/prod/nginx/hango2.conf /usr/local/webserver/nginx/conf/vhost/hango2.conf 
/etc/init.d/nginx reload
```


### 注意事项

+ 101~104服务器修改hosts，配置www.hangowa.com到本机（127.0.0.1）




### 挂载Windows备份

```shell
mount -o username=hango_backup,password=hango@123 //192.168.11.112/backup /home/backup
```