<?php
/**
 * 一种采取数组模拟“指针移动”的php aop（Aspect Oriented Programming，面向切面）思路和demo
 * 当前比较多的php aop思路，是对调用的方法采取反射处理，并且使用装饰模式，通过对advice的递归调用链，来完成aop的流程
 * 此处提出一种不依赖反射和对象递归调用的方法，仅依赖数组索引，同时模拟“指针”移动来完成类似功能
 * 当然，这种方式可能没其它aop那么优雅，但不需要对数组多次变换和归并......
 * 
 * 该demo已经实现的aop功能有：
 *     - 基于before、after、around通知（Advice）的切面（Aspect）
 *     - 基于introduction通知的类“转换”
 *     
 * 该demo不能实现的aop功能有：
 *     - advice延迟运行（即在advice运行过程中，代码判断当前运行时机不适合，要求放到后面再一次运行；话说spring也没这么做啊...）
 *     - 动态添加pointcut（只能在配置中预定义）
 *     - 基于注释的pointcut（只能在配置中预定义。php中实现Tokenizer的话，代价有点大吧？！有些aop支持这功能，但需要缓存支持...）
 *     - advice中运行advice（确实有人提出这需求，但这个比较容易搞混advice的单一职责吧？！）
 * 
 * 该demo不建议的aop功能：
 *      - advice中调用lrn_aop_container容器内的方法（虽然许多情况下不能避免，但不建议调用过多，否则容易死循环...）
 * 
 * @author Horse Luke
 * @link http://www.linuxidc.com/Linux/2012-03/56516.htm
 * @link http://pandonix.iteye.com/blog/336873
 * @link http://javacrazyer.iteye.com/blog/794035
 * @link http://cloudbbs.org/forum.php?mod=viewthread&tid=7820
 */
error_reporting(E_ALL);

class lrn_aop_config{
	
	static protected $aop_config = null;
	
	static public function get($key = '', $default = null){
	
		if(!is_array(self::$aop_config)){
			self::$aop_config = require dirname(__FILE__). '/aop_test_config.php';
		}
		
		if(empty($key)){
			return self::$aop_config;
		}elseif(strpos($key, '>') !== false){
			$key = explode('>', $key);
			$val = self::$aop_config;
			
			foreach($key as $k){
				if(is_array($val) && isset($val[$k])){
					$val = $val[$k];
				}else{
					$val = $default;
					break;
				}
			}
			
			return $val;
		}else{
			return isset(self::$aop_config[$key]) ? self::$aop_config[$key] : $default;
		}
		
	}

}


class lrn_aop{

	/**
	 * 已经载入的aspect实例树。如果没有，则对应为false
	 * @var array
	 */
	static protected $load_aspect = array();
	
	static protected $config_classname = array();
	
	static protected $_counter_protected = 0;
	
	/**
	 * 当前已经实现的pointcut（用于定义Advice的应用时机；即定义jointpoint）
	 * 暂时不支持throw exception
	 * @var array
	 */	
	static protected $pointcut_config_index = array('pointcut_before', 'pointcut_after', 'pointcut_around');
	
	/**
	 * 载入某个class的aop pointcut配置，并返回结果
	 * @param string $classname 类名称
	 * @return array|bool 如果该类名有aop pointcut配置，返回一个非空数组，否则返回false
	 */
	static public function load_config($classname){
		if(isset(self::$config_classname[$classname])){
			return self::$config_classname[$classname];
		}
		
		$new_config = array();
		foreach(self::$pointcut_config_index as $pointcut){
			$cfg = lrn_aop_config::get($pointcut);
			if(empty($cfg)){
				continue;
			}
			foreach($cfg as $key => $row){
				if(!self::conf_str_match($classname, $row['class'])){
					continue;
				}
				
				$new_config[$pointcut][] = $row;
				
			}
		}
		
		if(empty($new_config)){
			self::$config_classname[$classname] = false;
			return false;
		}
		
		self::$config_classname[$classname] = $new_config;
		return $new_config;
		
	}
	
