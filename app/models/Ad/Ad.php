<?php

namespace App\Ad;

class Ad extends \AdService\Ad
{
    protected $_aid;
    protected $_date_created;
    protected $_comment;

    /**
    * @param int $id
    * @return Ad
    */
    public function setAid($id)
    {
        $this->_aid = $id;
        return $this;
    }

    /**
    * @return int
    */
    public function getAid()
    {
        return $this->_aid;
    }

    /**
    * @param string $date_created
    * @return Ad
    */
    public function setDateCreated($date)
    {
        $this->_date_created = $date;
        return $this;
    }

    /**
    * @return string
    */
    public function getDateCreated()
    {
        return $this->_date_created;
    }

    /**
    * @param string $comment
    * @return Ad
    */
    public function setComment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }

    /**
    * @return string
    */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data["date_created"] = (string) $this->_date_created;
        $data["comment"] = (string) $this->_comment;
        return $data;
    }
}
