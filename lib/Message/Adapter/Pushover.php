<?php

namespace Message\Adapter;

class Pushover extends AdapterAbstract
{
    protected $notify_url = "https://api.pushover.net/1/messages.json";

    /**
     * @var string
     */
    protected $user_key;

    /**
     * @param string $message
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if (!$this->token || !$this->user_key) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }
        $curl = $this->getCurl(array(
            CURLOPT_POSTFIELDS => array(
                "token" => $this->getToken(),
                "user" => $this->getUserkey(),
                "message" => $message,
                "title" => $this->getTitle(),
                "url" => $this->getUrl(),
            )
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($curl));
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 !== $code) {
            $response = json_decode($response, true);
            throw new \Exception("Errors:\n" . implode("\n", $response["errors"]));
        }

        return true;
    }

    /**
    * @param string $userkey
    * @return Pushover
    */
    public function setUserkey($userkey)
    {
        $this->user_key = $userkey;
        return $this;
    }

    /**
    * @return string
    */
    public function getUserkey()
    {
        return $this->user_key;
    }
}