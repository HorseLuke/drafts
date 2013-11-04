<?php
/*
@author Horse Luke
@since 2013-11-04 17:30
@link http://zone.wooyun.org/content/7879

“滔滔心情将于2013年11月15日正式下线。还请提前备份，感谢大家长久以来的支持！更多精彩，请继续关注说说，返回【说说首页】”

taotao_save.php仅允许在php 5.2.5之后的cli模式下运行！

依赖库：

libxml、mbstring、curl

使用方法：

php.exe -f "[taotao_save.php文件实际路径]" -- --qqnumber="[QQ号码]" --cookies="[登录访问http://user.qzone.qq.com/后的cookies]"

（cookies可通过如下方法查看：chrome按F12调出开发者工具，然后选择Network，在登录状态下再刷新http://user.qzone.qq.com/，然后点击第一条Network记录，里面有一个Request Headers，见到cookies部分，直接全部复制即是]）

其他可用参数：

--page_start=1 ：从多少页开始抓起？默认从第1页开始抓
--page_max=1000 ：最多抓多少页？默认1000页
--set_time_limit=7200 ：最多抓取时间。默认7200秒。0表示不限制
--save_dir="" ：保存数据目录，如果为空，表示在taotao_save.php文件下自动创建一个保存目录


数据保存规则：

“rawdata_{qq号码}_{页数}.xml”：每页滔滔心情的原始数据，供进一步分析。
“result_data_{qq号码}.txt”：供普通用户阅读（human-readable）的滔滔心情。每一行分别为：
{滔滔心情id} {所在页码} {评论数} {发表时间} {内容}


*/


class project_common_config{
    
	protected $conf = array(
		'cookies' => '',    //登录qzone的cookies
		'qqnumber' => '',    //要抓取的qq号码
		'page_start' => '1',    //从多少页开始抓起？
		'page_max' => '1000',    //最多抓多少页？
		'set_time_limit' => 7200,    //最多抓取时间（7200）
		'save_dir' => '',    //保存数据目录，如果为空，表示在该运行文件下自动创建一个保存目录
		'url_pattern' => 'http://e.qzone.qq.com/cgi-bin/cgi_emotion_indexlist.cgi?uin=:qqnumber&emotionarchive=:page',
	);
	
	public function __construct(){
	}
	
	public function get($k, $def=null){
		return isset($this->conf[$k]) ? $this->conf[$k] : $def;
	}
	
	public function set($k, $v){
		$this->conf[$k] = $v;
		return $this;
	}

}


class project_common_env{

	static protected $instance = array();
	
	static public function init(){
		self::init_argv();
		date_default_timezone_set('PRC');
	}
	
	static public function get_instance($classname){
		if(isset(self::$instance[$classname])){
			return self::$instance[$classname];
		}
		
		self::$instance[$classname] = new $classname();
		return self::$instance[$classname];
		
	}
	
	static public function init_argv(){
		global $argv;
		$new_argv = array();
		foreach($argv as $a){
			preg_match('/^[-]+([a-z0-9_-]+)=(.*)$/i', $a, $match);
			if(isset($match[1]) && !is_numeric($match[1])){
				$new_argv[$match[1]] = $match[2];
			}
		}
		
		if(!empty($new_argv)){
			$argv = array_merge($argv, $new_argv);
		}
		
	}
	
}

class project_common_cli{

	static public function output($str, $rn = 1){
		echo date('Y-m-d H:i:s'). ': '. $str. str_repeat("\r\n", $rn);
	}
	
}

class project_taotao_env{
	
	static public function init(){
		global $argv;
		
		$conf = project_common_env::get_instance('project_common_config');
		
		$conf->set('cookies', isset($argv['cookies']) ? $argv['cookies'] : '');
		
		foreach(array('page_start', 'page_max', 'qqnumber', 'set_time_limit') as $argv_key){
			if(isset($argv[$argv_key]) && is_numeric($argv[$argv_key])){
				$conf->set($argv_key, $argv[$argv_key]);
			}
		}
		
		$save_dir = $conf->get('save_dir');
		if(!empty($argv['save_dir'])){
			$conf->set('save_dir', $argv['save_dir']);
			$save_dir = $argv['save_dir'];
		}elseif(empty($save_dir)){
			$save_dir = dirname(__FILE__). '/data_save_'. date('Y-m-d-H-i-s');
			$conf->set('save_dir', $save_dir);
		}
		
		if(!is_dir($save_dir)){
			$res = mkdir($save_dir, 0777, true);
			if(!$res){
				trigger_error('make result dir fail!'. $save_dir, E_USER_ERROR);
			}
		}

	}
}

class project_taotao_controller{
	
	protected $curl_handler = null;
	protected $conf = null;
	
