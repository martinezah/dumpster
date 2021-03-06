<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function preDispatch()
    {
    }

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) 
            $this->_dispatch(json_decode($this->getRequest()->getRawBody(), 1));
        else 
            $this->_redirect('/diver');
    }

    protected function _dispatch($data) 
    {
        $this->view->data = array();
        $this->config = Zend_Registry::get('config')->dumpster;
        $this->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8');

        if (@$data['action'] == 'pubkey') 
            $this->_pubkey($data);
        if (@$data['action'] == 'dump')   
            $this->_dump($data);
        if (@$data['action'] == 'tags')   
            $this->_tags($data);
        if (@$data['action'] == 'get')    
            $this->_get($data);
        if (@$data['action'] == 'find')    
            $this->_find($data);
    }

    protected function _find($data) 
    {
        $db = new App_Model_Db();
        $this->view->data['dumps'] = $db->find($data['tags']);
    }

    protected function _get($data) 
    {
        $db = new App_Model_Db();
        $this->view->data['dump'] = $db->get((int)$data['id']);
    }

    protected function _tags($data) 
    {
        $db = new App_Model_Db();
        $this->view->data['tags'] = $db->tags(@$data['prefix']);
    }

    protected function _dump($data) 
    {
        $db = new App_Model_Db();
        $message = $this->_decrypt($data['message'], base64_decode($data['key']));
        if (@$message['apiKey'] !== $this->config->apiKey) {
            $this->view->data['error'][] = 'Invalid API Key';
            return;
        } 
        $dump = new App_Model_Dump($message['data'], $message['tags']);
        $this->view->data['dump'] = $db->store($dump);
    }

    protected function _decrypt($encryptedMessage, $encryptedKey)
    {
        if ($this->config->privateKey && file_exists($this->config->privateKey))
        {
            $privKey = file_get_contents($this->config->privateKey);
        } else {
            $privKey = false;
        }
        if (!$privKey) {
            $this->view->data['error'][] = 'Could not load private key';
            return false;
        }
        $success = openssl_private_decrypt($encryptedKey, $decryptedKey, $privKey);
        if (!$success || empty($decryptedKey)) {
            $this->view->data['error'][] = 'Could not decrypt message key';
            return false;
        }
        $decryptedKey = json_decode($decryptedKey,1);
        $cipher = 'aes-256-cbc';
        $messageKey = base64_decode($decryptedKey["key"]);
        $initVector = base64_decode($decryptedKey["iv"]);
        $decryptedMessage = openssl_decrypt($encryptedMessage, $cipher, $messageKey, false, $initVector);
        return json_decode($decryptedMessage, 1);
    }

    protected function _pubkey($data) 
    {
        if ($this->config->distributePublicKey && $this->config->publicKey && file_exists($this->config->publicKey))
        {
            $this->view->data['pubkey'] = file_get_contents($this->config->publicKey);
        } else {
            $this->view->data['pubkey'] = false;
        }
    }
}