	/**
	 * 根据类名，返回一个aop运行容器实例
	 * 如果该类名没有aop可支持，则返回原始对象
	 * @param string $classname 类名
	 * @return object
	 */
	static public function getInstance($classname){
		$aop_config = self::load_config($classname);
		if(empty($aop_config)){
			return new $classname();
		}
		
		return new lrn_aop_container(new $classname(), $aop_config);
	}
	
	/**
	 * 载入切面（Aspect）实例，如果不成功，返回false
	 * @param string $aspect 切面（Aspect）名称
	 * @param bool $return_instance 返回对象？默认否，只是返回载入成功与否
	 * @return bool|object
	 */
	static public function load_aspect($aspect, $return_instance = false){
		if(empty($aspect)){
			return false;
		}
		
		if(isset(self::$load_aspect[$aspect])){
			return $return_instance ? self::$load_aspect[$aspect] : is_object(self::$load_aspect[$aspect]);
		}
		
		$aspect_classname = 'plugin_aop_aspect_'. $aspect;
		
		if(!class_exists($aspect_classname)){
			self::$load_aspect[$aspect] = false;
			return false;			
		}
		
		self::$load_aspect[$aspect] = new $aspect_classname(isset($conf['options']) ? $conf['options'] : null);
		
		return $return_instance ? self::$load_aspect[$aspect] : is_object(self::$load_aspect[$aspect]);
		
		
	}
	
	static public function conf_str_match($name, $conf_str){
		
		if(is_array($conf_str)){
			if(in_array($name, $conf_str)){
				return true;
			}
			return false;
		}
		
		switch($conf_str{0}){
			case '*':
				if('*' === $conf_str){
					return true;
				}
				return false;
			case 'e':
				if('e:'. $name === $conf_str){
					return true;
				}
				return false;
			case 's':
				if(0 === strops('s:'. $name, $conf_str)){
					return true;
				}
				return false;
			case 'p':
				if(preg_match(substr($string, 2), $name)){
					return true;
				}
				return false;
			default:
				return false;
		}
		
	}
	
	/**
	 * 防止aop之间的死循环调用（一般出现在执行通知（Advice）中调用其它aop容器实例方法）
	 * 当前假定aop之间循环调用极少，且pointcut_around极少，限定在15
	 * 仅供aop内部通讯调用，请勿在外部使用
	 * @param int type counter通讯类型
	 */
	static public function _counter_protecte($type = 1){
		
		if($type == 0){
			if(--self::$_counter_protected >= 15){
				trigger_error('AOP LOOP DETECTED, SYSTEM HALTED', E_USER_ERROR);
			}
		}else{
			if(++self::$_counter_protected >= 15){
				trigger_error('AOP LOOP DETECTED, SYSTEM HALTED', E_USER_ERROR);
			}
		}
		
		//$_counter_protected = self::$_counter_protected;
		//echo "\r\n<<=================|{$type} -> {$_counter_protected}|====================>>\r\n";	
		
	}
	
}

//========================================================

/**
 * aop之切面（Aspect）抽象类；所有切面均需要继承此抽象类，并且前缀必须为plugin_aop_aspect_
 * 一个切面下面可以编写多个通知（Advice）
 *
 */
class lrn_aop_abstract_aspect{

	
	const FORCE_RETURN_RESULT = '__FALSE_FORCE_RETURN_RESULT_233333';
	
	/**
	 * 如果没有编写对应的通知，是否静默pass？
	 * 默认为否，此时将抛出fatal error
	 * @var bool
	 */
	protected $silent_method = false;
	
	protected $options = null;
	
	public function __construct($options = null){
		$this->options = $options;
	}
	
	public function __call($name, $param){
		if($silent_method){
			return null;
		}
		
		trigger_error('ASPECT METHOD:'. __CLASS__. '->'. $name. ' NOT EXISTS', E_USER_ERROR);
		
	}
	
}

