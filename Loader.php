<?php
namespace Fw;

use Fw\Lib\Framework\UrlManager;

class Loader{

	public static $aliases = ['@Fw' => __DIR__];

	private static $container;

	public static function autoload($className)
	{
		$classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
		if ($classFile === false || !is_file($classFile)) {
			return;
		}

		include($classFile);

		if ( !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
			throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}

	public static function getAlias($alias, $throwException = true)
	{
		if (strncmp($alias, '@', 1)) {
			return $alias;
		}

		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);

		if (isset(static::$aliases[$root])) {
			if (is_string(static::$aliases[$root])) {
				return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $path . substr($alias, strlen($name));
					}
				}
			}
		}

		if ($throwException) {
			throw new \Exception("Invalid path alias: $alias");
		} else {
			return false;
		}
	}

	public static function run(){
		header("Content-Type: text/html; charset=UTF-8");
		$path_info = UrlManager::getPathInfo();
		if( empty( $path_info ) ) {
			die('invalid request');
		}
		list($module , $controller, $action) = explode( '/', $path_info );
		if( !isset($module) || !isset($controller) || !isset($action) ){
			throw new \Exception('Missing parameter');
		}
		$controller = UrlManager::createControllerByID( 'Fw\\Modules\\'.ucfirst($module).'\\controllers', $controller ,$action);

		UrlManager::runAction( $controller, $action );
	}


}
