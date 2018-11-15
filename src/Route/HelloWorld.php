<?php 
return [
	'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
	'path' => '/HelloWorld',
	'beforeCallback' => [],
	'callback' => function($api, $request, $response, $args){
		$apiRespond = $api->response();
		$apiRespond->isSuccess($api->statusCodes()::OK)->withData([
			'message' => 'Hello World!'
		]);
		return $response->withJson($apiRespond->getBody(), $apiRespond->getStatusCode());
	},
	'afterCallback' => [],
];