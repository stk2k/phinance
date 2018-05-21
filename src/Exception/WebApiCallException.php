<?php
namespace Phinance\Exception;

class WebApiCallException extends \Exception implements PhinanceClientExceptionInterface
{
    /**
     * construct
     *
     * @param string $message
     * @param \Exception|null $prev
     */
    public function __construct($message, $prev = null){
        parent::__construct($message,0,$prev);
    }
}