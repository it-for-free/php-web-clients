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
        $message = 'Trumail API Response is incorrect';
        if (!empty($Responce->Message)) {
            $message = $Responce->Message;
        }
        parent::__construct($message, $code, $previous);
    }

    // Переопределим строковое представление объекта.
    public function __toString() {
        return __CLASS__ . ": [!] Trumail Response object: " 
            . print_r($this->responce, true) .  "\n";
    }

}
