<?php

namespace App\Ad;

class Ad extends \AdService\Ad
{
    protected $_aid;
    protected $_date_created;
    protected $_comment;
    protected $_tags;
    protected $_online;
    protected $_online_date_checked;

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
     * @param array $tags
     * @return Ad
     */
    public function setTags($tags)
    {
        $this->_tags = $tags;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        if (!$this->_tags) {
            return array();
        }
        return $this->_tags;
    }

    /**
     * @param boolean $online
     * @return Ad
     */
    public function setOnline($online)
    {
        $this->_online = $online;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getOnline()
    {
        return $this->_online;
    }

    /**
     * @return boolean
     */
    public function isOnline()
    {
        return true == $this->_online;
    }

    /**
     * @return boolean
     */
    public function isOffline()
    {
        return false == $this->_online;
    }

    /**
     * @param string $online_date_checked
     * @return Ad
     */
    public function setOnlineDateChecked($online_date_checked)
    {
        $this->_online_date_checked = $online_date_checked;
        return $this;
    }

    /**
     * @return string
     */
    public function getOnlineDateChecked()
    {
        return $this->_online_date_checked;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data["date_created"] = (string) $this->_date_created;
        $data["comment"] = (string) $this->_comment;
        $data["tags"] = $this->getTags();
        $data["online"] = (int) $this->_online;
        $data["online_date_checked"] = (string) $this->_online_date_checked;
        return $data;
    }
}
