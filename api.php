<?

include_once($_SERVER['DOCUMENT_ROOT'] . '/api/Medoo.php');

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

use Medoo\Medoo;

// DEMO
class RConfig {
    static $dbType = 'mysql';
    static $dbName = 'dbname';
    static $dbServer = 'localhost';
    static $dbUser = 'dbuser';
    static $dbPassword = 'pass';
}

$database = new Medoo(array(
    'database_type' => RConfig::$dbType,
    'database_name' => RConfig::$dbName,
    'server' => RConfig::$dbServer,
    'username' => RConfig::$dbUser,
    'password' => RConfig::$dbPassword
));


/**
 * RESTWork
 *
 * This class processed with shop data (users, merchants and coupons).
 */
class RESTWork {
	private $method = 'GET';
	private $operator = '';
	private $enity = '';
	private $paramsList;
	private $params;
	private $methods;
	private $database;

    /**
     * Returns generated token.
     *
     * @return string
     */
	public function generateToken () {
		return uniqid('U'.mt_rand(0, PHP_INT_MAX), true);
	}

	public function __construct($databaseObj) {
		$this->database = $databaseObj;
		$this->methods['GET'] = 'select';
		$this->methods['POST'] = 'insert';
		$this->methods['PATCH'] = 'update';
		$this->methods['DELETE'] = 'delete';
	}

    /**
     * Set request method.
     *
     * @return Object RESTWork
     */
	public function setMethod ($method) {
		$this->method = $method;
		$this->operator = $this->methods[$method];
		switch ($method) {
			case 'GET':
				$this->paramsList = array('table', 'columns', 'where');
				break;
			case 'POST':
				$this->paramsList = array('table', 'data');
				break;
			case 'PATCH':
				$this->paramsList = array('table', 'data', 'where');
				break;
			case 'DELETE':
				$this->paramsList = array('table', 'where');
				break;
			default:
				$this->paramsList = array('table', 'columns', 'where');
				break;
		}
		return $this;
	}

    /**
     * Prepare content for enity "users" 
     *
     * @param array $params
     * @param array $content
     * @return object RESTWork
     */
	public function users ($params, $content) {
		$this->enity = 'users';
		$this->params['table'] = 'users';
		$this->params['columns'] = array('id', 'token', 'name', 'email', 'datetime'); //'password',
		if(isset($params[0]) && !empty($params[0])) {
			// SQL Injection filter
			$params[0] = preg_replace('/([^\.\w\d])/i', '', $params[0]);
			$this->params['where'] = array('token' => $params[0]);
		}
		$this->params['data'] = $content;
		if (!empty($content) && $this->operator == 'insert') {
			foreach ($this->params['data'] as &$row) {
				$row['id'] = NULL;
				$row['password'] = md5($row['password']);
				$row['token'] = $this->generateToken();
			}
		}
		return $this;
	}

    /**
     * Prepare content for enity "merchants" 
     *
     * @param array $params
     * @param array $content
     * @return object RESTWork
     */
	public function merchants ($params, $content) {
		$this->enity = 'merchants';
		$this->params['table'] = 'merchants';
		if($this->method != 'GET' && $this->method != 'DELETE') {
			return $this->error('You do not have permission to method: "' . $this->method . '" for "' . $this->enity . '" enity');
		}
		$this->params['columns'] = array('id', 'name', 'description');
		$this->params['where'] = array();
		if($this->method == 'GET') {

			
			$this->operator = 'query';
			$this->paramsList = array('query');
			$this->params['query'] = "SELECT `merchants`.`id`,`merchants`.`name`,`merchants`.`description`,`coupons`.`code` FROM `merchants` LEFT JOIN `relations` ON `merchants`.`id` = `relations`.`muid` LEFT JOIN `coupons` ON `coupons`.`id` = `relations`.`cid` AND `relations`.`type` = 'merchants' ORDER BY `merchants`.`id`";
		}
		if(isset($params[0]) && !empty($params[0])) {
			$this->params['where'] = array('id' => intval($params[0]));
		}
		return $this;
	}

    /**
     * Prepare content for enity "coupons" 
     *
     * @param array $params 
     * @param array $content
     * @return object RESTWork
     */
	public function coupons ($params, $content) {
		$this->enity = 'coupons';
		$this->params['table'] = 'coupons';
		// permisson to others methods
		if($this->method != 'GET') {
			return $this->error('You do not have permission to method: "' . $this->method . '" for "' . $this->enity . '" enity');
		}
		$this->params['where'] = array();
		$type = 'users';
		if(isset($params[0]) && !empty($params[0]) && isset($params[1]) && !empty($params[1])) {
			if ($params[0] == 'mid') {
				$type = 'merchants';
			}
			if ($params[0] == 'uid') {
				$type = 'users';
			}
			$id = intval($params[1]);
		}
		
		$this->operator = 'query';
		$this->paramsList = array('query');
		$this->params['query'] = "SELECT `coupons`.`id`,`coupons`.`name`,`coupons`.`code` as 'coupon', `" . $type ."`.`name` as '" . $type ."' FROM `coupons` LEFT JOIN `relations` ON `coupons`.`id` = `relations`.`cid` LEFT JOIN `" . $type ."` ON `" . $type ."`.`id` = `relations`.`muid` AND `relations`.`type` = '" . $type ."' WHERE `" . $type ."`.`id` = " . $id . " ORDER BY `coupons`.`id`";		
		return $this;
	}

