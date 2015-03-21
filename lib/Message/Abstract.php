<?php

namespace Message;

abstract class Message_Abstract
{
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options = array())
    {
        foreach ($options AS $name => $value) {
            $method = "set".ucfirst($name);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }


    abstract public function send($message);
}