# helper-config
配置读写工具

##### email: <yuhari@126.com>
----
###install:
1. **git** 
	<pre>git clone https://github.com/yuhari/helper-config.git</pre>
2. **composer**
	<pre>composer require yuhari/config </pre>

### example usages:
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
	 * Test Case
	 */
	
	include_once '../vendor/autoload.php' ;
	
	//initial
	\config\Factory::init(__DIR__ . '/conf/', 'dev') ;
	
	//you can get all options by character '*' .
	$c = \config\Factory::get('example.*') ;
	print_r($c) ;
	
	//you can get a specified value.
	$c = \config\Factory::get('example.tip') ;
	print_r($c) ;
	
	//you may also achieve target as this
	$c = \config\Factory::get('example', 'tip') ;
	print_r($c) ;
	
	//you can set a value
	\config\Factory::set('example.tip', 'dev mode') ;
	$c = \config\Factory::get('example.tip') ;
	print_r($c) ;
	
	\config\Factory::set('env.welcome' , 'Hello, world!') ;
	$c = \config\Factory::get('env.welcome') ;
	print_r($c) ;
