[draft]sinaapp_generate_204：使用SAE（Sina App Engine，新浪云平台）生成HTTP 204返回码
======

## 说明

使用SAE（Sina App Engine，新浪云平台）生成HTTP 204返回码（generate_204），以解决Nexus 5等网络连接处出现感叹号问题。

如果不想自建sae应用，请直接从第4部开始看。

## 使用方法

1、在sae新建一PHP应用；如果有请忽略此步骤。

2、创建一个版本。此处以创建50版本（50.horseluke.sinaapp.com）为例子。

3、将upload下的文件全部上传到该版本代码库。

4、到adb执行su（也有说不用的，自己试试吧），然后再执行如下命令（如自建sae应用，请自行替换地址）：

<pre>
settings put global captive_portal_server 50.horseluke.sinaapp.com
</pre>

5、断开所有网络连接，再重连，感叹号消失

