<?php

namespace Message\Adapter;

class NotifyMyAndroid extends AdapterAbstract
{
    protected $notify_url = "https://www.notifymyandroid.com/publicapi/notify";

    /**
     * Envoi un message.
     * @param string $msg
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        $message = trim($message);
        $this->setOptions($options);
        if (!$this->token || !$this->title) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $params = array(
            "apikey" => $this->getToken(),
            "application" => $this->getTitle(),
            "event" => $this->getDescription(),
            "description" => $message,
        );
        if ($url = $this->getUrl()) {
            $params["url"] = $url;
        }

        $curl = $this->getCurl(array(
            CURLOPT_POSTFIELDS => http_build_query($params)
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($curl));
        }

        // find error through content
        $xml = simplexml_load_string($response);
        $code = 200;

        if (isset($xml->error)) {
            $code = 0;
            if (isset($xml->error["code"])) {
                $code = (int) $xml->error["code"];
            }

            if (!$msg = (string) $xml->error) {
                $msg = "Unknow error.";
            }

            throw new \Exception($msg, $code);
        }

        return true;
    }
}