/**
 * 特殊通知（Advice）之introduction通知（introduction advice）运行器
 * introduction通知的运用见lrn_aop_abstract_advice_introduction的注释
 */
class lrn_aop_advice_introduction{

	protected static $loaded = array();
	
	protected static $config = array();
	
	protected static $spl_obj_hash_tree = array();
	
	/**
	 * 获取某对象指定的introduction通知
	 * 如果获取失败且$halt为false，则返回原对象，否则fatal error（默认）
	 * @param string $name introduction通知名称
	 * @param object $object 要获取的对象
	 * @param bool $halt 如果获取失败且$halt为false，是否fatal error？默认true
	 * @return object
	 */
	static public function get($name, $object, $halt = true){
		if($object instanceof lrn_aop_abstract_advice_introduction){
			$halt && trigger_error('can not reget in lrn_aop_abstract_advice_introduction. param name:'. $name, E_USER_ERROR);
			return $object;
		}
		
		if($object instanceof lrn_aop_container){
			$instance_aop = true;
			$spl_hash = '';
		}else{
			$instance_aop = false;
			$spl_hash = spl_object_hash($object);
		}
		
		if($instance_aop || isset(self::$spl_obj_hash_tree[$spl_hash][$name])){
			$advice_obj = $instance_aop ? $object->___aop_advice_introduction_get($name) : self::$spl_obj_hash_tree[$spl_hash][$name];
			
			if(null === $advice_obj){
				$halt && trigger_error('can not found advice_introduction '. $name, E_USER_ERROR);
				return $object;
			}elseif(is_object($advice_obj)){
				return $advice_obj;
			}
		}
		
		$classname = $instance_aop ? $object->___aop_origin_get_name() : get_class($object);
		
		$advice_load = self::load($name);
		$advice_cfg = $advice_load ? self::load_config($classname) : false;
		//var_export(array('%%%%%%%%%%%%%%%', $name, $advice_load, $classname, $advice_cfg));
		if(!$advice_load || empty($advice_cfg)){
			if($instance_aop){
				$object->___aop_advice_introduction_inject($name, null);
			}else{
				self::$spl_obj_hash_tree[$spl_hash][$name] = null;
			}
			
			$halt && trigger_error('can not found advice_introduction: '. $name, E_USER_ERROR);
			return $object;
		}
		
		$advice_obj_classname = 'plugin_aop_advice_introduction_'. $name;
		$advice_obj = new $advice_obj_classname($object, isset($advice_cfg['options']) ? $advice_cfg['options'] : null);
		if($instance_aop){
			$object->___aop_advice_introduction_inject($name, $advice_obj);
		}else{
			self::$spl_obj_hash_tree[$spl_hash][$name] = $advice_obj;
		}
		
		return $advice_obj;
		
	}
	

	static public function load($name){
		if(empty($name)){
			return false;
		}
		
		if(isset(self::$loaded[$name])){
			return self::$loaded[$name];
		}
		
		$classname = 'plugin_aop_advice_introduction_'. $name;
		
		if(!class_exists($classname)){
			self::$loaded[$name] = false;
			return false;
		}
		
		self::$loaded[$name] = true;
		return true;
		
	}	
	
	static public function load_config($name){
		
		if(isset(self::$config[$name])){
			return self::$config[$name];
		}

		$advice_introduction = lrn_aop_config::get('advice_introduction');
		$new_config = array();
		if(!empty($advice_introduction)){
			foreach($advice_introduction as $row){
				if(!lrn_aop::conf_str_match($name, $row['class'])){
					continue;
				}
				
				$new_config[$row['name']] = $row;
			}
		}
		
		if(empty($new_config)){
			self::$config[$name] = false;
			return false;
		}
		self::$config[$name] = $new_config;
		
		return $new_config;
		
	}
	
}

