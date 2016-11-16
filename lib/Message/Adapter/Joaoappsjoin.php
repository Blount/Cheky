<?php

namespace Message\Adapter;

class Joaoappsjoin extends AdapterAbstract
{
    protected $notify_url = "https://joinjoaomgcd.appspot.com/_ah/api/messaging/v1/sendPush";

    /**
     * @var string
     */
    protected $device_ids;

    /**
     * @param string $message
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if (!$this->device_ids) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }
        $params = array(
            "apikey" => $this->getToken(),
            "title" => $this->getTitle(),
            "text" => $message,
            "url" => $this->getUrl(),
        );
        $deviceIds = $this->getDeviceIds();
        if (0 === strpos($deviceIds, "group.")) {
            $params["deviceId"] = $deviceIds;
        } else {
            $params["deviceIds"] = $deviceIds;
        }
        $curl = $this->getCurl(array(
            CURLOPT_POST => false,
            CURLOPT_URL => $this->notify_url."?".http_build_query($params),
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
     * @return Joaoappsjoin
     */
    public function setDeviceIds($device_ids)
    {
        $this->device_ids = $device_ids;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceIds()
    {
        return $this->device_ids;
    }
}