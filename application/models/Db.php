<?php

class App_Model_Db {

    public function __construct() 
    {
        $this->config = Zend_Registry::get("config");
        $this->db = Zend_Db::factory($this->config->resources->db->adapter, $this->config->resources->db->params);
        $this->db->getConnection();
    }

    public function store(App_Model_Dump $dump) 
    {
        if (!$dump->data) throw new Exception("Invalid Dump Object");

        $this->db->insert('dump', array('data' => $dump->data));
        $dumpid = $this->db->lastInsertId();
        
        foreach ($dump->tags as $tag) {
            $tagid = $this->_add_tag($tag);
            $this->db->insert('dump_tag', array('dump' => $dumpid, 'tag' => $tagid));
        }
        return true;
    }

    public function get(int $id) 
    {
    }

    public function find($tags = array(), $limit = 10, $skip = 0) 
    {
    }

    public function tags($prefix = '', $limit = 10)
    {
    }

    protected function _add_tag($tag)
    {
       $result = $this->db->fetchAll('SELECT id FROM tag WHERE tag = ?', $tag);
       if ($result) return $result[0]["id"];
       $this->db->insert('tag', array('tag' => $tag));
       return $this->db->lastInsertId();
    }
}
