<?php
namespace Phinance\Exception;

class ServerResponseFormatException extends \Exception implements PhinanceClientExceptionInterface
{
    /**
     * construct
     *
     * @param string $message
     */
    public function __construct($message){
        parent::__construct('API server returned illegal response:' . $message);
    }
}