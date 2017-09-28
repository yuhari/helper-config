<?php
/**
 * 
 *
 * @author yuhari
 * @version $Id$
 * @copyright , 27 September, 2017
 * @package default
 */

/**
 * 配置解析
 */
namespace config ;

class Factory implements IBase {
	
	//配置文件目录
	protected static $configDir ;
	
	//当前已加载的配置信息
	protected static $config = [] ;
	
	//当前环境模式，如生产环境product，开发环境dev
	protected static $mode ;
	
	//配置文件后缀，如.ini
	protected static $suffix ;
	
	//配置上下级分隔符
	public static $delimit = '.' ;
	
	//不同mode继承分隔符
	public static $extendDelimit = ':' ;
	
	//初始化，设置配置目录和环境模式
	//同时默认env为项目中的环境变量
	public static function init($configDir, $mode = 'product', $suffix = '.ini'){
		if (!is_dir($configDir)){
			throw new \Exception(sprintf("The directory routed '%s' not exist.", $configDir)) ;
		}
		
		self::$configDir = $configDir ;
		self::$mode = $mode ;
		self::$suffix = $suffix ;
		
		self::set('env.mode', $mode) ;
	}
	
	//读取一个$key的配置信息
	public static function get($key){
		if ($key === '*') {
			return self::$config ;
		}
		
		if (func_num_args() > 1) {
			$key = implode(static::$delimit, func_get_args()) ;
		}
		
		$ks = explode(static::$delimit, $key) ;
		if ($ks[0] !== 'env'){
			do{
				self::loadConfigIni($ks[0]) ;
				$mkey = self::getModeKey($key) ;
				$mval = self::getByStep($mkey, self::$config) ;
				if ($mval !== null) {
					return $mval ;
				}
			}while(false) ;
		}
		return self::getByStep($key, self::$config) ;
	}
	
	//这里会优先获取当前环境模式下的key
	protected static function getModeKey($key) {
		$mode = static::getByStep('env.mode', self::$config) ;
		
		$ks = explode(static::$delimit, $key) ;
		if ($mode !== null) {
			$t = array_shift($ks) ;
			array_unshift($ks, $mode) ;
			array_unshift($ks, $t) ;
		}
		return implode(static::$delimit, $ks) ;
	}
	
	//从已加载的配置信息中查找
	protected static function getByStep($key, $config){
		if ($key === '*') return $config ;
		
		$ks = explode(static::$delimit, $key) ;
		$fk = array_shift($ks) ;
		$rk = implode(static::$delimit, $ks) ;
		
		if (!is_array($config)){
			return $rk === '' ? $config : null ;
		}
		
		$res = null ;
		if (array_key_exists($fk, $config)){
			$res = self::getByStep($rk, $config[$fk]) ;
		}
		return $res ;
	}
	
	public static function has($key){
		return static::get($key) !== null ;
	}
	
	//设置一个$key的变量
	public static function set($key, $value){
		$ks = explode(static::$delimit, $key) ;
		
		if ($ks[0] !== 'env') {
			do{
				self::loadConfigIni($ks[0]) ;	
			}while(false) ;
			$mkey = self::getModeKey($key) ;
			if (self::has($mkey)) $key = $mkey ;
		}
		
		self::setByStep($key, self::$config, $value) ;
	}
	
	protected static function setByStep($key , &$config, $value){
		$ks = explode(static::$delimit, $key) ;
		$fk = array_shift($ks) ;
		$rk = implode(static::$delimit, $ks) ;
		
		if ($rk === '') {
			$config[$fk] = $value ;
		}else{
			self::setByStep($rk, $config[$fk], $value) ;
		}
		return true ;
	}
	
	protected static function beenLoad($key){
		return array_key_exists($key, self::$config) ;
	}
	
	protected static function getFile($name){
		return sprintf("%s/%s", self::$configDir, $name . self::$suffix) ;
	}
	
	//加载一个名为$name的配置
	public static function loadConfigIni($name) {
		if (!self::beenLoad($name)){
			$fileName = self::getFile($name) ;
			
			if (!is_file($fileName)) {
				throw new Exception(sprintf("The file named '%s' not exist." , $fileName)) ;
			}
			
			$data = parse_ini_file($fileName, true) ;
			$data = self::handleIni($data) ;
			static::$config[$name] = $data ;
		}
	}
	
	//处理默认的ini格式
	public static function handleIni($data) {
		$data = self::explodeToArray($data) ;
		
		$data = self::extendByArray($data) ;
		return $data ;
	}
	
	protected static function explodeToArray($data) {
		foreach($data as $k => $v) {
			if (is_array($v)){
				$data[$k] = self::explodeToArray($v) ;
			}
			
			$ks = explode(static::$delimit, $k) ;
			if (count($ks) > 1) {
				$fk = array_shift($ks) ;
				if (!array_key_exists($fk, $data)){
					$data[$fk] = [] ;
				}
				
				unset($data[$k]) ;
				$rk = implode(static::$delimit, $ks) ;
				$tres = self::explodeToArray([$rk => $v]) ;
				$data[$fk] = array_replace_recursive( $data[$fk], $tres) ;
			}
		}

		return $data ;
	}
	
	protected static function extendByArray($data) {
		foreach($data as $k => $v) {
			$ks = explode(static::$extendDelimit, $k) ;
			
			if (count($ks) != 2) continue ;
			
			if (!array_key_exists($ks[1], $data)) continue ;
			$data[$ks[0]] = array_replace_recursive($data[$ks[1]] , $data[$k]) ;
			unset($data[$k]) ;
		}
		return $data ;
	}
}
