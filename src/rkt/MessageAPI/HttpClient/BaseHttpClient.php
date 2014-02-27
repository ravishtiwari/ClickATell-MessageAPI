<?php
namespace   rkt\MessageAPI\HttpClient;

/**
 * BaseHttpClient class. This class serves as base class for http client functionality
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 */
abstract class BaseHttpClient 
{
    /**
     * http get method
     */
    const HTTP_GET = 'get';
    
    /**
     * http post method
     */
    const HTTP_POST = 'post';

    protected $request;
    
    /**
     * 
     * request uri for operation
     * @var string
     */
    protected $requestUri;

    /**
     * 
     * @var string 
     */
    protected $requestBody;

    /**
     * 
     * resonse object
     * @var Response 
     */
    protected $response;
    

    abstract protected function __construct();
    
    /**
     * Execute Http request on given uri using the method specified
     */
    abstract public function executeRequest($uri, $requestData, $requestMethod = self::HTTP_GET);
    
    /**
     * initialize Response object
     * @param string $responseMessage
     * @param \ArrayObject $responseDetails
     */
    abstract protected function initResponse($responseMessage, \ArrayObject $responseDetails);
    
    /**
     * 
     * @param string $type
     * @return \MessageAPI\HttpClient
     * @throws \InvalidArgumentException
     */
    static public function getInstance($type = 'curl')
    {
        switch ($type)
        {
            case 'curl':
                return new CurlHttpClient();
            case 'socket':
                throw new \InvalidArgumentException("HttpClient type {$type} not implemented yet.");
            default :
                throw new \InvalidArgumentException("Invalid http client type :".$type);
        }
    }
    
    /**
     * 
     * @return Response object
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Prepare HTTP Request 
     * @param string $uri
     * @param string $requestData
     * @param string $requestMethod
     * @param string $requestData
     * @return string 
     * @throws \InvalidArgumentException
     */
    protected function prepateRequest($uri, $requestData, $requestMethod=self::HTTP_GET)
    {
        self::validate($uri, $requestMethod);
        $this->requestbody =  '';
        if(!empty($requestData)){
            $this->requestBody =  http_build_query($requestData);
        }
        $this->requestUri = $uri;
        if($requestMethod == self::HTTP_GET){
            $this->requestUri.= "?".$this->requestBody;
        }
        return $this->requestUri;
    }
    
    protected function validate($uri, $requestMethod)
    {
        if(empty($uri)){
            throw new \InvalidArgumentException('Empty uri provided');
        }
        
        if(!in_array($requestMethod, array(self::HTTP_GET, self::HTTP_POST))) {
            throw new \InvalidArgumentException('Invalid request method :'.$requestMethod);
        }
    }
}