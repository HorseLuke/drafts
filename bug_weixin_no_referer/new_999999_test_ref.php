<?php

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '无';
$a = isset($_GET['a']) ? $_GET['a'] : '';

$counter = isset($_COOKIE['esf_counter']) ? intval($_COOKIE['esf_counter']) : 0;
$counter++;
setcookie('esf_counter', $counter);

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$currentFileName = pathinfo(__FILE__, PATHINFO_BASENAME);
$nextFileName = "test_ref.php";


$paramATest = array(
    "no_param" => '',
	"has_param_page_1" => '?a=page_1',
	"has_param_page_2" => '?a=page_2',
	"has_param_page_3" => '?a=page_3',
	"has_param_but_without_value" => '?a=',
	"has_param_rand" => "?a=page_rand_". md5('asfasd_'. mt_rand(). uniqid()),
);

?>


<!DOCTYPE html>
<html manifest="N404.manifest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,  width=device-width, initial-scale=1, minimum-scale=1, height=device-height" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <title><?=$currentFileName;?> - a - <?=htmlspecialchars($a);?></title>
</head>

<body>

<div class="container">

<h1>NOW IN <?=$currentFileName;?></h1>
<hr />
<h2><b>关键信息</b></h2>
<p>当前页面url：<b><?=htmlspecialchars($currentUrl);?></b></p>
<p>Referer: <b><?=htmlspecialchars($referer);?></b></p>

<h2>其他信息</h2>

<p>GET param a: <?=htmlspecialchars($a);?></p>
<p>访问次数: <?=htmlspecialchars($counter);?></p>
<p>访问ip: <?=htmlspecialchars($_SERVER['REMOTE_ADDR']);?></p>
<p>http头 X_FORWARDED_FOR ip（有此头且第一个ip和上面的ip不一致，则表示被腾讯的云加速代理）: <?=htmlspecialchars(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : "无");?></p>

<hr /><hr /><hr />


<h2>同域同文件<?=$currentFileName;?>测试</h2>

<?php foreach($paramATest as $paramATestName => $paramATestValue): ?>
	<a href="<?=($currentFileName.$paramATestValue);?>"><?=($currentFileName.$paramATestValue);?></a>&nbsp;(<?=$paramATestName;?>)<hr />
	
<?php endforeach; ?>

<hr /><hr /><hr />

<p>
同域同文件<?=$currentFileName;?>测试方法：<br />
1. 先访问index;<br />
2. 再访问page_1或者page_2或者page_3（此步骤可选）<br />
3. 然后访问page_rand_str<br />
4. 最后访问index<br />
5. 是不是很惊喜微信/手机QQ不给referer呢？（Chrome手机版测试正常）
</p>

<hr /><hr /><hr />

<h2>同域跨文件<?=$nextFileName;?>测试</h2>

<?php $paramATest['has_param_rand'] = "?a=page_next_file_rand_". md5('asfasd_'. mt_rand(). uniqid()); ?>
<?php foreach($paramATest as $paramATestName => $paramATestValue): ?>
	<a href="<?=($nextFileName.$paramATestValue);?>"><?=($nextFileName.$paramATestValue);?></a>&nbsp;(<?=$paramATestName;?>)<hr />
	
<?php endforeach; ?>

<hr /><hr /><hr />

<p>
微信/手机QQ的“QQ浏览器X5内核”居然还会有时候不给REFERER的Bug：<br />
如果微信/手机QQ在访问新的页面（url参数不同就是一个新页面）后，再返回url没有参数的页面（典型的是首页），就会不给Referer。
</p>

<p>last update: 2015-07-27</p>
<p>源代码: <a href="https://github.com/HorseLuke/drafts/tree/master/bug_weixin_no_referer">https://github.com/HorseLuke/drafts/tree/master/bug_weixin_no_referer</a></p>
</div>

</body>
</html>
