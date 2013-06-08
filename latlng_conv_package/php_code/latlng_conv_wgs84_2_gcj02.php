<?php
/**
 * latlng转换器：从wgs84转换到gcj02（暂未实现）
 * 
 * 验证方法可以从高德地图中验证。验证url：
 * http://mo.amap.com/?q=纬度,经度&name=park&dev=0#thirdpoi
 * 
 * 也有在线转换方法，文档见：document/latlng_conv_online_wgs84_2_gcj02.txt
 * 
 * @link http://blog.csdn.net/coolypf/article/details/8686588
 * @author wgtochina_lb
 * @author coolypf
 * @author hl
 * @version 0.1
 * 
 */
class latlng_conv_wgs84_2_gcj02{
	
	/**
	 * 转换接口
	 * @param float|array $lat wgs84坐标系的纬度、或者array('lat'=>lat, 'lng'=>lng)
	 * @param float $lng wgs84坐标系的经度
	 * @return array 转换后的gcj02坐标(高德坐标系)。格式array('lat'=>lat, 'lng'=>lng)
	 */
	static public function conv($lat, $lng = 0){
		if(is_array($lat)){
			$lng = isset($lat['lng']) ? $lat['lng'] : 0;
			$lat = isset($lat['lat']) ? $lat['lat'] : 0;
		}
		return array(
			'lat' => 0,
			'lng' => 0,
		);
		
	}
	
}

