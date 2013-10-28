<?php

namespace Lbc;

class Item
{
    protected $_id;
    protected $_link;
    protected $_title;
    protected $_description;
    protected $_price;
    protected $_date;
    protected $_category;
    protected $_county;
    protected $_city;
    protected $_professional;
    protected $_thumbnail_link;
    protected $_urgent;


    /**
    * @param int $id
    * @return Lbc_Ad
    */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
    * @return int
    */
    public function getId()
    {
        return $this->_id;
    }


    /**
     * @param string $link
     * @return Lbc_Ad
     */
    public function setLink($link)
    {
        $this->_link = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->_link;
    }


    /**
     * @param string $title
     * @return Lbc_Ad
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }


    /**
     * @param string $description
     * @return Lbc_Ad
     */
    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }


    /**
     * @param int $price
     * @return Lbc_Ad
     */
    public function setPrice($price)
    {
        $this->_price = (int) preg_replace('/[^0-9]*/', '', $price);
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->_price;
    }


    /**
     * @param Zend_Date $date
     * @return Lbc_Ad
     */
    public function setDate($date)
    {
        $this->_date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }


    /**
     * @param string $category
     * @return Lbc_Ad
     */
    public function setCategory($category)
    {
        $this->_category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->_category;
    }


    /**
     * @param string $county
     * @return Lbc_Ad
     */
    public function setCounty($county)
    {
        $this->_county = $county;
        return $this;
    }

    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->_county;
    }


    /**
     * @param string $city
     * @return Lbc_Ad
     */
    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->_city;
    }


    /**
     * @param bool $professionnal
     * @return Lbc_Ad
     */
    public function setProfessionnal($professionnal)
    {
        $this->_professionnal = $professionnal;
        return $this;
    }

    /**
     * @return bool
     */
    public function getProfessionnal()
    {
        return $this->_professionnal;
    }


    /**
     * @param string $thumbail
     * @return Lbc_Ad
     */
    public function setThumbnailLink($thumbail)
    {
        $this->_thumbnail_link = $thumbail;
        return $this;
    }

    /**
     * @return string
     */
    public function getThumbnailLink()
    {
        return $this->_thumbnail_link;
    }


    /**
     * @param bool $urgent
     * @return Lbc_Ad
     */
    public function setUrgent($urgent)
    {
        $this->_urgent = (bool)$urgent;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUrgent()
    {
        return $this->_urgent;
    }
}


