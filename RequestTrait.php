<?php
namespace EacLibrary\RequestTrait;

trait RequestTrait
{
    public static $maxTimeOut = 2;
    public static $sid;
    public static $siteName;
    private $statusCode;
    private $response;
    private $headers;
    private $url;
    private $timer;


    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return float
     */
    public function getTimer()
    {
        return $this->timer;
    }


    /**
     * @param bool $jsonDecode
     * @return mixed
     */
    public function getResponse($jsonDecode = true)
    {
        if ($jsonDecode == true) {
            $result = json_decode($this->response, true);
        } else {
            $result = $this->response;
        }

        return $result;
    }


    /**
     * CURL Request
     *
     * @param string $path
     * @param string $method
     * @param string $body
     * @param array $headers
     * @return static
     */
    public static function request($path = null, $method = 'GET', $body = null, $headers = [])
    {
        $o = new self();
        $o->url = 'http://' . str_replace('//', '/', self::$siteName . '/' . $path);
        $sid = self::$sid;

        $curl = curl_init($o->url);
        $headers = array_merge([
            'Accept: text/html,application/json',
            'Accept-Language: ru-RU,ru',
            'Host:' . $_SERVER['HTTP_HOST'],
            'Content-Length: ' . strlen($body),
        ], $headers);

        if ($sid) {
            $headers[] = sprintf('Cookie: %s=%s', session_name(), $sid);
        }

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::$maxTimeOut);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $timer = microtime(true);

        $response = $o->parseHeaders(curl_exec($curl));
        $o->timer = microtime(true) - $timer;
        $o->response = $response->body;
        $o->headers = $response->headers;
        $o->statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $o;
    }

    protected function parseHeaders($curlResponse)
    {
        $response = new \stdClass();
        $headers = [];

        $response->body = substr($curlResponse, strpos($curlResponse, "\r\n\r\n") + 4);
        $headersText = substr($curlResponse, 0, strpos($curlResponse, "\r\n\r\n"));

        foreach (explode("\r\n", $headersText) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                if (array_key_exists($key, $headers) || $key == 'Set-Cookie') {
                    $headers[$key] = (array)$headers[$key];
                    $headers[$key][] = $value;
                } else {
                    $headers[$key] = $value;
                }
            }
        }
        $response->headers = $headers;

        return $response;
    }
}