<?php

class Dumpster 
{
    public static function Dump($data, $tags = array()) 
    {
        $config = DumpsterConfig::getInstance();
        if (! $config->isEnabled('dumpster')) { return; }

        try {
            if (empty($data)) throw new Exception("No data!");
            $data = json_encode($data);

            $url = $config->getConfig('dumpster.url');
            if (!$url) throw new Exception("Required setting 'dumpster.url' not found.");

            $apiKey = $config->getConfig('dumpster.apiKey');
            if (!$apiKey) throw new Exception("Required setting 'dumpster.apiKey' not found.");

            $pubKeyFile = $config->getConfig('dumpster.pubKey');
            if ($pubKeyFile) {
                if (!file_exists($pubKeyFile)) throw new Exception("Unable to find file '$pubKeyFile'");
                $pubKey = file_get_contents($pubKeyFile);
            } else {
                $msg = self::_post($url, array('action' => 'pubkey'));
                if (is_array($msg) && array_key_exists('pubkey', $msg)) $pubKey = $msg['pubkey'];
            }
            if (!$pubKey) throw new Exception("Unable to determine public key.");

            $messageKey['key'] = openssl_random_pseudo_bytes(16);
            $messageKey['iv'] = openssl_random_pseudo_bytes(16);
            if (!$messageKey['key'] || !$messageKey['iv']) throw new Exception("Could not create message key.");

            $cipher = 'aes-256-cbc';
            $message = array('apiKey' => $apiKey, 'data' => $data, 'tags' => $tags);
            $encryptedMessage = openssl_encrypt(json_encode($message), $cipher, $messageKey['key'], false, $messageKey['iv']);

            $encryptedKey = false;
            $messageKey['key'] = base64_encode($messageKey['key']);
            $messageKey['iv'] = base64_encode($messageKey['iv']);
            $success = openssl_public_encrypt(json_encode($messageKey), $encryptedKey, openssl_get_publickey($pubKey)); 
            if (!$success || empty($encryptedKey)) throw new Exception("Failed encrypting message key.");
            
            $encryptedKey = base64_encode($encryptedKey);
            $msg = self::_post($url, array('action' => 'dump', 'key' => $encryptedKey, 'message' => $encryptedMessage));
            if (!is_array($msg) || !@$msg['dump']) throw new Exception("Dump Failed.");
        } catch (Exception $e) { 
            error_log($e->getMessage());
        }
    }

    public static function _post($url, $data)
    {
        $data_string = json_encode($data);
        $ch = curl_init($url);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );                                                                                                                   
        return json_decode(curl_exec($ch), true);
    }
}

class DumpsterConfig
{
    private static $_instance;
    private $_data;

    private function __construct()
    {
        $this->_data = parse_ini_file('config.ini', true);
    }

    public static function getInstance()
    {
        if (!self::$_instance) self::$_instance = new self();
        return self::$_instance;
    }

    public function isEnabled($section)
    {
        return isset($this->_data[$section]);
    }

    public function getConfig($name)
    {
        $name_array = explode('.', $name, 2);
        if (count($name_array) < 2) return;
        list($section, $param) = $name_array;
        if (!isset($this->_data[$section][$param])) return;
        return $this->_data[$section][$param];
    }
}
