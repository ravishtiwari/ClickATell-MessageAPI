<?php
namespace   rkt\MessageAPI\HttpClient;

/**
 * CurlHttpClient
 * This class uses php 'curl' to perform http operations
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 */
use rkt\MessageAPI\HttpClient as HttpClient;
class CurlHttpClient extends HttpClient\BaseHttpClient
{
    public function __construct() 
    {        
        if(!function_exists("curl_init"))
        {
            throw  new \Exception("curl either not installed or not avaliable.");
        }
    }
    
    /**
     * 
     * @param type $uri
     * @param type $requestData
     * @param type $requestMethod
     * @return void
     * @throws \MessageAPI\HttpClient\Exception
     */
    public function executeRequest($uri, $requestData, $requestMethod = self::HTTP_GET, $options = array())
    {
        // create a new curl resource
        $curl = $this->initCurl();
        try
        {
            //prepare curl request
            $this->_prepareCurlRequest($curl, $uri, $requestData, $requestMethod);
            $response = $this->execCurl($curl);
            $responseInfo = $this->getInfo($curl);
            $responseInfo['response_error'] = '';
            $responseInfo['response_error_no'] = '';
            if(!empty($response))
            {
                $responseInfo['response_status'] = TRUE;
            }
            else 
            {
                $responseInfo['response_error'] = $this->getCurlError($curl);
                $responseInfo['response_error_no'] = $this->getCurlErrorNo($curl);
                $responseInfo['response_status']  = FALSE;                
            }
            
            $this->initResponse($response, new \ArrayObject($responseInfo));
            $this->closeCurl($curl);
        }catch(\Exception $e){
            $this->closeCurl($curl);
            throw $e;
        }        
    }
    
    protected function getRequestBody()
    {
        return $this->requestBody;
    }

    protected function initResponse($responseMessage, \ArrayObject $responseDetails) 
    {
        $this->response = new Response($responseMessage, $responseDetails);
    }
    
    /**
     * initiate curl
     * @return resource
     */
    private function initCurl()
    {
        return curl_init();
    }
    
    /**
     * 
     * @param resuource $curlResource
     */
    private function closeCurl($curlResource)
    {
        curl_close($curlResource);
    }
    
    /**
     * execute curl request
     * @param type $curl
     * @return string
     */
    private function execCurl($curl)
    {
        return curl_exec($curl);
    }
    
    /**
     * Prepare Curl Request
     * @param resource  $curl curl resource
     * @param string $uri request uri
     * @param array $requestData 
     * @param string $requestMethod
     */
    private function _prepareCurlRequest($curl, $uri, $requestData, $requestMethod)
    {
        curl_setopt($curl, CURLOPT_URL, $this->prepateRequest($uri, $requestData,  $requestMethod) );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        if($requestMethod != self::HTTP_GET)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8')); 
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getRequestBody());
        }
    }
    
    /**
     * 
     * @param resource $curl
     * @return array
     */
    private function getInfo($curl)
    {
        return curl_getinfo($curl);
    }
    
    /**
     * retunrs, curl response error
     * @param resource $curl curl resource
     * @return string 
     */
    private function getCurlError($curl)
    {
        return curl_error($curl);
    }
    
    /**
     * curl error number
     * @param resource $curl curl resource
     * @return string
     */
    private function getCurlErrorNo($curl)
    {
        return curl_errno($curl);
    }
}