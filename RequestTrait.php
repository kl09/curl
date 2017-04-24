<?php
namespace EacLibrary\RequestTrait;

trait RequestTrait
{
    private $maxTimeOut = 10;
    private $statusCode;
    private $response;
    private $method = "GET";
    private $body;
    private $headers;
    private $addHeader = false;
    private $siteHost = 'http://example.org';
    private $url;
    private $timer;


    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getMaxTimeOut()
    {
        return $this->maxTimeOut;
    }

    /**
     * @param $maxTimeOut
     * @return $this
     */
    public function setMaxTimeOut($maxTimeOut)
    {
        $this->maxTimeOut = $maxTimeOut;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAddHeader()
    {
        return $this->addHeader;
    }

    /**
     * @param boolean $addHeader
     * @return $this
     */
    public function setAddHeader($addHeader)
    {
        $this->addHeader = $addHeader;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteHost()
    {
        return $this->siteHost;
    }

    /**
     * @param string $siteHost
     * @return $this
     */
    public function setSiteHost($siteHost)
    {
        $this->siteHost = $siteHost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
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


    public static function response()
    {
        return new self();
    }


    public function execute()
    {
        $re = '/^(https:\/\/|http:\/\/)(.*)$/';
        preg_match_all($re, $this->url, $matches, PREG_SET_ORDER, 0);
        if (!isset($matches[0])) {
            $this->url = $this->siteHost . $this->url;
        }

        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);

        if ($this->addHeader == true) {
            $headers = array_merge([
                'Accept: text/html,application/json',
                'Accept-Language: ru-RU,ru',
                'Host:' . $_SERVER['HTTP_HOST'],
                'Content-Length: ' . strlen($this->body),
            ], $this->headers);

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->maxTimeOut);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($this->body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->body);
        }

        $response = curl_exec($curl);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $this->timer = curl_getinfo($curl, CURLINFO_TOTAL_TIME);

        $this->response = substr($response, $header_size);
        $this->headers = substr($response, 0, $header_size);

        $this->statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $this;
    }

}