	public function __construct(){
		$this->conf = project_common_env::get_instance('project_common_config');
		$this->_init_curl();
		libxml_disable_entity_loader(true);
	}
	
	protected function _init_curl(){
		$this->curl_handler = curl_init();
		
		$def_opt = array(CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HEADER => false,
							CURLOPT_TIMEOUT => 10,
							CURLOPT_USERAGENT => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'curl with no useragent'),
							CURLOPT_COOKIEFILE=>'',
		);
		
		foreach ($def_opt as $key => $value) {
			curl_setopt($this->curl_handler, $key, $value);
		}
		curl_setopt($this->curl_handler, CURLOPT_COOKIE, $this->conf->get('cookies'));
		
	}
	
	public function action_run(){
	
		$total_save_count = 0;
		
		$qqnumber = $this->conf->get('qqnumber');
		if(empty($qqnumber)){
			project_common_cli::output("[EXIT!]NO QQNUMBER!");
			exit();
		}
		$this->conf->set('taotao_save_file', $this->conf->get('save_dir'). '/result_data_'. $qqnumber. '.txt');
		
		$page_max = $this->conf->get('page_max');
		$page_max_in_fact = null;
		
		
		for($i=$this->conf->get('page_start'); $i<=$page_max; $i++){
			if(null !== $page_max_in_fact && $i > $page_max_in_fact){
				project_common_cli::output("[BREAK!]{$qqnumber} will not fetch page {$i}. Exceed page_max_in_fact:". $page_max_in_fact);
				break;
			}
			
			$data = $this->_fetch_taotao_data($qqnumber, $i);
			if(empty($data)){
				project_common_cli::output("[BREAK!]{$qqnumber} fetch page {$i} failure. Data Empty.");
				break;
			}
			
			$rawdata_save = $this->conf->get('save_dir'). '/rawdata_'. $qqnumber. '_'. $i. '.xml';
			file_put_contents($rawdata_save, $data);
			project_common_cli::output("raw data save to:". $rawdata_save);
			
			if(stripos($data, 'error') !== false){
				project_common_cli::output("[BREAK!]{$qqnumber} fetch page {$i} failure. Data Return Error:". $data);
				break;
			}
			
			//动态确定实际总页码
			if(null === $page_max_in_fact){
				preg_match('/<channel[ ]+archive="([0-9]+)"/i', $data, $page_match);
				if(isset($page_match[1])){
					$page_max_in_fact = $page_match[1];
					project_common_cli::output("[page_max_in_fact set]{$qqnumber} can fetch total pages:". $page_max_in_fact);
				}
			}
			
			
			$save_result = $this->_parse_xml_and_save($data, $qqnumber, $i);
			if($save_result < 0){
				project_common_cli::output("[BREAK!]{$qqnumber} fetch page {$i} failure. parse xml error(simplexml_load_string_error[-1] or xpath_empty_xml_result[-2]). Error_code:". $save_result);
				break;
			}
			
			project_common_cli::output("{$qqnumber} fetch page {$i} OK. Save taotao count:". $save_result);
			$total_save_count += $save_result;
			
		}
		
		project_common_cli::output("FININSH. total_save_count:". $total_save_count. ' IN '. $this->conf->get('taotao_save_file'));
		
	}
	
	
	
	protected function _fetch_taotao_data($qq, $page){
		$url = str_replace(array(':qqnumber', ':page'), array($qq, $page), $this->conf->get('url_pattern'));
		//project_common_cli::output($url);
		curl_setopt($this->curl_handler, CURLOPT_URL, $url);
		return curl_exec($this->curl_handler);
	}
	
	protected function _parse_xml_and_save($data, $qqnumber = 0, $page = 0){
		$xml = simplexml_load_string($data);
		if(!is_object($xml)){
			return -1;
		}
		
		$result = $xml->xpath('/rss/channel/item');
		if(empty($result)){
			return -2;
		}
		
		$save_data = array();
		foreach($result as $xml_block){
			$temp_row = array();
			$temp_row['id'] = (string)$xml_block->attributes()->id;
			$temp_row['page'] = $page;
			$temp_row['comment'] = (string)$xml_block->attributes()->comment;
			$temp_row['pubDate'] = trim((string)$xml_block->pubDate);
			$temp_row['title'] = trim((string)$xml_block->title);      //PHP自动完成编码转换？？？
			$save_data[$temp_row['id']] = implode("\t", $temp_row);
		}
		
		if(!empty($save_data)){
			ksort($save_data);
			file_put_contents($this->conf->get('taotao_save_file'), implode("\r\n", $save_data). "\r\n", FILE_APPEND);
			return count($save_data);
		}else{
			return 0;
		}
		
	}
	
	public function __destruct(){
		curl_close($this->curl_handler);
	}

}

project_common_env::init();
project_taotao_env::init();
project_common_env::get_instance('project_taotao_controller')->action_run();

