<?

class RESTWork {
	private $method = 'GET';
	private $enity = '';
	private $version = '';

	public function __construct($version) {
		$this->$version = $version;
	}

	public function GET () {
		$this->method = 'GET';
		return $this;
	}

	public function POST () {
		$this->method = 'POST';
		return $this;
	}

	public function PATCH () {
		$this->method = 'PATCH';
		return $this;
	}

	public function DELETE () {
		$this->method = 'DELETE';
		return $this;
	}

	public function users ($params, $content) {
		echo "users work " . $this->method;
	}

	public function merchants ($params, $content) {
		echo "merchants work " . $this->method;
	}

	public function coupons ($params, $content) {
		echo "coupons work " . $this->method;
	}

    public function __call($name, $arguments)
    {
        return false;
    }

    public static function __callStatic($name, $arguments)
    {
        return false;
    }
}

class RequestWork {
	private $roorDir = '';
	private $method = '';
	private $uri = '';
	private $params;
	private $version = '';
	private $content = '';
	private $enity = '';
	private $code = 200;
	private $message = '';
	private $response;
	private $error = false;

	public function __construct($rootDir, $method, $uri) {
		$this->roorDir = $rootDir;
		$this->method = $method;
		$this->uri = $uri;
		$this->params[0] = '';
		$this->params[1] = '';
	}

	public function getMethod() {
		return $this->method;
	}

	public function getEnity() {
		return $this->enity;
	}

	public function getVersion() {
		return $this->version;
	}

	public function getContent() {
		return $this->content;
	}

	public function getParams() {
		return $this->params;
	}

	private function validateMethods() {
		if (
			empty($this->method) ||
			(
				$this->method != 'GET' &&
				$this->method != 'POST' &&
				$this->method != 'PATCH' &&
				$this->method != 'DELETE'
			)
		) {
			return false;
		}
		return true;
	}

	private function validateEnity() {
		if (
			empty($this->enity) ||
			(
				$this->enity != 'users' &&
				$this->enity != 'merchants' &&
				$this->enity != 'coupons'
			)
		) {
			return false;
		}
		return true;
	}

	public function init() {
		if (!$this->validateMethods()) {
			$this->message = 'Error: invalid method';
			$this->code = 400;
			$this->error();
			return false;
		}
		if ($this->method != 'GET') {
			$contentJSON = file_get_contents("php://input");
			if (!empty($contentJSON !== NULL)) {
				$this->content = json_decode($contentJSON);
				$errorDecode = json_last_error();
				if ($errorDecode != JSON_ERROR_NONE) {
					$this->message = 'JSON parse error ' . $errorDecode;
					$this->code = 400;
					$this->error();
					return false;
				}
			} else {
				$this->message = 'Error: empty content ';
				$this->code = 400;
				$this->error();
				return false;
			}
		}
		$path = str_replace($this->roorDir, '', $this->uri);
		$firstLetter = substr($path, 0, 1);
		$lastLetter = substr($path, -1, 1);
		if ($firstLetter == '/') {
		    $path = substr($path, 1);
		}
		if ($lastLetter == '/') {
		    $path = substr($path, -1 * strlen($path) , strlen($path) - 1);
		}
		$pathArray = explode('/',$path);
		if (isset($pathArray[0]) && isset($pathArray[1])) {
		    $this->version = $pathArray[0];
		    $this->enity = $pathArray[1];
		    if (isset($pathArray[2])) {
		    	$this->params[0] = $pathArray[2];
		    }
		    if (isset($pathArray[3])) {
		    	$this->params[1] = $pathArray[3];
		    }
		} else {
			$this->message = 'Error: invalid request';
			$this->code = 400;
			$this->error();
			return false;
		}
		if (!$this->validateEnity()) {
			$this->message = 'Error: invalid enity';
			$this->code = 400;
			$this->error();
			return false;
		}
		return true;
	}

	private function error() {
		$this->error = true;
	}

	public function isError() {
		return $this->error;
	}

	public function getResponse() {
		$this->response['message'] = $this->message;
		$this->response['error'] = $this->error;
		return json_encode($this->response);
	}
}

$requestScriptName = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestScriptDir = dirname($requestScriptName);

$requestWork = new RequestWork($requestScriptDir, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if(!$requestWork->init()) {
	exit($requestWork->getResponse());
}
    
$restwork = new RESTWork($requestWork->getVersion());
$restwork->{$requestWork->getMethod()}()->{$requestWork->getEnity()}($requestWork->getParams(), $requestWork->getContent());

echo '<pre>' .  var_export($requestWork,true) . '</pre>';
echo '<pre>' .  var_export($restwork,true) . '</pre>';


