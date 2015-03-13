Web服务器反向代理googleapis到国内useso.com的配置文件
======

## 注意

仅用于本地开发，且用于示例web服务器简单的反向代理使用方法。

## nginx目录使用方法 - 以CentOS为例

须确保/etc/nginx/nginx.conf存在如下类似包含代码：

<pre>
    # Load modular configuration files from the /etc/nginx/conf.d directory.
    # See http://nginx.org/en/docs/ngx_core_module.html#include
    # for more information.
    include /etc/nginx/conf.d/*.conf;
    
</pre>

然后将conf.d下的两个文件复制到文件夹/etc/nginx/conf.d/下，最后重启nginx service。

注意：由于仅用于本地开发，故conf中限制仅允许127.0.0.1的连接。

## ssl本地证书生成方法 - CentOS

<pre>
sudo openssl genrsa -des3 -out server.key 1024
(Enter pass phrase for server.key:xxxxx)

sudo openssl  req -new -key server.key -out server.csr

sudo openssl rsa -in server.key  -out server_nopass.key

sudo openssl x509 -req -days 3650 -in server.csr -signkey server_nopass.key -out server_nopass.crt
</pre>

注意：chrome不允许添加自签名证书例外；firefox可以。
