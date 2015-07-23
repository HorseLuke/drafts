bug_weixin_no_referer
======

微信/手机QQ的“QQ浏览器X5内核”居然还会有时候不给REFERER的Bug：

如果微信/手机QQ在访问新的页面（url参数不同就是一个新页面）后，再返回url没有参数的页面（典型的是首页），就会不给Referer。

注意：此问题出在同域。

在线地址：

http://horseluke.sinaapp.com/bug/weixin_no_referer/test_ref.php
