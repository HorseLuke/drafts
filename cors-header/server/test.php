<?php

require __DIR__. '/Class/CorsHeaderProcess.php';


//运行cros代码
$allowOriginList = array(
	'http://surl.sinaapp.com',
);

CorsHeaderProcess::detectSend($allowOriginList);



//业务逻辑测试

session_start();
$last_random = isset($_SESSION['test_random']) ? $_SESSION['test_random'] : 'NO_RANDOM_NUMBER_IN_SESSION';
$_SESSION['test_random'] = mt_rand(1, 1000);

header('Content-Type: application/json;charset=UTF-8');
echo json_encode(array('LAST_RANDOM_NUMBER_IN_SESSION' => $last_random, 'NEXT_RANDOM_NUMBER_IN_SESSION' => $_SESSION['test_random']));

