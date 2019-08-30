<?php
namespace ounun;

class restful  extends \v
{
    protected $_class;
    protected $_method;
    protected $_request_gets;
    protected $_request_post;
    protected $_request_inputs;

    protected $_http_accept;

    protected $_http_version = 'HTTP/1.1';

    public function __construct($mod)
    {
        $this->_method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->_http_accept  = strtolower($_SERVER['HTTP_ACCEPT']);
        $this->_request_gets = $_GET;
        $this->_request_post = $_POST;
        $data = file_get_contents('php://input');
        if($data){
            $this->_request_inputs = json_decode_array($data);
        }
        if($this->_class){
            if (!$mod) {
                $mod = [\ounun\config::def_method];
            }
            $class = "{$this->_class}\\{$mod[0]}";
            if(class_exists($class)){
                \ounun\config::$view = $this;
                new $class($mod,$this);
            }else{
                parent::__construct($mod);
            }
        }
    }

    public function set_headers(string $contentType, int $statusCode)
    {
        $Http_Status_Message = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => '资源有空表示(No Content)',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => '资源的URI已被更新(Moved Permanently)',
            302 => 'Found',
            303 => '其他（如，负载均衡）(See Other)',
            304 => '资源未更改（缓存）(Not Modified)',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => '指代坏请求(Bad Request)',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => '资源不存在(Not Found)',
            405 => 'Method Not Allowed',
            406 => '服务端不支持所需表示(Not Acceptable)',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => '通用冲突(Conflict)',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => '通用错误响应(Internal Server Error)',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => '服务端当前无法处理请求(Service Unavailable)',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
        $statusMessage = $Http_Status_Message[$statusCode]??$Http_Status_Message[500];

        header($this->_http_version. ' ' . $statusCode  . ' ' . $statusMessage);
        header('Content-Type: '. $contentType. '; charset=utf-8');
    }

    public function gets_get($key = ''){
        if($key){
            return $this->_request_gets[$key];
        }
        return $this->_request_gets;
    }

    public function post_get($key = ''){
        if($key){
            return $this->_request_post[$key];
        }
        return $this->_request_post;
    }

    public function input_get($key = ''){
        if($key){
            return $this->_request_inputs[$key];
        }
        return $this->_request_inputs;
    }

    public function method_get(){
        return $this->_method;
    }

    public function out($rawData,int $statusCode = 200,string $requestContentType='') {

        $requestContentType = $requestContentType??$this->_http_accept;
        $this->set_headers($requestContentType, $statusCode);

        if(strpos($requestContentType,'application/json') !== false){
            $response = $this->encode_json($rawData);
        } else if(strpos($requestContentType,'text/html') !== false){
            $response = $this->encode_html($rawData);
        } else if(strpos($requestContentType,'application/xml') !== false){
            $response = $this->encode_xml($rawData);
        } else {
            $response = $this->encode_json($rawData);
        }
        exit($response);
    }

    public function encode_html($responseData) {
        if(is_array($responseData)){
            $htmlResponse = '<table style="border: darkcyan solid 1px;">';
            foreach($responseData as $key=>$value) {
                $htmlResponse .= "<tr><td>". $key. "</td><td>". $value. "</td></tr>";
            }
            $htmlResponse .= "</table>";
            return $htmlResponse;
        }
        return $responseData;
    }

    public function encode_json($responseData) {
        $jsonResponse = json_encode($responseData);
        return $jsonResponse;
    }

    public function encode_xml($responseData) {
        // 创建 SimpleXMLElement 对象
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><site></site>');
        foreach($responseData as $key=>$value) {
            $xml->addChild($key, $value);
        }
        return $xml->asXML();
    }
}
