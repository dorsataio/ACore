<?php
namespace Dorsataio\ACore\Handler;

/**
 * A wrapper for PHP json_encode and json_decode.
 *
 * Adapted from:
 * http://nitschinger.at/Handling-JSON-like-a-boss-in-PHP/
 */
class JsonHandler{

    private static $_instance = false;

    private function __construct(){}

	/**
	 * [$_messages description]
	 *
	 * @var array
	 */
    protected static $_messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    /**
     * [isJson description]
     *
     * @param string $json [description]
     *
     * @return boolean [description]
     */
    public static function isJson(string $json){
        return is_string($json) && is_array(json_decode($json, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     *
     * @param [type]
     * @param integer
     *
     * @return [type]
     */
    public static function encode($value, $options=0){
        $result = json_encode($value, $options);
        if($result)  {
            return $result;
        }
        throw new \RuntimeException(static::$_messages[json_last_error()]);
    }

    /**
     *
     * @param [type]
     * @param boolean
     *
     * @return [type]
     */
    public static function decode($json, $assoc=false){
        $result = json_decode($json, $assoc);
        if($result) {
            return $result;
        }
        throw new \RuntimeException(static::$_messages[json_last_error()]);
    }

    // Return the current or new instance of this class
    public static function getInstance(){
        if(self::$_instance === false){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}