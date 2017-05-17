<?php

namespace AdService;

class Ad
{
    protected $_id;
    protected $_link;
    protected $_title;
    protected $_description;
    protected $_price = 0;
    protected $_currency = "€";
    protected $_date;
    protected $_category;
    protected $_country;
    protected $_city;
    protected $_zip_code;
    protected $_professional;
    protected $_thumbnail_link;
    protected $_urgent;
    protected $_author;
    protected $_photos = array();
    protected $_properties = array();

    public function setFromArray(array $options)
    {
        foreach ($options AS $name => $value) {
            if (property_exists($this, "_".$name)) {
                $this->{"_".$name} = $value;
            }
        }

        return $this;
    }

    public function toArray()
    {
        return array(
            "id" => $this->_id,
            "link" => $this->_link,
            "title" => $this->_title,
            "description" => $this->_description,
            "price" => $this->_price,
            "currency" => $this->_currency,
            "date" => $this->_date,
            "category" => $this->_category,
            "country" => $this->_country,
            "city" => $this->_city,
            "zip_code" => $this->_zip_code,
            "professional" => $this->_professional,
            "photos" => $this->_photos,
            "urgent" => $this->_urgent,
            "author" => $this->_author,
            "properties" => $this->_properties,
        );
    }


    /**
    * @param int $id
    * @return \AdService\Ad
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
     * @return \AdService\Ad
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
     * @return \AdService\Ad
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
     * @return \AdService\Ad
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
     * @return \AdService\Ad
     */
    public function setPrice($price)
    {
//         $this->_price = (int) preg_replace('/[^0-9]*/', '', $price);
        $this->_price = $price;
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
    * @param string $currency
    * @return Ad
    */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
        return $this;
    }

    /**
    * @return string
    */
    public function getCurrency()
    {
        return $this->_currency;
    }


    /**
     * @param Zend_Date $date
     * @return \AdService\Ad
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
     * @return \AdService\Ad
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
     * @return \AdService\Ad
     */
    public function setCountry($county)
    {
        $this->_country = $county;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }


    /**
     * @param string $city
     * @return \AdService\Ad
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
     * @param string $zip_code
     * @return \AdService\Ad
     */
    public function setZipCode($zip_code)
    {
        $this->_zip_code = $zip_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->_zip_code;
    }


    /**
     * @param bool $professional
     * @return \AdService\Ad
     */
    public function setProfessional($professional)
    {
        $this->_professional = $professional;
        return $this;
    }

    /**
     * @return bool
     */
    public function getProfessional()
    {
        return $this->_professional;
    }


    /**
     * @param string $thumbail
     * @return \AdService\Ad
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
     * @return \AdService\Ad
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


    /**
     * @param string $author
     * @return \AdService\Ad
     */
    public function setAuthor($author)
    {
        $this->_author = $author;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->_author;
    }


    /**
     * @param array $photos
     * @return \AdService\Ad
     */
    public function setPhotos(array $photos)
    {
        $this->_photos = $photos;
        return $this;
    }

    /**
     * @return array
     */
    public function getPhotos()
    {
        return $this->_photos;
    }

    /**
     * @param string $name
     * @param string $value
     * @return \AdService\Ad
     */
    public function addProperty($name, $value)
    {
        $this->_properties[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return \AdService\Ad
     */
    public function removeProperty($name)
    {
        unset($this->_properties[$name]);
        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getProperty($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->_properties;
    }
}