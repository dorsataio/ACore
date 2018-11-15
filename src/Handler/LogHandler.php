<?php 
namespace Dorsataio\ACore\Handler;

// Logging Library
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

/**
 * A Monolog wrapper
 */
class LogHandler{

	// An array of different loggers loggers
	private static $_loggers = [];
	// Default logger configurations
	public static $defaults = [
		'path' => './logs',
		'filename' => 'errors.log',
		'rotateFiles' => true,
		// example: ./logs/2018/06/08-errors.log
		'rotateFilenameFormat' => '{date}-{filename}',
		'rotateDirectoryFormat' => 'Y/m/d',
		'datetimeFormat' => 'Y-m-d H:i:s',
		'lineFormat' => "%datetime%: %channel%.%level_name%\n\t%message%\n\t%context%\n\t%extra%\n",
		'logLevel' => 'DEBUG',
		// 'handler' => 'Monolog\Handler\StreamHandler',
		'processors' => ['\Monolog\Processor\WebProcessor']
	];

	/**
	 * Return an existing logger channel (name) or create a new logger with defaults
	 *
	 * @method getLogger
	 *
	 * @param  string    $name A logger name (channel)
	 *
	 * @return object 			A Monolog logger object
	 */
	public static function getLogger(string $name){
		if(!isset(self::$_loggers[$name])){
			self::$_loggers[$name] = self::newLogger($name, self::$defaults);
		}
		return self::$_loggers[$name];
	}

	/**
	 * Create a new Monolog logger
	 *
	 * @method newLogger
	 *
	 * @param  string    $name     A logger name (channel)
	 * @param  array     $settings A array of logger settings
	 *
	 * @return object 				A Monolog logger object
	 */
	public static function newLogger(string $name, array $settings){
		$logger = new Logger($name);
		$formatter = null;
		if(isset($settings['lineFormat']) && !empty($settings['lineFormat'])){
			$dateFormat = null;
			if(isset($settings['datetimeFormat']) && !empty($settings['datetimeFormat'])){
				$dateFormat = $settings['datetimeFormat'];
			}
			$formatter = new \Monolog\Formatter\LineFormatter($settings['lineFormat'], $dateFormat);
		}
		$handler = new \Monolog\Handler\StreamHandler("{$settings['path']}/{$settings['filename']}", Logger::DEBUG);
		if(isset($settings['rotateFiles']) && $settings['rotateFiles'] === true){
			$handler = new \Monolog\Handler\RotatingFileHandler("{$settings['path']}/{$settings['filename']}", 0, Logger::DEBUG);
			$rotateFilenameFormat = null;
			$rotateDirectoryFormat = null;
			if(isset($settings['rotateFilenameFormat']) && !empty($settings['rotateFilenameFormat'])){
				$rotateFilenameFormat = $settings['rotateFilenameFormat'];
			}
			if(isset($settings['rotateDirectoryFormat']) && !empty($settings['rotateDirectoryFormat'])){
				$rotateDirectoryFormat = $settings['rotateDirectoryFormat'];
			}
			$handler->setFilenameFormat($rotateFilenameFormat, $rotateDirectoryFormat);
		}
		if($formatter){
			$handler->setFormatter($formatter);
		}
		$logger->pushHandler($handler);
		if(is_array($settings['processors'])){
			foreach($settings['processors'] as $processor){
				if(is_callable($processor)){
					$handler->pushProcessor($processor);
					continue;
				}
				$handler->pushProcessor(new $processor());
			}
		}
		// 
		self::$_loggers[$name] = $logger;
		return self::$_loggers[$name];
	}
}