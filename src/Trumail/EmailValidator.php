<?php

namespace ItForFree\WebClients\Trumail;

use GuzzleHttp\Client;
use ItForFree\WebClients\Common\Exception\BadApiResponseException;
use ItForFree\rusphp\Common\Time\RequestsTimeInterval;


/**
 * Валидация verify email-а
 */
class EmailValidator
{
    
    public $email = 'example@example.com';
    public static $baseUrl = 'https://api.trumail.io/';
    
    protected $guzzleClient = null;
    
    /**
     * Объект для рассчета оптимального интервала между запросами
     * @var ItForFree\rusphp\Common\Time\RequestsTimeInterval 
     */
    protected $timeIntervalController = null;
    
    public function __construct($baseTimeInterval = 1)
    {
        $this->guzzleClient =  new Client([
            'base_uri' => self::$baseUrl,
        ]);
        
        $this->timeIntervalController = 
            new RequestsTimeInterval($baseTimeInterval); 
    }
    
    /**
     * 
     * Check mail is deliverable
     * @param string  $email
     * @param boolean $trustCatchAll accept or not this email in case server is in "catch-All"
     *    mode @see http://fkn.ktu10.com/?q=node/10336
     * @return boolean
     */
    public function verify($email, $trustCatchAll = true)
    {
        $result = false;
        
        $Response = $this->getTrumailResponce($email);
        if (!isset($Response->deliverable)) {
            throw new BadApiResponseException($Response);
        }
        
        $result = $Response->deliverable &&
                ($trustCatchAll || $Response->catchAll);

        return $result;
    }
    
    
    /**
     * 
     * Проверит на доставляемость с ожиданием между запросами 
     * (вызывайте этот метод во внешнем цикле)
     * 
     * ((Check mail is deliverable with waiting between requests))
     * 
     * 
     * @param string  $email
     * @param boolean $trustCatchAll accept or not this email in case server is in "catch-All"
     * @param string $printLog       verbose mode (печатать ли лог сообщений)
     * @return type
     */
    public function verifyNext($email, $trustCatchAll = true, $printLog = false)
    {
        $exceptionCatched = true;
        
        while ($exceptionCatched) { // пока не обойдётся без ислючения
            $exceptionCatched = false;
            
            $this->timeIntervalController->wait();
            try {
                $result = $this->verify($email, $trustCatchAll);
            } 
            catch (BadApiResponseException $e) {   
                $exceptionCatched = true;
                if ($printLog) {
                    echo " $email: ",
                        $e->getMessage(), "\n";
                }
            }
            $this->timeIntervalController->update(!$exceptionCatched); 
            
            if (!$exceptionCatched && $printLog) {
                echo " Current time wait interval: " 
                    . $this->timeIntervalController->getCurrentInterval()
                    . " \n";
            }
        }
        
        return $result;
    }
   
    /**
     * Return full answer of https://api.trumail.io/ for curren email
     * 
     * @param string $email
     * @return object
     */
    public function getTrumailResponce($email)
    {
        $response = $this->guzzleClient->get("v2/lookups/json?email=$email");
        return json_decode($response->getBody()->getContents());
    }
}