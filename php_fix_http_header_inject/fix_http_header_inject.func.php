<?php
/**
 * 修复http响应拆分漏洞（php < 5.4 ?）。暂时按照360网站安全检测的建议方案进行修正，虽然感觉strip_tags并非必须。
 * @link http://thread.gmane.org/gmane.comp.php.devel/70584
 * @link https://bugs.php.net/bug.php?id=60227
 * @author Horse Luke
 * @version 0.1 build 20131021
 */
function fix_http_header_inject($str){
    if(empty($str)){
	    return $str;
    }

    return trim(strip_tags(preg_replace('/( |\t|\r|\n|\')/', '', $str)));

}

//echo fix_http_header_inject(urldecode("asdfdas%0d%0a%20asdfdas%0d%0a%20asdfdas%0d%0a%20asdfdas%0d%0a%20"));