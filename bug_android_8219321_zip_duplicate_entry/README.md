[draft]bug_android_8219321_zip_duplicate_entry
======

漏洞成因
======

最近Bluebox爆出当前99%安卓设备存在一个巨大漏洞[1]，“造成的后果是，攻击者可以不需要更改Android应用程序的开发者签名便能对APK代码进行修改”。谷歌给各厂家修补的建议编号是ANDROID_8219321。[2]

根据CM讨论等消息[3][4]，造成此漏洞的原因是zip压缩包内允许存在多个同一个路径下的同名文件特性[5][6]，并且安卓在不同阶段、由于对zip包内文件路径的正向顺序读取后、处理会有不同的流程结果。

英文是这样说的：“When the Android operating system goes to verify the signature on the file, it examines the latter, original file and, as this is unchanged, will pass the archive as valid. But when the archive is actually used, it is the first, modified version of the file in the archive that is used.”

个人理解是，假设存在如下apk（apk本质是个zip包），并且存在重复的classes.dex：

<pre>

duplicate_entry_zip.apk
  |-classes.dex
  |-classes.dex
  |-duplicate_entry_dir
  |      |-duplicate_name_file.txt
  |      |-duplicate_name_file.txt
  |      |-...
  |-...

</pre>

此时保存在zip内文件路径的数据结构是这样的（为简化问题说明，暂以栈来表示）：

<pre>

 -----------------------------------------------------------
| 先期添加的修改版classes.dex | 后期添加的原版classes.dex   
 -----------------------------------------------------------

    &lt;- 正向顺序压栈、正向顺序读取（就是先进先出）  &lt;- 
 
</pre>


安卓在检查apk签名（java代码）的时候，会使用LinkedHashMap.put(ZipEntry.getName(), ZipEntry)保存zip包内的文件路径和入口（ZipEntry）。因此，按照文件的添加顺序正向读取时，如果出现重复路径，那么后一个（后期添加）会覆盖前一个（先期添加），签名检查也就成为检查最后一个（最后添加）路径对应的文件。

但在安装阶段解压缩（C代码）时，则因为查找zip包文件路径时，只要匹配第一个路径就返回结果（见参考[10]或者参考[11]，dexZipFindEntry函数中使用while-if-return），故导致最终，只会解压缩出第一个（先期添加）路径对应的文件。

所以攻击者只要想办法，在zip包内的文件记录中，令修改版classes.dex排在原版classes.dex前面，即可同时绕过签名检查并且被优先取出使用，从而造成漏洞。


POC和说明
======

目前已有人结合shell，利用python编辑zip的追加模式，写出了一个python版poc[7]。

（2013-07-10 14:13更新）根据参考[7]，有人写出了更容易使用的Scala版利用[9]。

这些POC，均要求将原来apk的所有文件都附加到修改apk内，这是因为这两个poc都假定攻击者使用apktool工具修改和生成修改apk，而apktool在重新打包时，会有可能修改res资源等文件内容，那么安卓在安装检查文件时，如果没有原版文件在前，会有可能出现签名校验失败，从而无法欺骗和安装（adb install -r会显示[INSTALL_PARSE_FAILED_NO_CERTIFICATES]，看logcat会找到类似“W/PackageParser: java.lang.SecurityException: META-INF/MANIFEST.MF has invalid digest for res/layout/xxx.xml in /data/app/xxx-xxxx.tmp”的信息）。

如果仅仅使用smali/baksmali修改classes.dex，那么不需要原来apk的所有文件、仅仅在classes.dex上做文章即可了，以下给出一种手动实践方法：

<pre>

（1）提取原版apk内的classes.dex，并复制一份为classes-ori.dex；
（2）用baksmali反编译classes.dex，然后修改其中内容；再用smail重新编译为修改版classes.dex
（3）删掉原版apk内的classes.dex，将修改版classes.dex添加到原版apk内
（4）用python的zip追加模式，将classes-ori.dex以“classes.dex”路径追加到原版apk
（5）搞定

</pre>


该github说明和其他
======

另外有关android绕过签名的方法，国内研究出来还有几种绕过方式：

    （1）针对/system/app内应用检查不严格的绕过，但需要root或者root漏洞，比较麻烦[8]。
	
    （2）针对java里short类型转int类型的情况、修改zip包的file header相关信息来达到和Bluebox一样的效果，不过有一定限制（@安卓安全小分队:“不是，是原始apk中的dex文件大小不能超过64K......该攻击方式只能攻击包含小于64K的dex文件的apk”），修复方案也和上述提到的漏洞不一样[12]。


此处结合ANDROID_8219321已知公开的互联网信息，存档其中的重点步骤：如何构造一个带有重名路径重名文件的zip包。java部分来自参考[4]，只是个demo，python部分来自参考[7]，可以实际使用。
	
	

参考
======

[1]http://bluebox.com/corporate-blog/bluebox-uncovers-android-master-key/

[2]http://www.pingwest.com/android-devices-vulnerable/

[3]http://www.h-online.com/open/news/item/Bluebox-s-Android-masterkey-hole-identified-1913097.html

[4]https://jira.cyanogenmod.org/browse/CYAN-1602

[5]http://stackoverflow.com/questions/3113556/when-creating-a-zip-archive-what-constitutes-a-duplicate-entry 

（请注意第一个答案追加的Edit和底下的评论）

[6]http://forums.gradle.org/gradle/topics/add_an_option_to_avoid_duplicate_entries_when_creating_a_zip_file

[7]https://gist.github.com/poliva/36b0795ab79ad6f14fd8

[8]http://weibo.com/1779382071/zEKr3fQwJ

[9]https://github.com/Fuzion24/AndroidMasterKeys

[10]http://weibo.com/1899360432/zFkanx99a

[11]http://www.kanxue.com/bbs/showthread.php?t=175129

[12]http://blog.sina.com.cn/s/blog_be6dacae0101bksm.html

