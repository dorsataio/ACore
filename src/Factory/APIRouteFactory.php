<?php
namespace Dorsataio\ACore\Factory;

class APIRouteFactory{

	public static function create($api, $params){
		return new \Dorsataio\ACore\Handler\APIRouteHandler($api, $params['path'], $params['methods'], $params['callback'], array($params['beforeCallback'], $params['afterCallback']), $params['handlers']);
	}
}