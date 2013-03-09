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

        $this->db->insert('dump', array('data' => $dump->data, 'tags' => json_encode($dump->tags)));
        $dumpid = $this->db->lastInsertId();
        
        foreach ($dump->tags as $tag) {
            $tagid = $this->_add_tag($tag);
            $this->db->insert('dump_tag', array('dump' => $dumpid, 'tag' => $tagid));
        }
        return true;
    }

    public function get($id) 
    {
        $data = $this->db->fetchRow('SELECT * FROM dump WHERE id = ?', $id);
        if (is_array($data) && array_key_exists("data", $data))
            $data["data"] = json_decode($data["data"], 1);
        return $data;
    }

    public function find($tags = array(), $limit = 10, $skip = 0) 
    {
        $tagIds = array();
        foreach ($tags as $tag) {
            $_ids = array();
            $result = $this->db->fetchAll("SELECT dump FROM dump_tag WHERE tag = (SELECT id FROM tag WHERE tag = ?)", $tag);
            if (is_array($result))
                foreach ($result as $row)
                    $_ids[] = $row["dump"];
            $tagIds[] = $_ids;

        }
        $ids = count($tagIds) > 1 ? call_user_func_array('array_intersect', $tagIds) : array_shift($tagIds);
        $ids = is_array($ids) ? implode(", ", $ids) : false;
        $dumps = array();
        if ($ids) {
            $sql = "SELECT * FROM dump WHERE id IN ({$ids})";
            $result = $this->db->fetchAll($sql);
            if (is_array($result))
                foreach ($result as $row) {
                    $row["data"] = json_decode($row["data"]);
                    $row["tags"] = json_decode($row["tags"]);
                    $dumps[] = $row;
            }
        }
        return $dumps;

    }

    public function tags($prefix = '', $limit = 0)
    {
        $tags = array();
        $limitPhrase = (int) $limit ? ' LIMIT ' . (int) $limit : '';
        $result = $this->db->fetchAll("SELECT tag FROM tag WHERE tag LIKE ? {$limitPhrase}", $prefix . '%');
        if (is_array($result))
            foreach ($result as $row)
                $tags[] = $row["tag"];
        return $tags;
    }

    protected function _add_tag($tag)
    {
       $result = $this->db->fetchAll('SELECT id FROM tag WHERE tag = ?', $tag);
       if ($result) return $result[0]["id"];
       $this->db->insert('tag', array('tag' => $tag));
       return $this->db->lastInsertId();
    }
}