	static function debug ($var) {
		echo '<pre>' .  var_export($var,true) . '</pre>';
	}

    /**
     * Get content from dataBase in JSON format
     *
     * @var operator string - Medoo (BD framework) Class method
     * @var database object - Medoo object
     * @var params array - list of params for Medoo Class method
     * @var paramsList array - Medoo Class method order List
     *
     * @return string JSON
     */
	public function exec () {
		if (empty($this->database)) {
			return $this->error('DB var is NULL');
		}
		if (empty($this->method)) {
			return $this->error('method var is NULL');
		}
		if (empty($this->enity)) {
			return $this->error('enity var is NULL');
		}
		$paramsNumber = count($this->paramsList);
		switch ($paramsNumber) {
			case 1:
				$result = $this->database->{$this->operator}(
					$this->params[$this->paramsList[0]]
					);
				if ($this->operator == 'query') {
					$result = $result->fetchAll(PDO::FETCH_ASSOC);
				}
				break;
			case 2:
				$result = $this->database->{$this->operator}(
					$this->params[$this->paramsList[0]],
					$this->params[$this->paramsList[1]]
					);
				break;
			case 3:
				$result = $this->database->{$this->operator}(
					$this->params[$this->paramsList[0]],
					$this->params[$this->paramsList[1]],
					$this->params[$this->paramsList[2]]
					);
				break;
			case 4:
				$result = $this->database->debug()->{$this->operator}(
					$this->params[$this->paramsList[0]],
					$this->params[$this->paramsList[1]],
					$this->params[$this->paramsList[2]],
					$this->params[$this->paramsList[3]]
					);
				break;
		}
		if (is_array($result)) {
			return json_encode($result);
		} elseif (is_string($result)) {
			return $result;
		} else {
			return json_encode(array('Message' => 'OK' ,'rowCount' => $result->rowCount()));
		}
	}

    /**
     * Stub 
     *
     * @param string $name 
     * @param array $arguments
     * @return object RESTWork
     */
    public function __call($name, $arguments)
    {
       return $this;
    }

    /**
     * Stub 
     *
     * @param string $name 
     * @param array $arguments
     * @return object RESTWork
     */
    public static function __callStatic($name, $arguments)
    {
        return $this;
    }

    /**
     * Set error message 
     *
     * @param string $msg 
     * @return string JSON
     */
	private function error ($msg = 'Invalid invoke methods') {
		return json_encode(array('message' => $msg, 'status' => 'error'));
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
	private $message = '';
	private $response;
	private $status = 'OK';

	public function __construct($rootDir, $method, $uri) {
		$this->roorDir = $rootDir;
		$this->method = $method;
		$this->uri = $uri;
		$this->params[0] = '';
		$this->params[1] = '';
	}
  
    /**
     * Get class var.
     *
     * @return string method from request
     */
	public function getMethod() {
		return $this->method;
	}

    /**
     * Get class var.
     *
     * @return string enity
     */
	public function getEnity() {
		return $this->enity;
	}

    /**
     * Get class var.
     *
     * @return string api version
     */
	public function getVersion() {
		return $this->version;
	}

    /**
     * Get class var.
     *
     * @return array JSON parsed data from input
     */
	public function getContent() {
		return $this->content;
	}

    /**
     * Get class var.
     *
     * @return array params from URI path (is not GET params)
     */
	public function getParams() {
		return $this->params;
	}

    /**
     * Check method from true list.
     *
     * @return bool check result
     */
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

    /**
     * Check enity from true list.
     *
     * @return bool check result
     */
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

    /**
     * Inicialisation request data and set class vars.
     *
     * @return bool true (when everything is fine) and false in other cases
     */
	public function init() {
		if (!$this->validateMethods()) {
			return $this->error('Error: invalid method');
		}
		if ($this->method != 'GET') {
			$contentJSON = file_get_contents("php://input");
			if (!empty($contentJSON !== NULL)) {
				$this->content = json_decode($contentJSON, true);
				$errorDecode = json_last_error();
				if ($errorDecode != JSON_ERROR_NONE) {
					return $this->error('JSON parse error ' . $errorDecode);
				}
			} else {
				return $this->error('Error: empty content');
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
			return $this->error('Error: invalid request');
		}
		if (!$this->validateEnity()) {
			return $this->error('Error: invalid enity');
		}
		if ($this->version != 'v1') {
			return $this->error('Error: invalid version');
		}
		return true;
	}

    /**
     * Set error status and return false for init() method faulty.
     *
     * @param string $errorMsg - message for output
     * @return bool false
     */
	private function error($errorMsg='Error') {
		$this->message = $errorMsg;
		$this->status = 'error';
		return false;
	}

    /**
     * Returns JSON.
     *
     * @param string $module_id
     * @param bool $bModuleInclude
     * @return string JSON Respon
     */
	public function getResponse() {
		$this->response['message'] = $this->message;
		$this->response['status'] = $this->status;
		return json_encode($this->response);
	}
}

$requestScriptName = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
$requestScriptDir = dirname($requestScriptName);

$requestWork = new RequestWork($requestScriptDir, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if(!$requestWork->init()) {
	exit($requestWork->getResponse());
}

$restwork = new RESTWork($database);
$result = $restwork->setMethod($requestWork->getMethod())->{$requestWork->getEnity()}($requestWork->getParams(), $requestWork->getContent())->exec();
echo $result;

