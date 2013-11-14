<?php
/**
 * 提取或排除数组内的数据
 * 为array_filter的补充版
 * 
 * @author Horse Luke
 * @since 2013-11-14
 * @version $Id$
 */
class lrn_tool_array_picker{
    
    /**
     * 忽略，既不处理也不赋任何值（魔术字符）
     */
    const IGNORE = '__MAGIC_CODE_IGNORE_30!@%$71$^*&08__';
    
    protected $_rule_parse_cache = array();
    
    /**
     * 简单的获取某些索引
     * @param array $data 要过滤的数组
     * @param array|string $cond 只保留的索引键。如果是字符串，每个索引键必须使用半角逗号（,）。如果为空，将返回空数组
     * @param mixed $not_found_def 索引键不存在时如何处理？
     */
    public function by_index($data, $cond, $not_found_def = self::IGNORE){
        if(!is_array($data) || empty($data)){
            return $data;
        }
        
        if(empty($cond)){
            return array();
        }
        
        if(!is_array($cond)){
            $cond = explode(',', $cond);
        }
        
        $return = array();
        foreach($cond as $key){
            if(isset($data[$key])){
                $return[$key] = $data[$key];
            }elseif(self::IGNORE != $not_found_def){
                $return[$key] = $not_found_def;
            }
        }
        
        return $return;
        
    }
    
    /**
     * 简单的排除某些索引
     * @param array $data 要过滤的数组
     * @param array|string $cond 要剔除的索引键。如果是字符串，每个索引键必须使用半角逗号（,）。如果为空，将原样返回不做处理
     * @param bool $cond_flipped $cond参数是否经过了预先array_flip？默认为false
     * @return mixed|array
     */
    public function exclude_index($data, $cond, $cond_flipped = false){
        
        if(!is_array($data) || empty($data) || empty($cond)){
            return $data;
        }
         
        if(!is_array($cond)){
            $cond = explode(',', $cond);
        }
        
        return array_diff_key($data, $cond_flipped ? $cond : array_flip($cond));
        
        /*
        //另一种方法
        $cond = $cond_flipped ? $cond : array_flip($cond);
        reset($data);

        //reset已触发copy on write，此处用while是防止再次触发该机制
        //http://horseluke-code.googlecode.com/svn/trunk/draftCode/for_foreach.php        
        while($row = each($data)) {
            if(isset($cond[$row[0]]) && isset($data[$row[0]])){
                unset($data[$row[0]]);
                continue;
            }
        }
        
        return $data;
        */
    }
    
    /**
     * 根据某些规则获取某些索引值
     * @param array $data 要过滤的数组
     * @param array|string $cond 规则组合。如果不是数组，规则之间请使用半角&连接。
     * 每条规则方法如下：
     * [数组路径，索引名之间使用/分割。如果是循环数组、或匹配任意数组索引键，请使用*]/(_return|_return_exclude)=xx,xx,xx,xx,xx
     * @param mixed $not_found_def (_return时才有效)索引键不存在时如何处理？
     * @return mixed|array
     */
    public function by_rule($data, $cond, $not_found_def = self::IGNORE){
        
        if(!is_array($cond)){
            $cond = explode('&', $cond);
        }
        
        foreach($cond as $cond_row){
            if(!is_array($data) || empty($data)){
                break;
            }
            
            $cond_row_block = $this->parse_rule($cond_row);
            if(empty($cond_row_block)){
                continue;   //规则有问题，此时忽略此过滤条件
            }
            
            if(empty($cond_row_block['path'])){
                $data = ('_return_exclude' == $cond_row_block['type'])
                ? $this->exclude_index($data, $cond_row_block['index'], $cond_row_block['index_flipped'])
                : $this->by_index($data, $cond_row_block['index'], $not_found_def);
            }else{
                $cond_row_block['_pointer'] = 0;
                $cond_row_block['_not_found_def'] = $not_found_def;
                array_walk($data, array($this, '_by_rule_do_format_array_walker'), $cond_row_block);
            }
            
        }
        
        return $data;
        
    }
    
    /**
     * 根据某些规则获取某些索引值(正式过滤方法之array_walk递归)
     * 此方法仅供内部使用！因为使用array_walk而必须public而已。
     * @param &$value 要过滤的数据（array_walk走到的数组数据）
     * @param array $key 要过滤的数据（array_walk走到的数组索引）
     * @param array $cond 已经解析好的过滤条件（array_walk特供版）
     * @return bool
     */    
    public function _by_rule_do_format_array_walker(&$value, $key, array $cond){
        if(!is_array($value) || empty($value)){
            return false;
        }
    
        if(!isset($cond['path'][$cond['_pointer']]) || empty($cond['path'][$cond['_pointer']])){
            return false;
        }
    
        $path_name = $cond['path'][$cond['_pointer']];
        if($path_name == $key || '*' == $path_name){
            if($cond['_pointer'] >= $cond['path_max_number']){
                $value = ('_return_exclude' == $cond['type'])
                ? $this->exclude_index($value, $cond['index'], $cond['index_flipped'])
                : $this->by_index($value, $cond['index'], $cond['_not_found_def']);
            }else{
                $cond['_pointer']++;
                return array_walk($value, array($this, __FUNCTION__), $cond);
            }
        }
    
    }

    /**
     * 解析一条规则
     * @param string $cond
     * @return array|null
     */
    public function parse_rule($cond){
        if(isset($this->_rule_parse_cache[$cond])){
            return $this->_rule_parse_cache[$cond];
        }
        
        $result = null;
        
        preg_match('/^(.*)(?:[\/ ]*)(_return|_return_exclude)=(.+)$/U', trim($cond, "\x00..\x20/,"), $match);
        if(!empty($match)){
            $result = array(
                'path' => empty($match[1]) ? null : explode('/', $match[1]),
                'type' => $match[2],
                'index' => empty($match[3]) ? null : explode(',', $match[3]),
                'index_flipped' => false,
            );
            $result['path_max_number'] = empty($result['path']) ? 0 : count($result['path']) - 1;
            if('_return_exclude' == $result['type'] && !empty($result['index'])){
                $result['index'] = array_flip($result['index']);
                $result['index_flipped'] = true;
            }
        }
        
        $this->_rule_parse_cache[$cond] = $result;
        return $result;
        
    }

}
