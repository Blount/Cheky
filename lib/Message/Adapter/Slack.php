<?php

namespace Message\Adapter;

class Slack extends AdapterAbstract
{
    protected $hookurl = "";

    /**
     * @param string $message
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if (!$this->hookurl) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        if ($url = $this->getUrl()) {
            $message = "<".$url."|".$message.">";
        }
        $params = array(
            "text" => $message,
        );

        $curl = $this->getCurl(array(
            CURLOPT_POST => true,
            CURLOPT_URL => $this->hookurl,
            CURLOPT_POSTFIELDS => array(
                "payload" => json_encode($params),
            ),
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($curl));
        }

        $response = json_decode($response, true);
        if (!empty($response["errorMessage"])) {
            throw new \Exception("Errors:\n" . $response["errorMessage"]);
        }

        return true;
    }

    /**
     * @param string $device_ids
     * @return Slack
     */
    public function setHookurl($hookurl)
    {
        $this->hookurl = $hookurl;
        return $this;
    }

    /**
     * @return string
     */
    public function getHookurl()
    {
        return $this->hookurl;
    }
}