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
    public $send_sms = 0;

    public function fromArray(array $values)
    {
        foreach ($values AS $key => $value) {
            $this->$key = $value;
        }
        if (!is_numeric($this->group_ads)) {
            $this->group_ads = 1;
        }
    }

    public function getCategories()
    {
        if ($this->categories) {
            return explode(",", $this->categories);
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
            "send_sms" => $this->send_sms
        );
    }
}