/**
 * introduction通知（introduction advice）抽象类
 * 所有introduction通知均需要继承此类，并且前缀必须为plugin_aop_advice_introduction_
 * introduction通知（introduction advice）属于一种特殊的通知，只能针对类。
 * 运用时机是：不想修改类的代码，但需要添加新的方法或者改变某些流程，以增强功能或者实现其他效果
 * spring似乎是通过拦截java类型强制转换的时候来完成的，比如如下代码：
 *    User u1 = new User();
 *    IAdvice a1 = (IAdvice)u1;
 *    
 * php没有这个能力，所以在此处，只能采取如下方法解决：
 *     lrn_aop_advice_introduction::get('introduction通知名称', '待转换的类');
 */
class lrn_aop_abstract_advice_introduction{
	
	/**
	 * @var lrn_aop_container
	 */
	protected $instance = null;
	
	protected $options = null;
	
	public function __construct($i, $options){
		$this->instance = $i;
		$this->options = $options;
	}
	
	public function __call($name, $param){
		if($this->instance instanceof lrn_aop_container){
			return $this->instance->__call($name, $param);
		}
		
		$param_count = 0;
		if(!empty($param)){
			$param_count = count($param);
		}
		
		switch($param_count){
			case 0:
				return $this->instance->$name();
				break;
			case 1:
				return $this->instance->$name($param[0]);
				break;
			case 2:
				return $this->instance->$name($param[0], $param[1]);
				break;
			case 3:
				return $this->instance->$name($param[0], $param[1], $param[2]);
				break;
			case 4:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3]);
				break;
			case 5:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3], $param[4]);
				break;
			case 6:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3], $param[4], $param[5]);
				break;
			default:
				call_user_func_array(array($this->instance, $name), $param);
				break;
		}
				
		
	}
	
}



//========================================================
/**
 * aop运行容器实例
 * @author horseluke
 */
class lrn_aop_container{

	/**
	 * 原始对象
	 * @var object
	 */
	protected $instance = null;
	

	/**
	 * 原始对象名称
	 * @var string
	 */	
	protected $instance_name = null;
	
	/**
	 * 该类的aop aspect配置
	 * @var array
	 */
	protected $aop_config = array();	
	
	/**
	 * 指定$aop_call_counter下的对应指针stack，以模拟指针的方式，记录pointcut_around走到哪里
	 * lrn_aop_container::$aop_call_stack
	 * @var array
	 */	
	protected $aop_call_stack = array();
	
	/**
	 * 指定$aop_call_counter下的对应数据栈
	 * 运行完一个$aop_call_counter时，对应数据栈内的数据就销毁
	 * @var array
	 */
	protected $aop_data_stack = array();
	
	/**
	 * 最后运行的方法名，防止aop运行时的死循环（loop）
	 * @var string
	 */	
	protected $aop_last_call_name = '';
	
	/**
	 * 当前运行__call的计数，从此处获取到的$aop_call_counter必须贯穿到诸如整个___aop_process中
	 * @var int
	 */	
	protected $aop_call_counter = 0;
	
	/**
	 * aop_adivce_introduction_stack对象树，不存在将返回null
	 * @array
	 */
	protected $aop_adivce_introduction_stack = array();
	
	public function __construct($i, $aop_config){
		$this->instance = $i;
		$this->instance_name = get_class($i);
		$this->aop_config = $aop_config;
	}
	
