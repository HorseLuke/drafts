[draft]php_fix_http_header_inject
======
http响应拆分漏洞常出现于需要header输出的业务流中。此处仅讨论php < 5.4的情况下header Location跳转问题（示例见Bug#60227），不讨论setrawcookies是否有类似问题（反正我在php 5.2及以上版本会被php自身拦截）。

根据参考链接中的的历史剖析：

    - PHP 5.1.2以下版本必定存在问题。使用[CR][LF]（urlencode结果为%0d%0a）就可以构造CRLF攻击。Bug#60227是以5.1.2版本为分界的，此处采纳但本人未验证。
    - 5.1.2 <= php < 5.3.11、5.4.0 <= php < 5.4.1（redhat讨论给出的版本范围）可以绕过内建防御。Stefan Esser大牛说只需要[CR]（urlencode结果为%0d）换行即可在Chrome和IE上生效，有时要配合使用空格（urlencode结果为%20）？
	- 已经完全修复的版本是从5.3.11和5.4.1开始（redhat讨论给出的版本范围）？本人没确认。


在360网站安全检测上，这个漏洞权重较高，属高危（http://webscan.360.cn/vul/view/vulid/129 ）。但个人不是很认可，因为：

    - 实际来看和业务太紧密，大多不好利用，和“高危”的空间有差别。
	- 5.1.2 <= php < 5.3.11、5.4.0 <= php < 5.4.1（redhat讨论给出的版本范围）的内建防御虽然不完全，但已经不允许组合使用[CR][LF]（php会报错从而执行不下去）。只使用[CR]绕过虽然能控制CSP（Content Security Policy）Header选项或者设置cookies影响业务可用性，但试来试去就是做不到某些教程中的显示钓鱼内容或者执行脚本（http://www.javaarch.net/jiagoushi/847.htm ）——浏览器似乎直接Location跳转走了，即使为空也不显示页面内容，导致无法显示钓鱼内容或者执行脚本，结果预期攻击失败，达不到效果......


另外其演示链接“r=%0d%0a%20SomeCustomInjectedHeader%3Ainjected_by_wvs”（参数r用于header跳转中的Location字段），经过测试即使是不完全内建防御（比如php 5.2.x）也会过滤掉%0d%0a（就是[CR][LF]），只剩下空格，结果这行header实际上没有任何换行，那这种情况下是否还能攻击生效，个人表示怀疑（实测没发现成功）。

检测网站还给出一个字符过滤修复的建议方案，但资源消耗比较大且不合理。这里主要是将它里面的多次正则直接合并为一个，并做成函数供开发按需调用过滤，而不是在所有GET/POST等输入参数都过滤——如果这样做的话好多存在换行的需求等业务会出现问题，比如写文章无法换行等。

参考链接（在此致谢）
======
    - @link http://thread.gmane.org/gmane.comp.php.devel/70584
    - @link https://bugs.php.net/bug.php?id=60227
	- @link https://bugzilla.redhat.com/show_bug.cgi?id=854184


CRLF攻击另类情况和玩法？
======
    - @link http://comic.sjtu.edu.cn/bbs/view.asp?TID=4118 (在fopen(), file() 及其它函数；未测试)


语言
======
php


更新日志
======
2013/10/21 18:20 更新：

update README.md


2013/10/21 15:15 更新：

初始版本
