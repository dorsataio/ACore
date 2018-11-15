<?php /**
 * For handling cross origin requests
 *
 * @method crossOrigin
 *
 * @param  [type]      $app [description]
 *
 * @return [type]           [description]
 */
return [
	'methods' => ['OPTIONS'],
	'path' => '/{routes:.+}',
	'beforeCallback' => [],
	'callback' => function($api, $request, $response, $args){
		return $response;
	},
	'afterCallback' => [
		['callback' => function($api, $request, $response, $next){
		    $response = $next($request, $response);
		    return $response
		            ->withHeader('Access-Control-Allow-Origin', '*')
		            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
		}]
	]
];