	/**
	 * 运行一次aop advice的起始点
	 * @param name $name
	 * @param array|null $param
	 */
	public function __call($name, $param){
	
		//防止死循环aop
		if($name == $this->aop_last_call_name){
			trigger_error('nested call found in lrn_aop_container with origin method '. $this->instance_name. '::'. $name, E_USER_ERROR);
		}
		
		lrn_aop::_counter_protecte(1);
		
		$this->aop_last_call_name = $name;
		
		$aop_call_counter = ++$this->aop_call_counter;
		$this->aop_call_stack[$aop_call_counter] = 0;
		$this->aop_data_stack[$aop_call_counter] = array(
			'name' => $name,
			'param' => $param,
			'result' => null,
		);
		$this->___aop_process($aop_call_counter);
		$return = $this->aop_data_stack[$aop_call_counter]['result'];
		
		unset($this->aop_data_stack[$aop_call_counter]);
		$this->aop_last_call_name = '';
		lrn_aop::_counter_protecte(0);
		
		//echo "\r\n{$name} - {$aop_call_counter}\r\n";
		
		return $return;
	}
	
	
	/**
	 * 循环运行aop advice的地方
	 * @param int $aop_call_counter 当前循环运行aop advice的“指针”
	 * @return null|string 
	 * 如果一个advice返回lrn_aop_abstract_aspect::FORCE_RETURN_RESULT，将终结剩余的advice运行（除了已经运行的around advice）
	 * 
	 */	
	public function ___aop_process($aop_call_counter){
		
		/*
		echo "\r\n>>>X?X=============={$aop_call_counter}=========\r\n";
		var_export($this->aop_call_stack[$aop_call_counter]);
		echo "\r\n\r\n";
		*/
		
		if(!isset($this->aop_call_stack[$aop_call_counter])){
			trigger_error('aop_call_counter param error!', E_USER_ERROR);
		}
		
		if($this->aop_call_stack[$aop_call_counter] < 0){
			return lrn_aop_abstract_aspect::FORCE_RETURN_RESULT;
		}
		


		//echo "\r\n>>>X?X==============/xd1/=========\r\n";
				
				
		
		//around通知运行区，以模拟指针方式运行；如果指到不存在的地方，那就是没有around通知要运行了
		if(isset($this->aop_config['pointcut_around'][$this->aop_call_stack[$aop_call_counter]])){
			$pointcut_cfg = $this->aop_config['pointcut_around'][$this->aop_call_stack[$aop_call_counter]];
			$this->aop_call_stack[$aop_call_counter]++;
			
			//echo "\r\n>>>X?X==============/xd2/=========\r\n";
			//var_export($pointcut_cfg);
			
			//运行around通知，如果这个around通知无效，就令其继续运行___aop_process
			return $this->___aop_process_run_advice($pointcut_cfg, $aop_call_counter, true);
			
		}
		

		//echo "\r\n>>>X?X==============/xd3/=========\r\n";
				
		
		//before通知和after通知运行区
		//运行到这里，around通知什么的都不需要再运行了，重置指针到不存在的地方
		$this->aop_call_stack[$aop_call_counter] = -1;
		if(!empty($this->aop_config['pointcut_before'])){
			foreach($this->aop_config['pointcut_before'] as $pointcut_cfg){
				$advice_result = $this->___aop_process_run_advice($pointcut_cfg, $aop_call_counter);
				if(lrn_aop_abstract_aspect::FORCE_RETURN_RESULT === $advice_result){
					return lrn_aop_abstract_aspect::FORCE_RETURN_RESULT;
				}
			}
		}
		
		$this->aop_data_stack[$aop_call_counter]['result'] = $this->___aop_call_origin($this->aop_data_stack[$aop_call_counter]['name'], $this->aop_data_stack[$aop_call_counter]['param']);
		
		if(!empty($this->aop_config['pointcut_after'])){
			foreach($this->aop_config['pointcut_after'] as $pointcut_cfg){
				$advice_result = $this->___aop_process_run_advice($pointcut_cfg, $aop_call_counter);
				if(lrn_aop_abstract_aspect::FORCE_RETURN_RESULT === $advice_result){
					return lrn_aop_abstract_aspect::FORCE_RETURN_RESULT;
				}
			}
		}
		
	}
	
