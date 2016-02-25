<?php

namespace AdService;

class Filter
{
    protected $min_id = 0;

    protected $last_id = 0;

    protected $price_min = -1;

    protected $price_max = -1;

    protected $price_strict = false;

    protected $cities = "";

    protected $categories = array();

    public function __construct(array $options = array())
    {
        $this->setFromArray($options);
    }

    public function isValid(Ad $ad)
    {
        if (!$ad->getPrice() && $this->price_strict) {
            return false;
        }

        if ($ad->getPrice()) {
            if ($this->price_min != -1 && $ad->getPrice() < $this->price_min
                || $this->price_max != -1 && $ad->getPrice() > $this->price_max) {
                return false;
            }
        }

        $city = mb_strtolower($ad->getCity());
        $country = mb_strtolower($ad->getCountry());
        if ($this->cities && !in_array($city, $this->cities) && !in_array($country, $this->cities)) {
            return false;
        }

        if ($this->categories && !in_array($ad->getCategory(), $this->categories)) {
            return false;
        }

        return true;
    }

    public function setFromArray(array $options)
    {
        foreach ($options AS $option => $value) {
            $method = "set".str_replace(" ", "", ucwords(
                      str_replace("_", " ", $option)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /**
    * @param int $min_id
    * @return Filter
    */
    public function setMinId($min_id)
    {
        $this->min_id = $min_id;
        return $this;
    }

    /**
    * @return int
    */
    public function getMinId()
    {
        return $this->min_id;
    }

    /**
     * @param int $last_id
     * @return Filter
     */
    public function setLastId($last_id)
    {
        $this->_last_id = $last_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastId()
    {
        return $this->_last_id;
    }

    /**
    * @param int $price_min
    * @return Filter
    */
    public function setPriceMin($price_min)
    {
        $this->price_min = $price_min;
        return $this;
    }

    /**
    * @return int
    */
    public function getPriceMin()
    {
        return $this->price_min;
    }

    /**
    * @param int $price_max
    * @return Filter
    */
    public function setPriceMax($price_max)
    {
        $this->price_max = $price_max;
        return $this;
    }

    /**
    * @return int
    */
    public function getPriceMax()
    {
        return $this->price_max;
    }

    /**
    * @param bool $price_strict
    * @return Filter
    */
    public function setPriceStrict($price_strict)
    {
        $this->price_strict = $price_strict;
        return $this;
    }

    /**
    * @return bool
    */
    public function getPriceStrict()
    {
        return $this->price_strict;
    }

    /**
    * @param array $cities
    * @return Filter
    */
    public function setCities(array $cities)
    {
        $this->cities = $cities;
        return $this;
    }

    /**
    * @return array
    */
    public function getCities()
    {
        return $this->cities;
    }

    /**
    * @param array $categories
    * @return Filter
    */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
    * @return array
    */
    public function getCategories()
    {
        return $this->categories;
    }
}