[draft]sinaapp_generate_204：使用SAE（Sina App Engine，新浪云平台）生成HTTP 204返回码
======

## 说明

使用SAE（Sina App Engine，新浪云平台）生成HTTP 204返回码（generate_204），以解决Nexus 5等刷了Android 5.0之后、wifi等网络连接处出现感叹号问题。

如果不想自建sae应用，请直接从第4部开始看。

## 使用方法

1、在sae新建一PHP应用；如果有请忽略此步骤。

2、创建一个版本。此处以创建50版本（50.horseluke.sinaapp.com）为例子。

3、此处有两种方法：

*（方法1）使用文件夹upload，输出正规的HTTP 204 返回码*

修改upload/config.yaml，替换name和version，为自己的应用名和版本号；

<pre>
name: 你自己的应用名
version: 你自己的版本号
</pre>

然后将upload的全部文件上传到该版本代码库的根目录。

*（方法2）使用文件夹upload_http_response_200，输出HTTP 200 返回码且Content-Length为空*

如果不熟悉config.yaml、或者不想修改config.yaml，可以直接在根目录建立一个“generate\_204”的空文件即可。见文件夹upload_http_response_200。

但这样做对Android 4.x无效，只对Android 5.0及以上有效。具体见Android核心源代码中，services/core/java/com/android/server/connectivity/NetworkMonitor.java的代码变更：https://github.com/android/platform_frameworks_base/commit/e547ff281020b08eb51ef7b2786831f7aacdd73c


4、到adb执行如下命令（如自建sae应用，请自行替换地址）：

<pre>
settings put global captive_portal_server 50.horseluke.sinaapp.com
</pre>

（PS：根据朋友测试，目前暂时不用su）

（PPS：如果不想在电脑执行adb、或电脑没有安装adb，可以在手机安装Android Terminal Emulator，然后运行命令。下载地址：http://www.coolapk.com/apk/jackpal.androidterm ）

5、断开所有网络连接，再重连，感叹号消失

## 其它信息

有关该问题的分析、以及一键设置captive_portal_server apk工具（需root），请见小狐狸文章：https://xn--yet824cpd.xn--fiqs8s/45.html （Android 4.x新增isCaptivePortal()以判断wifi等移动网络连接状态原因）

本代码博客说明见：http://www.iirr.info/blog/?p=1544

isCaptivePortal()的代码更改历史，见Android核心源代码中，services/core/java/com/android/server/connectivity/NetworkMonitor.java的commit记录：https://github.com/android/platform_frameworks_base/commits/master/services/core/java/com/android/server/connectivity/NetworkMonitor.java

