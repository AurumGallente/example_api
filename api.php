<?php
// ENCODING AUTH FUNCTION
function ejor2eb($object, $key) {
    
    $object = json_encode($object, JSON_FORCE_OBJECT);

    
    $object = strlen($object) . ':' . $object;

    
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
    $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $object, MCRYPT_MODE_ECB, $iv);

    
    return 'jor2eu:' . base64_encode($result);
}
// DECODING AUTH FUNCTION
function djor2eb($string, $key, $default = false) {
    
    $binary = base64_decode(substr($string, 7));
    if (!$binary) {
        return $default;
    }

    
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
    $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $binary, MCRYPT_MODE_ECB, $iv);

    
    $tokens = null;
    preg_match('/^([0-9]+):/i', $result, $tokens);
    if (sizeof($tokens) !== 2) {
        return $default;
    }
    $result = substr($result, strlen($tokens[1]) + 1, $tokens[1]);

    
    $object = json_decode($result);

    return $object;
}

$secret_key = 'my_secret_key';

$headers = getallheaders();

class api {

    public static $bad_request = false;
    protected $method = '';
    protected $endpoint = '';
    protected $args = Array();
    protected $current_user;
    protected $entity;
    protected $allowed_methods = array('GET', 'POST', 'PUT', 'DELETE');
    public static $status = '404';
    public static $response = '';

    public static function dispatch() {
        http_response_code(self::$status);
        echo self::$response;
    }

    public function __construct($entity) {
        $this->entity = $entity;
        // CORS
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
        //GETTING ARGUMENTS
        $this->args = isset($_REQUEST['path']) ? explode('/', rtrim($_REQUEST['path'], '/')) : array();
        //GETTING ENDPOINT
        $this->endpoint = isset($_REQUEST['path']) ? array_shift($this->args) : '';
        //GETTING METHOD
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    protected function is_allowed_method() {
        if (!in_array($this->method, $this->allowed_methods)) {
            api::$bad_request = true;
        }
    }

    //REST METHODS
    public function getOne($cb) {
        if ($this->method !== 'GET' || !isset($this->args[0]) || $this->entity !== $this->endpoint) {
            return;
        }
        $cb($this->args[0]);
    }

    public function getAll($cb) {
        if ($this->method !== 'GET' || isset($this->args[0]) || $this->entity !== $this->endpoint) {
            return;
        }
        $cb();
    }

    public function create($cb) {
        if ($this->method !== 'POST' || $this->entity !== $this->endpoint) {
            return;
        }
        $cb(json_decode(file_get_contents('php://input')));
    }

    public function edit($cb) {
        if ($this->method !== 'PUT' || !isset($this->args[0]) || $this->entity !== $this->endpoint) {
            return;
        }
        $cb($this->args[0], json_decode(file_get_contents('php://input')));
    }

    public function delete($cb) {
        if ($this->method !== 'DELETE' || !isset($this->args[0]) || $this->entity !== $this->endpoint) {
            return;
        }
        $cb($this->args[0]);
    }

}
class current_user {
    public static function get_user() {
        $headers = getallheaders();
        global $secret_key;
        return isset($headers['authorization']) ? djor2eb($headers['authorization'], $secret_key) : array();
    }
}
$current_user = current_user::get_user();

