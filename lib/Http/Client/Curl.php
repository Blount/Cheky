<?php

require_once __DIR__."/Abstract.php";

class HttpClientCurl extends HttpClientAbstract
{
    protected $_resource;

    public function __construct()
    {
        $this->_resource = curl_init();
        curl_setopt($this->_resource, CURLOPT_HEADER, false);
        curl_setopt($this->_resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_resource, CURLOPT_CONNECTTIMEOUT, 5);
    }

    public function request($url = null)
    {
        if (!$this->_url && !$url) {
            throw new Exception("Aucune URL à appeler.");
        }
        if ($url) {
            $this->setUrl($url);
        }
        if (!isset($this->_method) || $this->_method == self::METHOD_GET) {
            curl_setopt($this->_resource, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($this->_resource, CURLOPT_POST, true);
        }
        if ($this->_proxy_ip) {
            if ($this->getProxyType() == self::PROXY_TYPE_WEB) {
                $url = $this->_proxy_ip.urlencode($this->getUrl());
            } else {
                curl_setopt($this->_resource, CURLOPT_PROXY, $this->_proxy_ip);
                if (!$this->_proxy_type || $this->_proxy_type == self::PROXY_TYPE_HTTP) {
                    curl_setopt($this->_resource, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                } else {
                    curl_setopt($this->_resource, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                }
                if ($this->_proxy_port) {
                    curl_setopt($this->_resource, CURLOPT_PROXYPORT, $this->_proxy_port);
                }
                if ($this->_proxy_user) {
                    curl_setopt($this->_resource, CURLOPT_PROXYUSERPWD, $this->_proxy_user.":".$this->_proxy_password);
                }
            }
        }
        if ($userAgent = $this->getUserAgent()) {
            curl_setopt($this->_resource, CURLOPT_USERAGENT, $userAgent);
        }
        curl_setopt($this->_resource, CURLOPT_NOBODY, !$this->_download_body);
        curl_setopt($this->_resource, CURLOPT_URL, $url);
        $body = curl_exec($this->_resource);
        $this->_respond_code = curl_getinfo($this->_resource, CURLINFO_HTTP_CODE);

        $content_type = curl_getinfo($this->_resource, CURLINFO_CONTENT_TYPE);
        if (preg_match("#.*charset=(.+)#ims", $content_type, $m)) {
            $charset = trim($m[1], " '\"");
            $body = iconv($charset, "utf-8", $body);
        }

        if ($this->_respond_code == 301 || $this->_respond_code == 302) {
            $redirect = curl_getinfo($this->_resource, CURLINFO_REDIRECT_URL);
            $this->setLocation($redirect);
            if ($redirect && $this->getFollowLocation()) {
                return $this->request($redirect);
            }
        }

        if ($this->getProxyType() == self::PROXY_TYPE_WEB) {
            // restaure les vrai URL
            if (preg_match_all("#<(?:img|a)[^>]+(?:src|href)=(?:'|\")(https?://.*(http[^'\"]+))(?:'|\")[^>]*/?>#ismU", $body, $matches,  PREG_SET_ORDER)) {
                $replaceFrom = array();
                $replaceTo = array();
                foreach ($matches AS $m) {
                    $replaceFrom[] = $m[1];
                    $replaceTo[] = urldecode($m[2]);
                }
                $body = str_replace($replaceFrom, $replaceTo, $body);
            }
        }
        $this->_body = $body;
        return $this->_body;
    }

    /**
     * Retourne la dernière erreur générée par cURL.
     * @return string
     */
    public function getError()
    {
        return curl_error($this->_resource);
    }

    public function __destruct()
    {
        curl_close($this->_resource);
    }
}
