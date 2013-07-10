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

但在安装阶段解压缩（C代码？）时，则因为各种原因（可能判断了重复文件就不覆盖？暂时没跟踪到源代码），只会解压缩出第一个（先期添加）路径对应的文件。

所以攻击者只要想办法，在zip包内的文件记录中，令修改版classes.dex排在原版classes.dex前面，即可同时绕过签名检查并且被优先取出使用，从而造成漏洞。


其他信息
======

目前已有人结合shell，利用python编辑zip的追加模式，写出了一个python版poc[7]。

（2013-07-10 14:13更新）根据参考[7]，有人写出了更容易使用的Scala版利用[9]。

此处结合ANDROID_8219321已知公开的互联网信息，存档其中的重点步骤：如何构造一个带有重名路径重名文件的zip包。java部分来自参考[4]，只是个demo，python部分来自参考[7]，可以实际使用。

另外有关android绕过签名的方法，国内还有另一种绕过方式，不过只能针对/system/app。[8]


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