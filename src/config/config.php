<?php
/**
 * 
 */
return [
	'enableSlimGeneralErrorHandler' => false,
	'enableSlimFatalErrorHandler' => false,
	'displaySlimErrorDetails' => false,
	'enablePrettyErrorHandler' => false,
	'bypassDefaultCrossOriginHandler' => false,
	'loggers' => [
		'core-access' => [
			'path' => $_SERVER['DOCUMENT_ROOT'] . '/logs',
			'filename' => 'access.log',
			'rotateFiles' => true,
			// example: ./logs/2018/06/08-errors.log
			'rotateFilenameFormat' => '{date}-{filename}',
			'rotateDirectoryFormat' => 'Y/m/d',
			'datetimeFormat' => 'Y-m-d H:i:s',
			'lineFormat' => "%datetime%: %channel%.%level_name%\n\t%message%\n\t%context%\n\t%extra%\n",
			'logLevel' => 'DEBUG',
			'handlers' => [
				'\Monolog\Handler\StreamHandler',
			],
			'processors' => ['\Monolog\Processor\WebProcessor']
		],
		'core-error' => [
			'path' => $_SERVER['DOCUMENT_ROOT'] . '/logs',
			'filename' => 'errors.log',
			'rotateFiles' => true,
			// example: ./logs/2018/06/08-errors.log
			'rotateFilenameFormat' => '{date}-{filename}',
			'rotateDirectoryFormat' => 'Y/m/d',
			'datetimeFormat' => 'Y-m-d H:i:s',
			'lineFormat' => "%datetime%: %channel%.%level_name%\n\t%message%\n\t%context%\n\t%extra%\n",
			'logLevel' => 'DEBUG',
			'handlers' => [
				'\Monolog\Handler\StreamHandler',
			],
			'processors' => ['\Monolog\Processor\WebProcessor']
		]
	],
	// Define a database connection parameters
	// 'databases' => [
	// 	// 'sample' => [
	// 	// 	'type' => 'pgsql or mysql',
	// 	// 	'host' => 'localhost',
	// 	// 	'username' => 'YOUR USERNAME',
	// 	// 	'password' => 'YOUR PASSWORD',
	// 	// 	'database' => 'YOUR DATABASE',
	// 	// ]
	// ],
];