[draft]tencent_taotao_save：备份早期滔滔心情的php cli脚本到本地
======

@马伯庸 今天在新浪微博中发出了这样的感慨（http://weibo.com/1444865141/Ah8BhFyBD ；2013-11-04 09:34）：

<pre>
我高中时的日记至今搁在家里，小时候拍的照片也还在。而我在98年那会儿浏览的网站和论坛已经消失得干干净净，个别论坛也许还在某个硬盘深处留有备份，但大部分帖子连备份都没有就彻底消失。几百年后，谁要想研究那段时期的网络史，恐怕史料会相当缺乏。网络到底是让痕迹保存更加容易呢还是相反？
</pre>

碰巧的是，就在14个小时前，ID为园长的用户也在乌云zone的帖子《批量抓取QQ空间5-8年前“消失了的QQ心情”》中，提到腾讯滔滔心情将要正式下线、数据将无法访问的通知（http://zone.wooyun.org/content/7879；2013-11-03 19:21）：

<pre>
滔滔心情将于2013年11月15日正式下线。还请提前备份，感谢大家长久以来的支持！更多精彩，请继续关注说说，返回【说说首页】
</pre>

两厢比较，感慨万千，故有这个备份早期滔滔心情的php cli脚本（其实是腾讯没有给在线全部备份滔滔心情的原因）。


语言
======
php（只能运行在php cli模式下）

依赖库：
libxml、mbstring、curl


使用方法
======
php cli访问之（必填参数--qqnumber和--cookies）：
<pre>
php.exe -f "[taotao_save.php文件实际路径]" -- --qqnumber="[QQ号码]" --cookies="[登录访问http://user.qzone.qq.com/后的cookies]"
</pre>

（cookies可通过如下方法查看：chrome按F12调出开发者工具，然后选择Network，在登录状态下再刷新http://user.qzone.qq.com/，然后点击第一条Network记录，里面有一个Request Headers，见到cookies部分，直接全部复制即是]）


其它可用参数：

<pre>
--page_start=1 ：从多少页开始抓起？默认从第1页开始抓
--page_max=1000 ：最多抓多少页？默认1000页
--set_time_limit=7200 ：最多抓取时间。默认7200秒。0表示不限制
--save_dir="" ：保存数据目录，如果为空，表示在taotao_save.php文件下自动创建一个保存目录
</pre>


数据保存规则：
<pre>
“rawdata_{qq号码}_{页数}.xml”：每页滔滔心情的原始数据，供进一步分析。
“result_data_{qq号码}.txt”：供普通用户阅读（human-readable）的滔滔心情。每一行分别为：
{滔滔心情id} {所在页码} {评论数} {发表时间} {内容}
</pre>


更新日志
======
2013/11/4 17:57 更新：

初始版本

