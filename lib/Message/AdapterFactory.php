<?php

namespace Message;

use \Message\Adapter;

class AdapterFactory
{
    /**
     * @param string $service
     * @param array $options
     * @return \Adapter\AdapterAbstract
     */
    public static function factory($service, array $options = array())
    {
        $service = strtolower($service);
        switch ($service) {
            case "smsfreemobile":
            case "freemobile":
                return new Adapter\SmsFreeMobile($options);
            case "smsovh":
            case "ovh":
                return new Adapter\SmsOvh($options);
            case "notifymyandroid":
                return new Adapter\NotifyMyAndroid($options);
            case "pushbullet":
                return new Adapter\Pushbullet($options);
            case "pushover":
                return new Adapter\Pushover($options);
            case "joaoappsjoin":
                return new Adapter\Joaoappsjoin($options);
            case "slack":
                return new Adapter\Slack($options);
        }
        throw new \Exception("No service available");
    }
}