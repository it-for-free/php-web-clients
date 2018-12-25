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
     * @param boolean accept or not this email in case server is in "catch-All"
     *    mode @see http://fkn.ktu10.com/?q=node/10336
     * @return type
     */
    public function verify($email, $trustCatchAll = true)
    {
        $Response = $this->getTrumailResponce($email);
        if (!isset($Response->deliverable)) {
            throw new BadApiResponseException($Response);
        }
        
        $result = $Response->deliverable &&
                ($trustCatchAll || $Response->catchAll);

        return $result;
    }
    

    public function verifyNext($email, $trustCatchAll = true, $startInterval = 1)
    {
        
//        try {
//            $result = $this->verify($email, $trustCatchAll);
//        } 
//        catch () {
//            
//        }
        
        $this->timeIntervalController->update($result);
        
        return ($Response->deliverable ?? false) 
            && (!($Response->catchAll ?? false));
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