<?php

namespace ItForFree\WebClients\Common\Exception;

class BadApiResponseException extends \Exception
{
    protected $responce  = null;
    
    // Переопределим исключение так, что параметр message станет обязательным
    public function __construct($Responce, $code = 0, Exception $previous = null) {
        // некоторый код 
        $this->responce = $Responce;
        // убедитесь, что все передаваемые параметры верны
        parent::__construct('API Response is incorrect', $code, $previous);
    }

    // Переопределим строковое представление объекта.
    public function __toString() {
        return __CLASS__ . ": [!] Response is incorrect: " 
            . print_r($this->responce, true) .  "\n";
    }

}
