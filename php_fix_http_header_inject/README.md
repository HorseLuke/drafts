[draft]php_fix_http_header_inject
======
http响应拆分漏洞（php <
5.4?）主要涉及header自定义输出的业务流中容易出问题——最容易出现问题的是header跳转（已测试），其次是cookies生成（未测试）。

影响版本似乎是在php 5.4以下的版本。根据参考链接中的Stefan Esser的历史剖析，PHP 5.1.2以下版本必定存在问题（%0d%0a，即[CR][LF]）；PHP 5.1.2 —— PHP 5.3.x可以绕过内建防御（只需要%0d换行即可在Chrome和IE上生效，有时要配合使用%20？）。

在360网站安全检测上这个漏洞权重较高，属高危（http://webscan.360.cn/vul/view/vulid/129 ）。但个人不是很认可，因为实际来看利用程度似乎没想象中高，而且演示链接“r=%0d%0a%20SomeCustomInjectedHeader%3Ainjected_by_wvs”（参数r用于header跳转中的Location字段），经过测试php 5.2+会过滤掉%0d%0a，只剩下空格但没有任何换行，那这样的话能否还能生效个人表示怀疑。

该网站还给出一个字符过滤修复的建议方案，但资源消耗比较大且不合理。这里主要是将它里面的多次正则直接合并为一个，并做成函数供开发按需调用过滤，而不是在所有GET/POST等输入参数都过滤——如果这样做的话好多存在换行的需求等业务会出现问题，比如写文章无法换行等。

参考链接（在此致谢）
======
    - @link http://thread.gmane.org/gmane.comp.php.devel/70584
    - @link https://bugs.php.net/bug.php?id=60227


语言
======
php


更新日志
======
2013/10/21 15:15 更新：

初始版本
