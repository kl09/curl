<?php
namespace EacLibrary\RequestTrait;

trait RequestTrait
{
    private $maxTimeOut = 10;
    private $statusCode;
    private $response;
    private $method = "GET";
    private $body;
    private $headers = [];
    private $addHeader = false;
    private $siteHost = 'http://example.org';
    private $url;
    private $timer;
    private $login = true;
    private $userName = 'userName';
    private $userPass = 'userPass';
    private $userAgent = "Mozilla/4.0";
    private $followLocation = true;

    public function __construct()
    {

    }

    /**
     * @return boolean
     */
    public function isFollowLocation()
    {
        return $this->followLocation;
    }

    /**
     * @param boolean $followLocation
     * @return $this
     */
    public function setFollowLocation($followLocation)
    {
        $this->followLocation = $followLocation;

        return $this;
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
     * @return boolean
     */
    public function isLogin()
    {
        return $this->login;
    }

    /**
     * @param boolean $login
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @param string $userPass
     * @return $this
     */
    public function setUserPass($userPass)
    {
        $this->userPass = $userPass;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return $this;
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
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

        if ($this->addHeader == true && is_array($this->headers)) {
            $headers = array_merge([
                'Accept: text/html,application/json',
                'Accept-Language: ru-RU,ru',
                'Host:' . $_SERVER['HTTP_HOST'],
                'Content-Length: ' . strlen($this->body)
            ], $this->headers);

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->maxTimeOut);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->followLocation);

        if ($this->login) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->userName . ":" . $this->userPass);
        }

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