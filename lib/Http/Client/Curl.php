<?php

require_once __DIR__."/Abstract.php";

class HttpClientCurl extends HttpClientAbstract
{
    protected $_resource;

    public function __construct()
    {
        $this->_resource = curl_init();
        curl_setopt($this->_resource, CURLOPT_HEADER, true);
        curl_setopt($this->_resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_resource, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->_resource, CURLOPT_ENCODING, "gzip, deflate");
    }

    public function request($url = null)
    {
        if (!$this->_url && !$url) {
            throw new Exception("Aucune URL à appeler.");
        }

        if ($url) {
            $this->setUrl($url);
        }

        if ($path = $this->getCookiePath()) {
            $host = parse_url($this->_url, PHP_URL_HOST);
            curl_setopt($this->_resource, CURLOPT_COOKIEJAR, $path."/".$host);
            curl_setopt($this->_resource, CURLOPT_COOKIEFILE, $path."/".$host);
        }

        $headers = $this->_default_headers;

        if ($this->_headers) {
            $headers = array_merge($headers, $this->_headers);
        }

        if ($this->_referer) {
            $headers["Referer"] = $this->_referer;
        }

        $this->_referer = $this->_url;

        $curl_headers = array();
        foreach ($headers AS $key => $value) {
            $curl_headers[] = $key.": ".$value;
        }
        curl_setopt($this->_resource, CURLOPT_HTTPHEADER, $curl_headers);

        // Reset
        $this->setLocation(null);

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
        curl_setopt($this->_resource, CURLOPT_URL, $this->_url);

        if ($this->_time_between_requests && $this->_time_last_request) {
            $time = microtime(true) - $this->_time_last_request;
            if ($time < $this->_time_between_requests) {
                sleep(ceil($this->_time_between_requests - $time));
            }
        }

        $response = curl_exec($this->_resource);
        $this->_time_last_request = microtime(true);

        $header_size = curl_getinfo($this->_resource, CURLINFO_HEADER_SIZE);
        $headers = mb_substr($response, 0, $header_size);
        $body = mb_substr($response, $header_size);
        $headers = explode("\r\n", $headers);
        $this->_response_headers = array();
        foreach ($headers AS $header) {
            if (false !== strpos($header, ":")) {
                $data = explode(":", $header);
                $this->_response_headers[$data[0]] = trim($data[1]);
            }
        }

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
