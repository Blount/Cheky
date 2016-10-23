<?php

namespace App\Mail;

class Alert
{
    public $email;
    public $id;
    public $title;
    public $url;
    public $interval = 30;
    public $time_last_ad = 0;
    public $time_updated = 0;
    public $price_min = -1;
    public $price_max = -1;
    public $price_strict = false;
    public $cities;
    public $categories;
    public $suspend = 0;
    public $group = "";
    public $group_ads = 1;
    public $send_mail = 1;
    public $send_sms_free_mobile = 0;
    public $last_id = array();
    public $max_id = 0;
    public $send_sms_ovh = 0;
    public $send_pushbullet = 0;
    public $send_notifymyandroid = 0;
    public $send_pushover = 0;
    public $send_joaoappsjoin = 0;

    public function fromArray(array $values)
    {
        foreach ($values AS $key => $value) {
            $this->$key = $value;
        }
        if (!is_numeric($this->group_ads)) {
            $this->group_ads = 1;
        }

        /**
         * Depuis 3.1, last_id contient les derniers IDs de la liste d'annonce
         * et max_id l'ID max trouvé.
         */
        if ($this->last_id && is_numeric($this->last_id) && !$this->max_id) {
            $this->max_id = $this->last_id;
        }
    }

    public function getCategories()
    {
        if ($this->categories && is_string($this->categories)) {
            return explode(",", $this->categories);
        }
        if (is_array($this->categories)) {
            return $this->categories;
        }
        return array();
    }

    public function toArray()
    {
        return array(
            "email" => $this->email,
            "id" => $this->id,
            "title" => $this->title,
            "url" => $this->url,
            "interval" => $this->interval,
            "time_last_ad" => $this->time_last_ad,
            "time_updated" => $this->time_updated,
            "price_min" => $this->price_min,
            "price_max" => $this->price_max,
            "price_strict" => $this->price_strict,
            "cities" => $this->cities,
            "suspend" => $this->suspend,
            "group" => $this->group,
            "group_ads" => $this->group_ads,
            "categories" => $this->categories,
            "send_mail" => $this->send_mail,
            "send_sms_free_mobile" => $this->send_sms_free_mobile,
            "last_id" => $this->last_id,
            "max_id" => (int) $this->max_id,
            "send_sms_ovh" => $this->send_sms_ovh,
            "send_pushbullet" => $this->send_pushbullet,
            "send_notifymyandroid" => $this->send_notifymyandroid,
            "send_pushover" => $this->send_pushover,
            "send_joaoappsjoin" => $this->send_joaoappsjoin,
        );
    }
}