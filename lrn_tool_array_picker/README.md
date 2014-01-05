[draft]lrn_tool_array_picker
======

<table class="" border="">
<tbody><tr>
<td><center><a href="http://www.phpclasses.org/" title="PHP Classes" alt="PHP Classes"><img src="http://files.phpclasses.org/graphics/phpclasses/logo-small-phpclasses.png" width="75" height="15" alt="PHP Classes" border="0"></a><br>
<hr>
<b><a href="http://www.phpclasses.org/package/8348-PHP-Filter-arrays-by-key-exclude-items-or-match-rules.html">Array Picker</a><br>
By <a href="http://www.phpclasses.org/browse/author/640834.html">Horse Luke</a></b><br>
<a href="http://www.phpclasses.org/award/innovation/"><img src="http://www.phpclasses.org/award/innovation/nominee.gif" width="89" height="89" alt="PHP Programming Innovation award nominee" title="PHP Programming Innovation award nominee" border="0"></a><br><font size="+1"><b>November&nbsp;2013<br>
Number 8</b></font></center></td>
</tr>
</tbody></table>


ENGLISH DESCRIPTION
======
Exclude or only return specific index value from an array(Supplement of function array_filter).

Goal: By unifying the filter rules, this class can be used in any array-data exchange place, such as api front-end custom data return, or function/method return data filter.

Acknowledge
======
@文刀_建州 （http://weibo.com/u/1735154004 ）


Usage
======
This class provide three methods:

<pre>
    /**
     * [ENG]Simple method, only return specific index value from array
     * @param array $data 
     * [ENG]Input data
     * @param array|string $cond 
     * [ENG]Specific index string or array that would return. If it is string, use comma delimiter between index
     * @param mixed $not_found_def 
     * [ENG]What will do if index not exist in array? default is self::IGNORE
     * @return mixed|array
     */
    public function by_index($data, $cond, $not_found_def = self::IGNORE){}
</pre>

<pre>
    /**
     * [ENG]Simple method, exclude specific index value from array
     * @param array $data 
     * [ENG]Input data
     * @param array|string $cond 
     * [ENG]Specific index string or array that would exclude. If it is string, use comma delimiter(,) between index
     * @param bool $cond_flipped 
     * [ENG]Param $cond has been processed with function array_flip? Default is false
     * @return mixed|array
     */
    public function exclude_index($data, $cond, $cond_flipped = false){}
</pre>

<pre>
    /**
     * [ENG]Exclude or return specific index value from array by rule
     * @param array $data 
     * [ENG]Input data
     * @param array|string $cond 
     * [ENG]rules array or string. If it is string, use and delimiter(&) between rule.
     * Format of each rule:
     * [path to array, delimited by "/". If next path is circular array or want to match any name of index, use "*" instead]/(_return|_return_exclude)=xx,xx,xx,xx,xx
     * @param mixed $not_found_def 
     * [ENG](only valid in _return)What will do if index not exist in array? default is self::IGNORE
     * @return mixed|array
     */
    public function by_rule($data, $cond, $not_found_def = self::IGNORE){}
</pre>

Example code can be found in file "example.php".


中文说明
======
提取或排除数组内的数据（array_filter的补充版）

目标：提供一个组件，能用在任意项目的数据交换处（比如api、函数或方法过滤返回），以统一的过滤规则、自由更改并返回定制数组的内容。


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
     * @param mixed $not_found_def 索引键不存在时如何处理？默认为self::IGNORE
     * @return mixed|array
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
2013/11/14 17:42 更新：

初始版本
