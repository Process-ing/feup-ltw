<?php
declare(strict_types=1);

require_once __DIR__ . '/Session.php';

/**
 * @brief Encapsulates superglobals when requests are created
 */
class Request
{
    private array $getParams;
    private array $postParams;
    private array $putParams;
    private array $patchParams;
    private array $deleteParams;
    private array $cookies;
    private array $headers;
    private array $files;
    private Session $session;

    public function __construct()
    {
        $this->getParams = $_GET;
        $this->postParams = $_POST;

        $this->putParams = [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT')
            parse_str(file_get_contents('php://input'), $this->putParams);

       	$this->patchParams = [];
        if ($_SERVER['REQUEST_METHOD'] === 'PATCH')
            parse_str(file_get_contents('php://input'), $this->patchParams);        

        $this->deleteParams = [];
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE')
            parse_str(file_get_contents('php://input'), $this->deleteParams);

        $this->cookies = $_COOKIE;
        $this->headers = $_SERVER;
        $this->files = $_FILES;
        $this->session = new Session();
    }

    private static function sanitize(string|array|null $data): array|string|null {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = Request::sanitize($value);
            }
            return $sanitized;
        }
        if ($data === null)
            return null;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function get($key, $default = null)
    {
        return Request::sanitize($this->getParams[$key]) ?? $default;
    }

    public function post($key, $default = null)
    {
        return Request::sanitize($this->postParams[$key]) ?? $default;
    }

    public function put($key, $default = null)
    {
        return Request::sanitize($this->putParams[$key]) ?? $default;
    }

    public function patch($key, $default = null)
    {
        return Request::sanitize($this->patchParams[$key]) ?? $default;
    }

    public function delete($key, $default = null)
    {
        return Request::sanitize($this->deleteParams[$key]) ?? $default;
    }

    public function cookie($key, $default = null)
    {
        return Request::sanitize($this->cookies[$key]) ?? $default;
    }

    public function header($key)
    {
        return $this->headers[$key] ?? null;
    }

    public function files($key)
    {
        return $this->files[$key] ?? null;
    }

    public function session($key)
    {
        if (!$this->verifyCsrf())
            return false;
        return $this->session->get($key);
    }

    public function getSession() : Session
    {
        return $this->session;
    }

    public function verifyCsrf() : bool
    {
        switch ($this->getMethod()) {
            case 'GET':
                $csrf = $this->get('csrf');
                break;
            case 'POST':
                $csrf = $this->post('csrf');
                break;
            case 'PUT':
                $csrf = $this->put('csrf');
                break;
            case 'PATCH':
                $csrf = $this->patch('csrf');
                break;
            case 'DELETE':
                $csrf = $this->delete('csrf');
                break;
            default:
                return false;
        }
        return $csrf === $this->session->getCsrf();
    }

    public function paramsExist(array $params) : bool
    {
        switch ($this->getMethod()) {
            case 'GET':
                $array = $this->getParams;
                break;
            case 'POST':
                $array = $this->postParams;
                break;
            case 'PUT':
                $array = $this->putParams;
                break;
            case 'PATCH':
                $array = $this->patchParams;
                break;
            case 'DELETE':
                $array = $this->deleteParams;
                break;
            default:
                return false;
        }

        foreach ($params as $param) {
            if (!isset($array[$param]))
                return false;
        }
        return true;
    }

    function getMethod(): string {
        return $this->header('REQUEST_METHOD');
    }

    function getEndpoint(): string {
        return $this->header('PATH_INFO');
    }

    function getServerHost(): string {
        return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $this->header('HTTP_HOST');
    }
}
