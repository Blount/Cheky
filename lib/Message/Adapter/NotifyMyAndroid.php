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

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code == 200) {
            // find error through content

        }
        if ($code != 200) {
            switch ($code) {
                case 400:
                    $msg = "The data supplied is in the wrong format, invalid length or null.";
                    break;
                case 401:
                    $msg = "None of the API keys provided were valid.";
                    break;
                case 402:
                    $msg = "Maximum number of API calls per hour exceeded.";
                    break;
                case 500:
                    $msg = "Internal server error. Please contact our support if the problem persists.";
                    break;
                default:
                    $msg = "Unknow error.";
            }
            throw new \Exception($msg, $code);
        }

        return true;
    }
}