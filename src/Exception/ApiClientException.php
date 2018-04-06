<?php
namespace Phinance\Exception;

class ApiClientException extends \Exception
{
    /** @var string */
    private $api;
    
    /**
     * construct
     *
     * @param string $api
     * @param \Exception|null $prev
     */
    public function __construct($api, $prev = null){
        parent::__construct('API call failed:' . $api,0,$prev);
    
        $this->api = $api;
    }
    
    /**
     * get api
     *
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }
    
}