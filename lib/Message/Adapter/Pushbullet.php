<?php

namespace Message\Adapter;

class Pushbullet extends AdapterAbstract
{
    protected $notify_url = "https://api.pushbullet.com/v2/pushes";

    /**
     * @param string $message
     * @throws \Exception
     * @return array
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if (!$this->token) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $params = array(
            "type" => "note",
            "title" => $this->getTitle(),
            "body" => $message
        );
        if ($url = $this->getUrl()) {
            $params["type"] = "link";
            $params["url"] = $url;
        }

        $params = json_encode($params);

        $curl = $this->getCurl(array(
            CURLOPT_USERPWD => $this->getToken(),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json ",
                "Content-Length: ".strlen($params)
            ),
            CURLOPT_POSTFIELDS => $params,
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exceptions("cURL Error: " . curl_error($curl));
        }

        $json = json_decode($response, true);

        if (400 <= $code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
            throw new \Exception($json["error"]["message"], $code);
        }

        return $json;
    }
}