[draft]bug_android_8219321_zip_duplicate_entry
======

最近Bluebox爆出当前99%安卓设备存在一个巨大漏洞[1]，“造成的后果是，攻击者可以不需要更改Android应用程序的开发者签名便能对APK代码进行修改”。谷歌给各厂家修补的建议编号是ANDROID_8219321。[2]

根据CM讨论等消息[3][4]，造成此漏洞的原因是zip压缩包内允许存在多个同一个路径下的同名文件特性[5][6]，而安卓在不同阶段会对此有不同处理流程。

举例来讲，存在如下apk（apk本质是个zip包），并且存在重复的classes.dex：

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

安卓在遇到重复路径的同名文件，如果是检查apk签名，会检查后一个（最后一个？）；但运行代码时，则使用第一个。所以攻击者只要想办法，使得修改版classes.dex排在原版classes.dex的前面，即可绕过签名检查，从而造成漏洞。（“When the Android operating system goes to verify the signature on the file, it examines the latter, original file and, as this is unchanged, will pass the archive as valid. But when the archive is actually used, it is the first, modified version of the file in the archive that is used.”）

目前已有人结合shell，利用python编辑zip的追加模式，写出了一个poc[7]。

有关android绕过签名的方法，国内还有另一种绕过方式，不过只能针对/system/app。[8]

此处结合ANDROID_8219321已知公开的互联网信息，存档其中的重点步骤：如何构造一个带有重名路径重名文件的zip包。java部分来自参考[4]，只是个demo，python部分来自参考[7]，可以实际使用。

======

参考：

[1]http://bluebox.com/corporate-blog/bluebox-uncovers-android-master-key/

[2]http://www.pingwest.com/android-devices-vulnerable/

[3]http://www.h-online.com/open/news/item/Bluebox-s-Android-masterkey-hole-identified-1913097.html

[4]https://jira.cyanogenmod.org/browse/CYAN-1602

[5]http://stackoverflow.com/questions/3113556/when-creating-a-zip-archive-what-constitutes-a-duplicate-entry 

（请注意第一个答案追加的Edit和底下的评论）

[6]http://forums.gradle.org/gradle/topics/add_an_option_to_avoid_duplicate_entries_when_creating_a_zip_file

[7]https://gist.github.com/poliva/36b0795ab79ad6f14fd8

[8]http://weibo.com/1779382071/zEKr3fQwJ