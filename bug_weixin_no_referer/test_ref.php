<?php

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '无';
$a = isset($_GET['a']) ? $_GET['a'] : '';

$counter = isset($_COOKIE['esf_counter']) ? intval($_COOKIE['esf_counter']) : 0;
$counter++;
setcookie('esf_counter', $counter);
?>


<!DOCTYPE html>
<html manifest="N404.manifest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,  width=device-width, initial-scale=1, minimum-scale=1, height=device-height" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <title>TEST - a - <?=htmlspecialchars($a);?></title>
</head>

<body>

<div class="container">



<a href="test_ref.php">index</a><hr />
<a href="test_ref.php?a=ab/dv/ff">page_1</a><hr />
<a href="test_ref.php?a=dce/de/fs">page_2</a><hr />
<a href="test_ref.php?a=dce/de/fs">page_3</a><hr />
<a href="test_ref.php?a=rand/<?=md5('asfasd_'. mt_rand(). uniqid());?>">page_rand_str</a><hr />

<p>referer: <?=htmlspecialchars($referer);?></p>
<p>a: <?=htmlspecialchars($a);?></p>
<p>访问次数: <?=htmlspecialchars($counter);?></p>

<p>
测试方法：<br />
1. 先访问index;<br />
2. 再访问page_1或者page_2或者page_3（此步骤可选）<br />
3. 然后访问page_rand_str<br />
4. 最后访问index<br />
5. 是不是很惊喜微信不给referer呢？（Chrome手机版测试正常）
</p>

<p>
微信的“QQ浏览器X5内核”居然还会有时候不给REFERER的Bug：<br />
如果微信在访问新的页面（url参数不同就是一个新页面）后，再返回url没有参数的页面（典型的是首页），就会不给Referer。
</p>

<p>last update: 2015-07-22</p>
<p>源代码: <a href="https://github.com/HorseLuke/drafts/tree/master/bug_weixin_no_referer">https://github.com/HorseLuke/drafts/tree/master/bug_weixin_no_referer</a></p>
</div>

</body>
</html>
