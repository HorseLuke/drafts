<?php

class CorsHeaderProcess{

	/**
	 * 检测并发送cros header头
	 * @param array $allowOriginList 允许跨域的origin头列表，每一个的格式一般为“http://域名”
	 */
    static public function detectSend(array $allowOriginList){
		
		//浏览器没有发送Origin头，则不是处在跨域状态中，此处忽略该流程
        if(!isset($_SERVER['HTTP_ORIGIN'])){
            return ;
        }
        
		//限定origin，不通过，则禁止访问
        if(!in_array($_SERVER['HTTP_ORIGIN'], $allowOriginList)){
			header('HTTP/1.1 403 Forbidden');
            exit();
        }
        
		//此处发送需要的CROS HEADER头：
		
		//检测通过则返回对应的originheader头
		//注意：Access-Control-Allow-Origin强烈不建议用*，会导致安全问题！而应该检测后直接返回原来的origin头
		header('Access-Control-Allow-Origin: '. $_SERVER['HTTP_ORIGIN']);
		
		//告诉浏览器，支持并且接受传递cookies等的登录状态
		//注意：Access-Control-Allow-Origin为*时，该设置对浏览器无效，此时浏览器报错，即使已经发送了cookies。
        header('Access-Control-Allow-Credentials: true');
		
		//支持ajax标识
		//如果需要支持html5跨域文件上传，请同时添加Content-Type, Content-Range, Content-Disposition, Content-Description
		//见：https://github.com/blueimp/jQuery-File-Upload/wiki/Cross-domain-uploads 中的“Cross-site XMLHttpRequest file uploads”
		header('Access-Control-Allow-Headers: X-Requested-With');
		
		//如果要支持其他METHOD操作，比如DELETE，请修改此处~
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		
		//如果要支持其他METHOD操作，比如DELETE，请修改此处~
		//警告：浏览器发出的OPTIONS操作应该像下面这样立刻终止运行，以防止浏览器由于使用OPTIONS检测CROS可用性，而导致重复请求，引发业务逻辑错误或脏数据
        if(!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'))){
            exit();
        }
        
	}

}