	protected function ___aop_process_run_advice($pointcut_cfg, $aop_call_counter, $forward_aop_process = false){
		if(!lrn_aop::conf_str_match($this->aop_data_stack[$aop_call_counter]['name'], $pointcut_cfg['method'])){
			if(true === $forward_aop_process){
				return $this->___aop_process($aop_call_counter);
			}
			return ;
		}
		
		$aspect_obj = lrn_aop::load_aspect($pointcut_cfg['aspect'], true);
		if(false === $aspect_obj){
			if(true === $forward_aop_process){
				return $this->___aop_process($aop_call_counter);
			}
			return ;
		}
		
		return $aspect_obj->$pointcut_cfg['advice_method']($aop_call_counter, $this, isset($pointcut_cfg['advice_param']) ? $pointcut_cfg['advice_param'] : null);
	}
	
	/**
	 * 获取原始对象
	 * @return object
	 */
	public function ___aop_origin_get(){
		return $this->instance;
	}
	

	/**
	 * 获取原始对象对应的类名
	 * @return NULL
	 */
	public function ___aop_origin_get_name(){
		return $this->instance_name;
	}
	

	public function ___aop_data_stack_get($aop_call_counter, $name){
		if(!isset($this->aop_data_stack[$aop_call_counter][$name])){
			trigger_error('aop_data_stack not found!', E_USER_ERROR);
		}
		
		return $this->aop_data_stack[$aop_call_counter][$name];
		
	}
	

	public function ___aop_data_stack_set($aop_call_counter, $name, $value){
		if('name' === $name){
			trigger_error('can not set name in aop_data_stack->stack '. $aop_call_counter, E_USER_ERROR);
		}
		
		$this->aop_data_stack[$aop_call_counter][$name] = $value;
		return $this;
	}
	
	public function ___aop_advice_introduction_get($name){
		if(isset($this->aop_adivce_introduction_stack[$name])){
			return $this->aop_adivce_introduction_stack[$name];
		}
		return false;
	}
	
	public function ___aop_advice_introduction_inject($name, lrn_aop_abstract_advice_introduction $obj = null){
		$this->aop_adivce_introduction_stack[$name] = $obj;
	}
	
	
	/**
	 * 调用原始对象的方法
	 * 此处不完全使用call_user_func_array的原因，是考虑到可能某些第三方类的方法存在引用
	 * @param string $name
	 * @param mixed $param
	 */
	protected function ___aop_call_origin($name, &$param){
		//echo ">>>>>>>>". get_class($this->instance). '->'. $name. "\r\n";
		$param_count = 0;
		if(!empty($param)){
			$param_count = count($param);
		}
		
		switch($param_count){
			case 0:
				return $this->instance->$name();
				break;
			case 1:
				return $this->instance->$name($param[0]);
				break;
			case 2:
				return $this->instance->$name($param[0], $param[1]);
				break;
			case 3:
				return $this->instance->$name($param[0], $param[1], $param[2]);
				break;
			case 4:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3]);
				break;
			case 5:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3], $param[4]);
				break;
			case 6:
				return $this->instance->$name($param[0], $param[1], $param[2], $param[3], $param[4], $param[5]);
				break;
			default:
				call_user_func_array(array($this->instance, $name), $param);
				break;
		}
		
	}
	
}



//======================
class hmvc_index_controller_index{
	public function index(){
		echo 'hmvc_index_controller_index->index() running~'. "\r\n";
		return 'return_value';
	}
	
	public function no_around_test(){
		echo 'hmvc_index_controller_index->no_around_test() running~'. "\r\n";
	}	
	
	public function special_around_test(){
		echo 'hmvc_index_controller_index->special_around_test() running~'. "\r\n";
	}

	public function get_data(){
		echo 'hmvc_index_controller_index->get_data()~'. "\r\n";
	}

	public function get_data_222(){
		echo 'hmvc_index_controller_index->get_data_222()~'. "\r\n";
	}
		
	
}


class plugin_aop_aspect_test1 extends lrn_aop_abstract_aspect{
	
	public function do_before($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. "\r\n";
	}
	
	public function do_before2($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. "\r\n";
		
		//$aop_instance->___aop_data_stack_set($aop_call_counter, 'result', 'test_advice_direct_return_result');
		//return self::FORCE_RETURN_RESULT;
	}
	
