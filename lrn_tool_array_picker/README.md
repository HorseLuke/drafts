[draft]lrn_tool_array_picker
======
[php]提取或排除数组内的数据（array_filter的补充版）

目标：提供一个组件，能用在任意项目的数据交换处（比如api、函数或方法过滤返回），以统一的过滤规则、自由更改并返回数组返回的内容。


语言
======
php


致谢
======
@文刀_建州 （http://weibo.com/u/1735154004 ）


使用方法
======
提供了三种过滤方式：

<pre>
    /**
     * 简单的获取某些索引
     * @param array $data 要过滤的数组
     * @param array|string $cond 只保留的索引键。如果是字符串，每个索引键必须使用半角逗号（,）。如果为空，将返回空数组
     * @param mixed $not_found_def 索引键不存在时如何处理？
     */
    public function by_index($data, $cond, $not_found_def = self::IGNORE){}
</pre>

<pre>
    /**
     * 简单的排除某些索引
     * @param array $data 要过滤的数组
     * @param array|string $cond 要剔除的索引键。如果是字符串，每个索引键必须使用半角逗号（,）。如果为空，将原样返回不做处理
     * @param bool $cond_flipped $cond参数是否经过了预先array_flip？默认为false
     * @return mixed|array
     */
    public function exclude_index($data, $cond, $cond_flipped = false){}
</pre>

<pre>
	/**
     * 根据某些规则获取某些索引值
     * @param array $data 要过滤的数组
     * @param array|string $cond 规则组合。如果不是数组，规则之间请使用半角&连接。
     * 每条规则方法如下：
     * [数组路径，索引名之间使用/分割。如果是循环数组、或匹配任意数组索引键，请使用*]/(_return|_return_exclude)=xx,xx,xx,xx,xx
     * @param mixed $not_found_def (_return时才有效)索引键不存在时如何处理？
     * @return mixed|array
     */
    public function by_rule($data, $cond, $not_found_def = self::IGNORE){}
</pre>


示例
======

所有例子见example.php，此处摘录方法by_rule的规则示例：

<pre>
$conds = array(
    '/_return=users,total_number',      //整个数组顶端仅保留users,total_number节点
    '/users/*/_return=idstr,screen_name,created_at,status',     //users节点下每个子数组，仅保留idstr,screen_name,created_at,status节点
    '/users/*/status/_return=idstr,created_at,text,source,pic_urls',     //users节点下、每个子数组中的status节点，仅保留idstr,created_at,text,source节点
    '/users/*/status/_return_exclude=source,pic_urls',     //users节点下、每个子数组中的status节点，去掉source,pic_urls节点
);
//$conds = implode('&', $conds);    //此步可省略

$result3 = $lrn_tool_array_picker->by_rule(array(), $conds);
</pre>


更新日志
======
2013/11/14 17:28 更新：

初始版本
