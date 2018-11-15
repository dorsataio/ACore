<?php
namespace Dorsataio\ACore;

class API{

	private $_serviceName = false;
	private $_servicePath = false;
	private $_apiRoot = false;
	private $_slim;
	private $_dbs;

	/**
	 * Creates a new API instance
	 *
	 * @method __construct
	 */
	public function __construct(string $name, string $apiRoot){
		$this->_serviceName = $name;
		$this->_servicePath = "/{$name}";
		$this->_apiRoot = $apiRoot;
	}

	/**
	 * Redirect and invoke a slim method if it doesn't exist in this class.
	 *
	 * @method __call
	 *
	 * @param  string 	$name Name of the method to call
	 * @param  array 	$args Array of arguments to pass into the method we're calling
	 *
	 * @return Mixed 	Slim's response object
	 */
	public function __call($name, $args){
		return call_user_func_array(array($this->_slim, $name), $args);
	}

	/**
	 * Retrieves the _serviceName set when an API instance is created
	 *
	 * @method name
	 *
	 * @return string The service name.
	 */
	public function name(){
		return (string) $this->_serviceName;
	}

	/**
	 * Retrieves the _apiRoot directory path set when an API instance is created.
	 *
	 * @method rootDir
	 *
	 * @return string  /the/absolute/path/to/our/API
	 */
	public function rootDir(){
		return (string) $this->_apiRoot;
	}

	/**
	 * Wrapper for our HTPP status code handler.
	 *
	 * @method statusCodes
	 *
	 * @return object      An instance of \Dorsataio\ACore\Handler\StatusCodeHandler
	 */
	public function statusCodes(){
		return Handler\StatusCodeHandler::getInstance();
	}

	/**
	 * Returns a Monolog logger object by its channel.
	 *
	 * @param string $name 	A unique log channel.
	 *
	 * @return object 		A Monolog object.
	 */
	public function logger(string $name){
		return Handler\LogHandler::getLogger($name);
	}

	/**
	 * Returns a Medoo database object by its name that was set
	 * during registration, please see registerDatabases(array $databases).
	 *
	 * @method database
	 *
	 * @param  string   $name A database name used during registration.
	 *
	 * @return object         A Medoo database object.
	 */
	public function database(string $name = ''){
		if(empty($name)){
			return $this->_dbs[array_shift(array_keys($this->_dbs))];
		}
		return $this->_dbs[$name];
	}

	/**
	 * Wrapper for our API Response Handler object
	 *
	 * @method response
	 *
	 * @return object   An instance of \Dorsataio\ACore\Handler\APIResponseHandler
	 */
	public function response(){
		return new Handler\APIResponseHandler($this->_serviceName);
	}

	/**
	 * Register a new SLIM application
	 *
	 * @param bool|null $displayErrorDetails Set to true for SLIM to display
	 *                                       detailed error information.
	 */
	public function registerAppHandler(bool $displayErrorDetails = null){
		$configuration = [
		    'settings' => [
		        'displayErrorDetails' => $displayErrorDetails,
		    ],
		];
		$c = new \Slim\Container($configuration);
		$c['notFoundHandler'] = function ($c){
			return function ($request, $response) use ($c){
				// Log the error
				$message = "The resource '{$request->getUri()->getPath()}' does not exist.";
				throw new \Exception($message, $this->statusCodes()::NOT_FOUND);
			};
		};
		$c['notAllowedHandler'] = function ($c){
			return function ($request, $response) use ($c){
				// Log the error
				$message = "The resource '{$request->getUri()->getPath()}' does not exist.";
				throw new \Exception($message, $this->statusCodes()::NOT_FOUND);
			};
		};
		$this->_slim = new \Slim\App($c);
		// Log new requests
		$logger = $this->logger('core-access');
		$this->_slim->add(function ($request, $response, $next) use($logger){
			$logger->info('NEW REQUEST', [
				'header' => $request->getHeaders(),
				'path' => $request->getUri()->getPath(),
				'query' => $request->getUri()->getQuery(),
				'body' => $request->getParsedBody(),
			]);
			return $next($request, $response);
		});
	}

	/**
	 * Register a new Monolog logger instance.
	 *
	 * @param string $name     	A unique log channel.
	 * @param string $path     	The path to the log file.
	 * @param string $filename 	The filename to which log entries are written.
	 *
	 * @return object 			A Monolog object.
	 */
	public function registerLogHandler(string $channel, array $config){
		$logger = Handler\LogHandler::$defaults;
		if(!empty($config)){
			$logger = array_merge($logger, $config);
		}
		// Append the service name to the base path
		$logger['path'] .= "/{$this->_serviceName}";
		// Return a new handler
		return Handler\LogHandler::newLogger($channel, $logger);
	}

