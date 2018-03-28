<?php
namespace Phinance\Http;

use Phinance\PhinanceClient;

class HttpDeleteRequest extends CurlRequest
{
    /** @var string  */
    private $url;
    
    /** @var array  */
    private $options;
    
    /**
     * Construct
     *
     * @param PhinanceClient $client
     * @param string $url
     * @param array $options
     */
    public function __construct($client, $url, $options = null)
    {
        parent::__construct($client);
        
        $this->url = $url;
        $this->options = $options ? $options : array();
    }
    
    /**
     * get http headers
     *
     * @return array
     */
    public function getHttpHeaders()
    {
        $http_deaders = parent::getDefaultHttpHeaders();
    
        $http_deaders['Content-Type'] = 'text/plain';
        
        if (isset($this->options['http_headers'])){
            $http_deaders = array_merge($http_deaders, $this->options['http_headers']);
        }
        return $http_deaders;
    }
    
    /**
     * get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * get extra options
     *
     * @return array
     */
    public function getExtraOptions()
    {
        return array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        );
    }
}