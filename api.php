<?

include_once($_SERVER['DOCUMENT_ROOT'] . '/api/Medoo.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Medoo\Medoo;

// DEMO
class RConfig {
    static $dbType = 'mysql';
    static $dbName = 'resag464_testrestapi';
    static $dbServer = 'localhost';
    static $dbUser = 'resag464_testrestapi';
    static $dbPassword = '123qwe123';
}

$database = new Medoo(array(
    'database_type' => RConfig::$dbType,
    'database_name' => RConfig::$dbName,
    'server' => RConfig::$dbServer,
    'username' => RConfig::$dbUser,
    'password' => RConfig::$dbPassword
));

//////$data = $database->select('users', '*');

//select($table, $columns, $where)
//select($table, $join, $columns, $where)
//insert($table, $data)
//update($table, $data, $where)
//delete($table, $where)

/*
$data = $database->select('users', [
    'user_name',
    'email'
], [
    'user_id' => 50
]);
*/

/////echo json_encode($data);

class RESTWork {
	private $method = 'GET';
	private $operator = '';
	private $enity = '';
	private $paramsNumber;
	private $paramsList;
	private $params;
	private $content;
	private $database;

	public function generateToken () {
		return uniqid('U'.mt_rand(0, PHP_INT_MAX), true);
	}

	public function __construct($databaseObj) {
		$this->database = $databaseObj;
	}

	public function GET () {
		$this->method = 'GET';
		$this->operator = 'select';
		$this->paramsNumber = 3;
		$this->paramsList = array('table', 'columns', 'where');
		return $this;
	}

	public function POST () {
		$this->method = 'POST';
		$this->operator = 'insert';
		$this->paramsNumber = 2;
		$this->paramsList = array('table', 'data');
		return $this;
	}

	public function PATCH () {
		$this->method = 'PATCH';
		$this->operator = 'update';
		$this->paramsNumber = 3;
		$this->paramsList = array('table', 'data', 'where');
		return $this;
	}

	public function DELETE () {
		$this->method = 'DELETE';
		$this->operator = 'delete';
		$this->paramsNumber = 2;
		$this->paramsList = array('table', 'where');
		return $this;
	}

	public function users ($params, $content) {
		$this->enity = 'users';
		$this->params['table'] = 'users';
		$this->params['columns'] = array('id', 'token', 'name', 'email', 'datetime'); //'password',
		if(isset($params[0]) && !empty($params[0])) {
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
			$this->paramsNumber = 1;
			$this->paramsList = array('query');
			$this->params['query'] = "SELECT `merchants`.`id`,`merchants`.`name`,`merchants`.`description`,`coupons`.`code` FROM `merchants` LEFT JOIN `relations` ON `merchants`.`id` = `relations`.`muid` LEFT JOIN `coupons` ON `coupons`.`id` = `relations`.`cid` AND `relations`.`type` = 'merchants' ORDER BY `merchants`.`id`";
			

/*
			//$this->params['table'] = 'relations';
			$this->paramsNumber = 4;
			$this->paramsList = array('table', 'join', 'columns', 'where');
			$this->params['columns'] = array(
				'merchants.id',
				'merchants.name',
				'merchants.description',
				'coupons.code'
			);
			// LEFT JOIN API
			$this->params['join'] = array(
				'[<]merchants' => array('id' => 'id'),
				'[>]coupons' => array('cid' => 'id'),
			);
			$this->params['join'] = array(
				'[>]relations' => array('id' => 'muid', 'coupons.id' => 'cid', 'type' => "'merchants'"),
				'[>]coupons' => array('id'),
			);
			*/
			//$this->params['where'] = array('relations.type' => 'merchants');
			
		}
		if(isset($params[0]) && !empty($params[0])) {
			$this->params['where'] = array('id' => $params[0]);
		}
		return $this;
	}

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
		}

		//mysqli::real_escape_string
		$this->operator = 'query';
		$this->paramsNumber = 1;
		$this->paramsList = array('query');
		$this->params['query'] = "SELECT `coupons`.`id`,`coupons`.`name`,`coupons`.`code` as 'coupon', `" . $type ."`.`name` as '" . $type ."' FROM `coupons` LEFT JOIN `relations` ON `coupons`.`id` = `relations`.`cid` LEFT JOIN `" . $type ."` ON `" . $type ."`.`id` = `relations`.`muid` AND `relations`.`type` = '" . $type ."' ORDER BY `coupons`.`id`";		
		return $this;
	}

	static function debug ($var) {
		echo '<pre>' .  var_export($var,true) . '</pre>';
	}

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
		if (count($this->paramsList) < $this->paramsNumber) {
			return $this->error('paramsList number more then paramsNumber');
		}
		self::debug($this->params);
		switch ($this->paramsNumber) {
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
		//$account_id = $database->id(); //
		if (is_array($result)) {
			return json_encode($result);
		} else {
			return json_encode(array('status' => 'OK' ,'rowCount' => $result->rowCount()));
		}
	}

    public function __call($name, $arguments)
    {
       return $this;
    }

    public static function __callStatic($name, $arguments)
    {
        return $this;
    }

	private function error ($msg = 'Invalid invoke methods') {
		return array('message' => $msg, 'error' => true);
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
	private $message = 'OK';
	private $response;
	private $error = false;

	public function __construct($rootDir, $method, $uri) {
		$this->roorDir = $rootDir;
		$this->method = $method;
		$this->uri = $uri;
		$this->params[0] = '';
		$this->params[1] = '';
	}

    /**
     * Returns module database connection.
     * Can be used only if module supports sharding.
     *
     * @param string $module_id
     * @param bool $bModuleInclude
     * @return bool|CDatabase
     */
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
				$this->content = json_decode($contentJSON, true);
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
		//var_export($pathArray);
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
		if ($this->version != 'v1') {
			$this->message = 'Error: invalid version';
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

$requestScriptName = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
$requestScriptDir = dirname($requestScriptName);

$requestWork = new RequestWork($requestScriptDir, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if(!$requestWork->init()) {
	exit($requestWork->getResponse());
}

//echo 'result=<pre>' .  var_export($requestWork->getEnity(),true) . '</pre><BR><BR>'."\n\n";

$restwork = new RESTWork($database);
$result = $restwork->{$requestWork->getMethod()}()->{$requestWork->getEnity()}($requestWork->getParams(), $requestWork->getContent())->exec();


RESTWork::debug($result);
//RESTWork::debug($requestWork);
//RESTWork::debug($restwork);


