<?php
namespace Dorsataio\ACore\Handler;

class APIRouteHandler{

	public function __construct($api, string $path, array $methods, $callback, array $beforeAfter, $handlers = []){
		// Load custom handlers
		if(isset($handlers) && is_array($handlers)){
			foreach($handlers as $handler){
				if(file_exists("{$api->rootDir()}/{$handler}")){
					require_once("{$api->rootDir()}/{$handler}");
				}
			}
		}
		if(empty($methods)){
			trigger_error("Failed to instantiate API route {$path}; invalid methods or no methods were supplied.", E_USER_ERROR);
		}
		if(!is_callable($callback)){
			trigger_error("Failed to instantiate API route {$path}; the callback function is not callable.", E_USER_ERROR);
		}
		$beforeCallbackTriggers = isset($beforeAfter[0]) ? $beforeAfter[0] : [];
		$afterCallbackTriggers = isset($beforeAfter[1]) ? $beforeAfter[1] : [];
		foreach($beforeCallbackTriggers as $i => $callable){
			if(!$callable['callback'] || !is_callable($callable['callback'])){
				trigger_error("Unable to attach middleware due to uncallable within $beforeCallbackTriggers stack at {$i}.", E_USER_WARNING);
				continue;
			}
			$callable = $callable['callback'];
			$api->add(function($request, $response, $next) use($api, $callable){
				return $callable($api, $request, $response, $next);
			});
		}
		$api->map($methods, $path, function($request, $response, $args) use($api, $callback){
			return $callback($api, $request, $response, $args);
		});
		foreach($afterCallbackTriggers as $i => $callable){
			if(!$callable['callback'] || !is_callable($callable['callback'])){
				trigger_error("Unable to attach middleware due to uncallable within $afterCallbackTriggers stack at {$i}.", E_USER_WARNING);
				continue;
			}
			$callable = $callable['callback'];
			$api->add(function($request, $response, $next) use($api, $callable){
				return $callable($api, $request, $response, $next);
			});
		}
	}
}