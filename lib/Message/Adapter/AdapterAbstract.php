<?php

namespace Message\Adapter;

abstract class AdapterAbstract
{
    protected $curl;

    protected $notify_url;

    protected $token;

    protected $title;

    protected $description;

    protected $url;

    protected $curl_options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_POST => true,
    );

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        foreach ($options AS $name => $value) {
            if (false !== strpos($name, "_")) {
                $method = "set".str_replace(" ", "", ucwords(str_replace("_", " ", $name)));
            } else {
                $method = "set".ucfirst($name);
            }
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
    * @return mixed
    */
    public function getCurl(array $options = array())
    {
        if (!$this->curl) {
            $this->curl = curl_init();
            if ($this->curl_options) {
                curl_setopt_array($this->curl, $this->curl_options);
            }
            if ($this->notify_url) {
                curl_setopt($this->curl, CURLOPT_URL, $this->notify_url);
            }
        }
        if ($options) {
            curl_setopt_array($this->curl, $options);
        }
        return $this->curl;
    }

    public function __destruct()
    {
        if ($this->curl) {
            curl_close($this->curl);
        }
    }

    /**
    * @param string $url
    * @return Message_Abstract
    */
    public function setNotifyUrl($url)
    {
        $this->notify_url = $url;
        return $this;
    }

    /**
    * @return string
    */
    public function getNotifyUrl()
    {
        return $this->notify_url;
    }

    /**
    * @param string $token
    * @return Message_Abstract
    */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
    * @return string
    */
    public function getToken()
    {
        return $this->token;
    }

    /**
    * @param string $title
    * @return Message_Abstract
    */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
    * @return string
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * @param string $description
    * @return Message_Abstract
    */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
    * @return string
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
    * @param string $url
    * @return Message_Abstract
    */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
    * @return string
    */
    public function getUrl()
    {
        return $this->url;
    }


    abstract public function send($message);
}