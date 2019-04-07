## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─app           		应用目录
│  ├─adm                后台核心目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  └─view            视图模板目录
│  │
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件/数据库配置文件
│  └─route.php          路由配置文件   
│
├─public                WEB目录（对外访问目录）
│  ├─static          	  css、js、图片等资源目录
│  ├─upload               用户上传图片等资源文件
│  └─index.php            入口文件
│
├─vendor                第三方类库目录（Composer依赖库）
├─extend                重定义扩展类库目录     
├─cache                 应用的缓存目录（可写，可定制）
├─tests                 单元测试程序目录
│  └─phpunit.xml           单元测试配制
│
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
└─ounun                 命令行入口文件
~~~

## 安装使用

1. 首先克隆下载应用项目仓库（或者直接下载最新[发布版本包](https://github.com/ounun-php/ounun)）
    
    ```bash
    git clone https://github.com/ounun-php/ounun.git
    ```
2. 然后切换到`tplay`目录下面，再使用`composer`自动安装更新依赖库

    ```bash
    composer install 
    ```
3. 将根目录下的`ounun_adm.sql`文件导入`mysql`数据库

    ```mysql
    mysql>source 你的(磁盘)路径/ounun_adm.sql
    ```
4. 修改项目`/app/database.php`文件中的数据库配置信息

5. 将你的域名指向根目录下的public目录（重要）,详情请看这里 [服务环境部署](#服务环境部署)

6. 浏览器访问：`http://adm.你的域名.com/`，默认管理员账户：`admin` 密码：`ounun`

7. 如果你用到了短信配置，请前往阿里大鱼官网申请下载自己的sdk文件，替换/extend/dayu下的文件，在后台配置自己的appkey即可

> 如遇问题可在 issues 交流。

## 服务环境部署 
####  Nginx 虚拟主机配置参考

```bash
server {
    listen 80;
    server_name 你的域名.com *.你的域名.com; # 这里修改为你的域名或者公网IP地址


    location / {
        if (!-e $request_filename) {
            rewrite (.*) /index.php last;
            break;
        }
    }
}
```
> 重新启动 Nginx 即可生效，浏览器输入地址：[https://www.你的域名.com/](https://www.你的域名.com/)

####  Apache 配置参考
在项目根目录加入.htaccess文件，只需开启rewrite模块
```bash
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php [QSA,PT,L]
</IfModule>
```
> 重新启动 Apache 即可生效

## 版权信息

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2018 by www.ounun.org (https://www.ounun.org)

All rights reserved。
