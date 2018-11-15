<?php 
namespace Dorsataio\ACore\Handler;

/**
 * 
 */
class APIResponseHandler{

	private $_response = [];
	private $_statuses = ['success', 'fail', 'error'];
	private $_headers = [];

	public function __construct($name=''){
		$this->asProgram($name);
	}

	/**
	 * [__toString description]
	 *
	 * @return string [description]
	 */
	public function __toString(){
		return json_encode($this->_response);
	}

	/**
	 * [getBody description]
	 *
	 * @return [type] [description]
	 */
	public function getBody(){
		return $this->_response;
	}

	/**
	 * [getStatusCode description]
	 *
	 * @return [type] [description]
	 */
	public function getStatusCode(){
		return $this->_response['code'];
	}

	/**
	 * [asProgram description]
	 *
	 * @param string $name [description]
	 *
	 * @return [type] [description]
	 */
	public function asProgram(string $name){
		if(!empty($name)){
			$this->_response['program'] = $name;
		}

		return $this;
	}

	/**
	 * All went well, and (usually) some data was returned
	 *
	 * @method isSuccess
	 *
	 * @param  int       $statusCode [description]
	 *
	 * @return boolean               [description]
	 */
	public function isSuccess(int $statusCode=0){
		if(empty($statusCode))
			$statusCode = StatusCodeHandler::OK;
			$this->withStatuscode($statusCode);

		$this->_response['status'] = $this->_statuses[0];

		return $this;
	}

	/**
	 * There was a problem with the data submitted, or some pre-condition of the API 
	 * call wasn't satisfied
	 *
	 * @method hasFailed
	 *
	 * @param  int       $statusCode [description]
	 *
	 * @return boolean               [description]
	 */
	public function hasFailed(int $statusCode=0){
		if(empty($statusCode))
			$statusCode = StatusCodeHandler::BAD_REQUEST;
			$this->withStatuscode($statusCode);

		$this->_response['status'] = $this->_statuses[1];

		return $this;
	}

	/**
	 * An error occurred in processing the request, i.e. an exception was thrown
	 *
	 * @method hasError
	 *
	 * @param  int      $statusCode [description]
	 *
	 * @return boolean              [description]
	 */
	public function hasError(int $statusCode=0, array $error=array()){
		if(empty($statusCode))
			$statusCode = StatusCodeHandler::INTERNAL_SERVER_ERROR;
			$this->withStatuscode($statusCode);

		$this->_response['status'] = $this->_statuses[2];

		if(!empty($error)){
			$this->withError($error['title'], $error['detail'], $error['code']);
		}

		return $this;
	}

	public function withHeader(string $header, string $value){
		$this->_headers[$header] = $value;
		return $this;
	}

	/**
	 * Specify a HTTP status code
	 *
	 * @method withStatusCode
	 *
	 * @param  int            $statusCode [description]
	 *
	 * @return [type]                     [description]
	 */
	public function withStatusCode(int $statusCode=0, string $message=''){
		if(!empty($statusCode))
			$this->_response['code'] = $statusCode;
			$this->withMessage(StatusCodeHandler::getMessageForCode($statusCode));
			if(!empty($message))
				$this->withMessage($message);

		return $this;
	}

	/**
	 * [withMessage description]
	 *
	 * @param string $message [description]
	 *
	 * @return [type] [description]
	 */
	public function withMessage(string $message=''){
		if(!empty($message))
			$this->_response['message'] = $message;
	}

	/**
	 * [withData description]
	 *
	 * @param array  $data [description]
	 * @param string $key  [description]
	 *
	 * @return [type] [description]
	 */
	public function withData(array $data, string $key=''){
		if(!empty($key))
			$this->_response['data'][$key] = $data;
		else
			$this->_response['data'] = $data;

		return $this;
	}

	/**
	 * [withError description]
	 *
	 * @param string $title  [description]
	 * @param string $detail [description]
	 * @param string $code   [description]
	 *
	 * @return [type] [description]
	 */
	public function withError(string $title, string $detail='', string $code=''){
		if(!isset($this->_response['errors']))
			$this->_response['errors'] = [];

		array_push($this->_response['errors'], [
			'title' => $title,
			'detail' => $detail,
			'code' => $code
		]);

		if(isset($this->_response['data']))
			unset($this->_response['data']);

		return $this;
	}

	/**
	 * [outputWithStatusCode description]
	 *
	 * @param int|integer $statusCode [description]
	 *
	 * @return [type]      [description]
	 */
	public function outputWithStatusCode(int $statusCode = 0){
		$code = ($statusCode > 0 ? $statusCode : $this->getStatusCode());
		header('Content-Type: application/json');
		if(function_exists('http_response_code')){
			http_response_code($code);
		}else{
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . $code . ' ' . StatusCodeHandler::getMessageForCode($code));
		}
		if(!empty($this->_headers)){
			foreach($this->_headers as $header => $value){
				header("{$header}: {$value}");
			}
		}
		echo $this;
		exit(0);
	}
}