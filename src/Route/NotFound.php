<?php 
// namespace Dorsataio\ACore\Route;

// /**
//  * Catch-all route to serve a 404 Not Found page if none of the routes match
//  * NOTE: make sure this route is defined last
//  *
//  * @method NotFound
//  *
//  * @param  [type]      $app [description]
//  *
//  * @return [type]           [description]
//  */
// class NotFound{

// 	public function __construct($app, $path){
// 		$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, function($req, $res){
// 	    	$handler = $this->notFoundHandler; // handle using the default Slim page not found handler
// 		    return $handler($req, $res);
// 		});
// 	}
// }