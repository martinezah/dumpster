<?php

class App_Model_Dump {
    
    protected $_data;
    protected $_tags;

    public function __construct($data = null, $tags = array()) {
        $this->_data = $data;
        $this->_tags = $tags;
    }

    public function __get($key)
    {
        $key = "_{$key}";
        if (property_exists($this, $key)) {
            return $this->$key;
        } else {
            throw new Exception("Failed to get invalid property '{$key}'");
        }
    }

    public function __set($key, $value) 
    {
        $key = "_{$key}";
        if (property_exists($this, $key)) {
            $this->$key = $value;
        } else {
           throw new Exception("Failed to set invalid property '{$key}'");
        }
    }
}