	public function do_before3($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. "\r\n";
	}
	
	public function do_after($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. " FORCE STOP OTHER ASPECT WITHOUT AROUND\r\n";
		return self::FORCE_RETURN_RESULT;
	}
	
	public function do_after22($aop_call_counter, lrn_aop_container $aop_instance){
		echo __METHOD__. "\r\n";
		
		//sleep(5);		
		//lrn_aop::getInstance('hmvc_index_controller_index')->special_around_test();    //模拟aop容器之间的死循环

		
	}
		
	public function do_around($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. " before\r\n";
		$time = microtime(true);
		$aop_instance->___aop_process($aop_call_counter);
			
		$process_time = microtime(true) - $time;
		
		echo __METHOD__. " after process. around using:{$process_time}s\r\n";
	}
	

	public function do_around_2($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. " before\r\n";
		$aop_instance->___aop_process($aop_call_counter);
		
		//$aop_instance->get_data_222();        //模拟aop容器内死循环
		
		echo __METHOD__. " after.\r\n";
	}
	
	public function do_around_special_test($aop_call_counter, lrn_aop_container $aop_instance, $advice_param){
		echo __METHOD__. " before\r\n";
		$aop_instance->___aop_process($aop_call_counter);
		echo __METHOD__. " after.\r\n";	
	}
	
}

class plugin_aop_advice_introduction_test3 extends lrn_aop_abstract_advice_introduction{

	public function get_class(){
		return get_class($this->instance);
	}

}


class plugin_aop_advice_introduction_test1 extends lrn_aop_abstract_advice_introduction{

	protected $locked = false;

	public function locked(){
		return $this->locked;
	}
	
	public function set_locked($bool){
		$this->locked = (bool)$bool;
		return $this;
	}
	
	public function __call($name, $param){
		if($this->locked){
			throw new Exception('CAN NOT CALL ANY METHOD BECAUSE PROPERTY IS LOCKED!');
		}
		return parent::__call($name, $param);
	}
	

}

//aop实验区
$aop_instance = lrn_aop::getInstance('hmvc_index_controller_index');    //其实就是一个lrn_aop_container实例
echo $aop_instance->index();
echo "\r\n================\r\n";

//$aop_instance->no_around_test();
//echo "\r\n================\r\n";

//$aop_instance->special_around_test();
//echo "\r\n================\r\n";


//lrn_aop_advice_introduction实验区
$advice_introduction_test3_obj1 = lrn_aop_advice_introduction::get('test3', $aop_instance, true);
$advice_introduction_test3_obj2 = lrn_aop_advice_introduction::get('test3', $aop_instance, true);
var_export('[aop_instance]spl_object_hash of lrn_aop_advice_introduction::get equal result:'. (spl_object_hash($advice_introduction_test3_obj1) === spl_object_hash($advice_introduction_test3_obj2)));
echo "\r\n================\r\n";



$array_object = new ArrayObject();
$advice_introduction_test3_arr_obj1 = lrn_aop_advice_introduction::get('test3', $array_object, true);
$advice_introduction_test3_arr_obj2 = lrn_aop_advice_introduction::get('test3', $array_object, true);
var_export('[ArrayObject]spl_object_hash of lrn_aop_advice_introduction::get equal result:'. (spl_object_hash($advice_introduction_test3_arr_obj1) === spl_object_hash($advice_introduction_test3_arr_obj1)));
echo "\r\n================\r\n";
$advice_introduction_test3_arr_obj2->offsetset('9', 'asdfdasfasdfasdf');
var_export($array_object->offsetget('9'));
echo "\r\n================\r\n";


//使用introduction通知改变类行为
$advice_introduction = lrn_aop_advice_introduction::get('test1', $aop_instance, true);
$advice_introduction->set_locked(true);
echo '$advice_introduction->locked() => ';
var_export($advice_introduction->locked());
echo "\r\n================\r\n";

$advice_introduction->special_around_test();
echo "\r\n================\r\n";
