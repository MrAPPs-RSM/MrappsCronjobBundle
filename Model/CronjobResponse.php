<?php

namespace Mrapps\CronjobBundle\Model;

class CronjobResponse
{
    private $success;
    private $output;
    private $extraData;
    
    public function __construct($success, $output, $extraData = null) {
        $this->success = (bool)$success;
        $this->output = trim($output);
        $this->extraData = is_array($extraData) ? $extraData : array();
    }
    
    public function isSuccessful() {
        return $this->success;
    }
    
    public function getResponse() {
        
        $output = array_merge($this->extraData, array(
            'output' => $this->output,
        ));
        
        return $output;
    }
    
    public function __toString() {
        return json_encode($this->getResponse());
    }
}