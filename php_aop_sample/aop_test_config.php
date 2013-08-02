<?php
/**
  * 插件aop注册文件（当前仅能实现基于类实例的aop） v5版
  */
$aop = array();

/**
 * ===========切面（Aspect）注册数组==================
 * 此处用于注册切面（Aspect），一个切面一个数组信息
 */
$aop['aspect'] = array();

//一个切面（Aspect）注册示例
$aop['aspect']['test1'] = array(
	'name' => 'test1',    //name必须和key相同，暂时绑死
	'type' => 0,          //类型。不填或者不为1表示为放在app目录下的切面；为1表示在system目录下的切面
	'options' => array(),
);


/**
 * ===========切入点（Pointcut）注册数组==================
 * 此处用于注册切入点（Pointcut），其中有pointcut_before，pointcut_after和pointcut_around三种。
 * 索引class和method代表如下：
 *     - 如果全等"*"表示不区分
 *     - 前缀"e:"表示全等某个类/方法；
 *     - 前缀"s:"表示用strpos确定是否命中某个类/方法。注意：只有当strpos返回0时才表示命中
 *     - 前缀"p:"表示用preg_match确定是否命中某个类/方法
 *     - 如果是数组，数组内的名称表示全等其中之一即命中。（注意：此时不用前缀"e:"）
 */
$aop['pointcut_before'] = $aop['pointcut_after'] = $aop['pointcut_around'] = array();

$aop['pointcut_before'][] = array(
	'class' => '*',
	'method' => '*',
	'aspect' => 'test1',
	'advice_method' => 'do_before',
	'advice_param' => array(),
);

$aop['pointcut_before'][] = array(
	'class' => '*',
	'method' => '*',
	'aspect' => 'test1',
	'advice_method' => 'do_before2',
	'advice_param' => array(),
);


$aop['pointcut_before'][] = array(
	'class' => '*',
	'method' => '*',
	'aspect' => 'test1',
	'advice_method' => 'do_before3',
	'advice_param' => array(),
);

$aop['pointcut_after'][] = array(
		'class' => '*',
		'method' => '*',
		'aspect' => 'test1',
		'advice_method' => 'do_after',
		'advice_param' => array(),
);

$aop['pointcut_after'][] = array(
		'class' => '*',
		'method' => '*',
		'aspect' => 'test1',
		'advice_method' => 'do_after22',
		'advice_param' => array(),
);

$aop['pointcut_around'][] = array(
	'class' => 'e:hmvc_index_controller_index',
	'method' => 'e:index',
	'aspect' => 'test1',
	'advice_type' => 'around',
	'advice_method' => 'do_around',
	'advice_param' => array(),
);


$aop['pointcut_around'][] = array(
	'class' => 'e:hmvc_index_controller_index',
	'method' => 'e:index',
	'aspect' => 'test1',
	'advice_type' => 'around',
	'advice_method' => 'do_around_2',
	'advice_param' => array(),
);

$aop['pointcut_around'][] = array(
	'class' => '*',
	'method' => 'e:special_around_test',
	'aspect' => 'test1',
	'advice_type' => 'around',
	'advice_method' => 'do_around_special_test',
	'advice_param' => array(),
);

/**
 * ===========introduction通知（introuction advice）注册数组==================
 * 此处用于注册introduction通知（introuction advice）。
 * introduction通知是模拟spring中对java类实例强制转换时，注入一个新的类实例，以扩充此类的功能
 * 索引class代表如下：
 *     - 如果全等"*"表示不区分
 *     - 前缀"e:"表示全等某个类/方法；
 *     - 前缀"s:"表示用strpos确定是否命中某个类/方法。注意：只有当strpos返回0时才表示命中
 *     - 前缀"p:"表示用preg_match确定是否命中某个类/方法
 *     - 如果是数组，数组内的名称表示全等其中之一即命中。（注意：此时不用前缀"e:"）
 */
$aop['advice_introduction'] = array();

$aop['advice_introduction']['test1'] = array(
	'name' => 'test1',      //此处name和index必须全等
	'options' => array(),
	'class' => '*',
);

$aop['advice_introduction']['test3'] = array(
	'name' => 'test3',      //此处name和index必须全等
	'options' => array(),
	'class' => '*',
);




return $aop;
