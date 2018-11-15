<?php
namespace Dorsataio\ACore\Factory;

class APIFactory{

	private static $_defaults = [];

	public static function create(string $name, string $apiRoot, array $configs = []){
		// Get default configs
		self::$_defaults = include(dirname(dirname(__FILE__)) . '/config/config.php');
		// Merge configs to defaults
		$configs = array_merge(self::$_defaults, $configs);
		// Force Whoops pretty page error handler
		if((isset($_GET['__conf-debug']) && $_GET['__conf-debug'] === 'pretty')){
			// $configs['enableSlimGeneralErrorHandler'] = false;
			// $configs['enableSlimFatalErrorHandler'] = false;
			$configs['enablePrettyErrorHandler'] = true;
		}
		// Create a new API instance
		$api = new \Dorsataio\ACore\API($name, $apiRoot);
		// Use our custom Whoops error handler instead!
		$api->registerErrorHandler();
		if($configs['enablePrettyErrorHandler'] === true){
			$api->registerPrettyErrorHandler();
		}
		// Register core loggers
		if(isset($configs['loggers']) && is_array($configs['loggers'])){
			foreach($configs['loggers'] as $channel => $config){
				$api->registerLogHandler($channel, $config);
			}
		}
		// Register a SLIM application
		$api->registerAppHandler($configs['displaySlimErrorDetails']);
		// Disable SLIM's error handlers by default?
		unset($api->getContainer()['errorHandler']);
		unset($api->getContainer()['phpErrorHandler']);
		// Register database connections
		if(is_array($configs['databases']) && !empty($configs['databases'])){
			$api->registerDatabases($configs['databases']);
		}
		// Pre-register some default routes that ALL API will have access to from
		// the geco.
		if($configs['bypassDefaultCrossOriginHandler'] === false){
			$api->registerApiRoute($crossOrigin = include(dirname(dirname(__FILE__)) . '/Route/CrossOrigin.php'));
		}
		$api->registerApiRoute($helloWorld = include(dirname(dirname(__FILE__)). '/Route/HelloWorld.php'));
		// Load & register API defined routes
		$routes = $api->loadApiRoutes("{$api->rootDir()}/Route", '*.php');
		$api->registerApiRoutes($routes);
		// Return the api object
		return $api;
	}
}