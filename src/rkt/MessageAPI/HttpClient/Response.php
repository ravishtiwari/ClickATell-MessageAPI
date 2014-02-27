<?php
namespace   rkt\MessageAPI\HttpClient;

/**
 * Http Response class
 *
 * @author ravish
 */
class Response
{    
    
    /**
     *
     * response status
     * @var bool 
     */
    protected $responseStatus;
    
    /**
     *
     * response message returned
     * @var type 
     */
    protected $responseMessage;
    
    /**
     * 
     * http response code
     * @var mixed 
     */
    protected $responseCode;
    
    /**
     * 
     * response 
     * @var string 
     */
    protected $responseError;
    
    public function __construct($response, \ArrayObject $responseInfo) 
    {
        $this->responseMessage = $response;
        $this->responseStatus = $responseInfo['response_status'];
        $this->responseCode = $responseInfo['http_code'];
        $this->responseError = $responseInfo['response_error'];                
    }
    
    /**
     * 
     * return getway response message 
     * @return string getway response
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isSuccessful()
    {
        return ($this->responseCode > 200 && $this->responseCode < 300);        
    }
    
    /**
     *
     * @return boolean Http response status 
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     *
     * @return numeric http response code
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }
    
    /** 
     *
     * @return string http response error
     */
    public function getResponseError()
    {
        return $this->responseError;
    }
}