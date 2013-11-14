<?php

require dirname(__FILE__). '/lrn_tool_array_picker.php';

$data = array('s'=>1, 's2'=>2, 's3'=>3);
$lrn_tool_array_picker = new lrn_tool_array_picker();

//简单的获取某些索引
$result = $lrn_tool_array_picker->by_index($data, 's,s3');
var_export($result);

//简单的排除某些索引
$result2 = $lrn_tool_array_picker->exclude_index($data, 's,s3');
var_export($result2);

//根据某些规则获取某些索引值
/*
$conds为规则组合。如果不是数组，规则之间请使用半角&连接。每条规则方法如下：
    [数组路径，索引名之间使用/分割。如果是循环数组、或匹配任意数组索引键，请使用*]/(_return|_return_exclude)=xx,xx,xx,xx,xx
*/
$data3 = require dirname(__FILE__). '/raw_data.php';
$conds = array(
    '/_return=users,total_number',      //整个数组顶端仅保留users,total_number节点
    '/users/*/_return=idstr,screen_name,created_at,status',     //users节点下每个子数组，仅保留idstr,screen_name,created_at,status节点
    '/users/*/status/_return=idstr,created_at,text,source,pic_urls',     //users节点下、每个子数组中的status节点，仅保留idstr,created_at,text,source节点
    '/users/*/status/_return_exclude=source,pic_urls',     //users节点下、每个子数组中的status节点，去掉source,pic_urls节点
);
//$conds = implode('&', $conds);    //此步可省略

$time_pass = microtime(true);
$result3 = $lrn_tool_array_picker->by_rule($data3, $conds);

echo (microtime(true) - $time_pass) / 1;

var_export($result3);
