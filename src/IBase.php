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
 * base implement
 */
namespace config ;

interface IBase {
	public static function get($key) ;
	public static function set($key, $value) ;
	public static function has($key) ;
}