	/**
	 * Create Medoo database objects
	 *
	 * @param array $conf Array of database connection parameters
	 */
	public function registerDatabases(array $databases){
		if(is_array($databases)){
			foreach($databases as $name => $conn){ 
				if(isset($conn['host']) && isset($conn['username']) && isset($conn['password']) && isset($conn['database'])){
					$type = (isset($conn['type']) && !empty($conn['type'])) ? $conn['type'] : 'mysql';
					// $this->_dbs[$name] = new \Medoo\Medoo([
					// 	'database_type' => $type,
					// 	'database_name' => $conn['database'],
					// 	'server' => $conn['host'],
					// 	'username' => $conn['username'],
					// 	'password' => $conn['password']
					// ]);
					$this->_dbs[$name] = new \Dorsataio\Squibble\Squibble([
						'type' 		=> $type,
						'host' 		=> $conn['host'],
						'port' 		=> $conn['port'],
						'dbname' 	=> $conn['database'],
						'user' 		=> $conn['username'],
						'password' 	=> $conn['password'],
					]);
				}
			}
		}
	}

	/**
	 * Register a custom filp/whoops error callback handler. Try to do this as
	 * early in the code as possible.
	 *
	 * @method registerErrorHandler
	 */
	public function registerErrorHandler(){
		if(!$this->_whoops){
			$this->_whoops = new \Whoops\Run;
		}
		$api = $this;
		$callback = new \Whoops\Handler\CallbackHandler(function($exception, $inspector, $run) use($api){
			// Execute API's shutdown method
			$api->shutdownWithError($exception->getMessage(), $exception->getCode(), $exception->getTrace());
		});
		$this->_whoops->pushHandler($callback);
		$this->_whoops->register();
	}

	/**
	 * Register a new filp/whoops json error heandler. Try to do this as
	 * early in the code as possible.
	 *
	 * @param bool|null $addTrace Should detailed stack trace output also be added to the
	 *                             JSON payload body? Set to "true" to include trace.
	 */
	public function registerPrettyErrorHandler(bool $addTrace = null){
		if(!$this->_whoops){
			$this->_whoops = new \Whoops\Run;
		}
		$this->_whoops = new \Whoops\Run;
		$handler = new \Whoops\Handler\PrettyPageHandler;
		$this->_whoops->pushHandler($handler);
		$this->_whoops->register();
	}

	/**
	 * Load route resources from any given path. Very useful when we want to load
	 * all custom API routes from our custom API's /Route directory. We so have the option
	 * of specifying a PHP glob pattern in the event that our directy contains other
	 * files including route files.
	 *
	 * @method loadApiRoutes
	 *
	 * @param  string        $path    Absolute path to the route resources.
	 * @param  string        $pattern PHP glob pattern for example, *.php would ensure files
	 *                                retrieve end with the .php extension.
	 *
	 * @return [type]                 [description]
	 */
	public function loadApiRoutes(string $path, string $pattern = '*.php'){
		$routeFiles = glob("{$path}/{$pattern}");
		$routes = [];
		foreach($routeFiles as $routeFile){
			$route = include($routeFile);
			array_push($routes, $route);
		}
		return $routes;
	}

	/**
	 * Register a single custom API route.
	 *
	 * @method registerApiRoute
	 *
	 * @param  array            $route Array containing route parameters.
	 */
	public function registerApiRoute(array $route){
		// Alter the route's path to include the full API (service) path
		$route['path'] = preg_replace('/\/\/+/', '/', "{$this->_servicePath}/{$route['path']}");
		// Create the API route so that it is accessible 
		// https://yourdomain.com/path/to/service/follow/by/api/route
		Factory\APIRouteFactory::create($this, $route);
	}

	/**
	 * Register a list of app defined SLIM paths. Please see registerApiRoute(array $route)
	 *
	 * @param array $routes Array containing many routes
	 */
	public function registerApiRoutes(array $routes){
		foreach($routes as $route){
			$this->registerApiRoute($route);
		}
	}
	
	/**
	 * This is were all errors go to die! Actually, it is a wrapper to handle API errors
	 * to ensure all errors are treated equally and outputted the same way.
	 *
	 * @method shutdownWithError
	 *
	 * @param  string            $message A user friendly error message or internal error message.
	 * @param  string            $code    An error code that should start with a 3 digit HTTP status code
	 *                                    followed by an application error code. For example 500APPERR01 is
	 *                                    parsed into "500" as the HTTP status code and "APPERR01" as the 
	 *                                    application error code.
	 * @param  array             $trace   An array containing trace data specific to where the error or
	 *                                    exception was triggered. This is included in the error log file
	 *                                    and aid with debugging.
	 */
	public function shutdownWithError(string $message, string $code, array $trace){
		// Log the error
		$this->logger('core-error')->error($message, ($trace[1] ? $trace[1] : $trace[0]));
		// Output a friendly error message
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: *');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
		$httpCode = substr((string) $code, 0, 3);
		if(!$this->statusCodes()::getMessageForCode($httpCode)){
			// Default to 500 - Internal Server Error
			$httpCode = 500;
		}
		header($this->statusCodes()::httpHeaderFor($httpCode));
		header("Content-type: application/json; charset=utf-8");
		$apiRespond = $this->response();
		$error = [
			'title' => $this->StatusCodes()::getMessageForCode($httpCode),
			'detail' => 'We encountered an internal error while processing your request.',
			'code' => $code
		];
		if($httpCode < 500){
			$error['detail'] = $message;
		}
		$apiRespond->hasError($httpCode, $error);
		echo $apiRespond;
		exit(0);
	}
}