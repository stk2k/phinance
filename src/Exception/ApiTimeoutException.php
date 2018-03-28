<?php
namespace Phinance\Exception;

class ApiTimeoutException extends ApiErrorResponseException
{
    /**
     * construct
     *
     * @param string $api
     * @param string $status
     */
    public function __construct($api, $status){
        parent::__construct($api, $status, 'bitFlyer api timed out:' . $api);
    }
    
}