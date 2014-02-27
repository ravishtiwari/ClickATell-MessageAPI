<?php
namespace   rkt\MessageAPI\HttpAPI;
/**
 * HttpMessageAPI
 * this class implements http api of clickatell sms getway, and uses http get and post to perform getway actions
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 */
use   rkt\MessageAPI\Exception;
use   rkt\MessageAPI\BaseMessageAPI;
use   rkt\MessageAPI\HttpClient;
class HttpMessageAPI extends BaseMessageAPI
{
    public function __construct($apiUserId, $apiPassword, $apiClientId)
    {
        $this->userId = $apiUserId;
        $this->apiClientId = $apiClientId;
        $this->password = $apiPassword;
        $this->messageId = '';
    }
    
    /**
     * 
     * @return string 
     */
    public function getAPIType()
    {
        return self::API_HTTP;
    }

     /**
     * @return void
     * @throws Exception\MessageAPIException
     */
    public function authnicate()
    {
        $this->apiInitCheck();
        $httpClient = $this->getHttpClient();
        $requestData = array("user" => urlencode($this->userId), "password" => $this->password, "api_id" => $this->apiClientId);
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_AUTH), $requestData);
        if(!$httpClient->getResponse()->getResponseStatus())
        {
            throw new Exception\MessageAPIException('Authnication failed for user : '.$this->userId.' API returned : '.$httpClient->getResponseBody(), Exception\Error::ERR_AUTH_FAIL);
        }    
        $response = $this->extractAPIResponse($httpClient->getResponse()->getResponseMessage());
        if(array_key_exists('err', $response))
        {
            list($errorNo, $errorMessage) = explode(",", $response['err'], 2);
            throw new Exception\MessageAPIException('Error while trying to authnicate user :'.  $this->userId.' API returned : '.$errorMessage,  $errorNo);
        }
        $this->apiSessionId =  trim($response['ok']);
    }
    
    /**
     *
     * check if a session is still active
     * @return boolean 
     * @throws Exception\HttpException
     */
    public function isSessionAlive()
    {
        $this->apiInitCheck();
        $httpClient = $this->getHttpClient();
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_PING), 
                                    array("session_id" => $this->apiSessionId));
        if (!$httpClient->getResponse()->getResponseStatus())
        {
            throw new Exception\HttpException("Error while trying to check status of session.");
        }
        $response = $this->extractAPIResponse($httpClient->getResponse()->getResponseMessage());
        return (array_key_exists('ok', $response));        
    }

    /**
     * Query account balance
     * @return long account balance on success | empty otherwise
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    public function getAccountBalance()
    {
        $httpClient = $this->getHttpClient();
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_BAL_QUERY), array('session_id' => $this->getApiSessionId()));
        if(!$httpClient->getResponse()->getResponseStatus())
        {
            throw new Exception\MessageAPIException('Error trying to query account balance. Getway returned : ' . $httpClient->getResponse()->getResponseError());
        }
        $response = $this->extractAPIResponse($httpClient->getResponse()->getResponseMessage());
        return (isset($response['credit'])) ? $response['credit'] : '';
    }
    
     /**getLastMessageId
     * Query amount charged for sending a particular message
     * 
     * @param type $messageId
     * @return type
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    public function getMessageCharge($messageId)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_GET_MSG_CHARGE), array('session_id' => $this->getApiSessionId(), 'apimsgid'=> $messageId ));
        if($httpClient->getResponse()->getResponseStatus())
        {
            throw new Exception\HttpException('Error trying to query message charge for Message : '.$messageId);
        }
        $response = $this->extractAPIResponse($httpClient->getResponse()->getResponseMessage());
        if(empty($response) || array_key_exists('err', $response))
        {
            throw new Exception\MessageAPIException('Error trying to query message charge, getway returne'.$httpClient->getResponse()->getResponseMessage());
        }
        return trim((float)$response['charge']);
    }

     /**
     * return status of a message sent
     * @param String $messageId
     * @return boolean
     */
    public function getMessageStatus($messageId)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_QUERY_MSG), array('session_id' => $this->getApiSessionId(), 'apimsgid'=> $messageId ));
        if($httpClient->getResponse()->getResponseStatus())
        {
            $regex = '/.+:(.*)\s.+:(.*)/i';
            $response = $httpClient->getResponse()->getResponseMessage();
            $parsedResponse = array();
            preg_match($regex, $response, $parsedResponse);
            if(!empty($parsedResponse))
            {
                return trim((string) $parsedResponse[2]);
            }
            else 
            {
                throw  new Exception\MessageAPIException('Error while trying to query message status. Getway returned :'.$response);
            }
        }
        else
        {
            throw new Exception\HttpException('Error while trying to query message status.');
        }
        
    }
        
    /**
     * send message 
     * @param String $text
     * @param string $to send message to 
     * @param array $options
     * @return string api message id of last message sent 
     * @throws \InvalidArgumentException
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    public function sendMessage($text, $to, $options = array())
    {
        $this->_validate($text, $to);
        $this->cleanNumber($to);
        $requestData = array_merge($options, array('to' => rawurlencode($to), 'text' => urlencode($text), 'session_id' => $this->getApiSessionId()));
        
        if(!empty($options['from']))
        {
            $requestData['from'] = rawurlencode ($options['from']);
        }
        $httpClient = $this->getHttpClient();
        $httpClient->executeRequest($this->buildUri(self::API_ACTION_SEND_MSG), $requestData, HttpClient\BaseHttpClient::HTTP_GET);
        if(!$httpClient->getResponse()->getResponseStatus())
        {
            throw new Exception\HttpException('Error trying to send message to : '.  $to);
        }
        $apiResponse = $this->extractAPIResponse($httpClient->getResponse()->getResponseMessage());
        if(array_key_exists('err', $apiResponse))
        {
            throw new Exception\MessageAPIException('Error while sending text message, gateway returned :'.$httpClient->getResponse()->getResponseMessage());
        }
        
        if(array_key_exists('id', $apiResponse))
        {
            $this->messageId = $apiResponse['id'];
        }
        return $this->messageId;
    }

    public function stopMessage($messageId)
    {
         $httpClient = $this->getHttpClient();
         $httpClient->executeRequest($this->buildUri(self::API_ACTION_STOP_MSG), array('apimsgid'=>$messageId));
         if($httpClient->getResponse()->getResponseStatus())
         {
             
         }
    }
                                                        
    /**
     * build request uri depending on action provided
     * @param string $action
     * @return string 
     */
    protected function buildUri($action) 
    {
        if(in_array($action, $this->getAllowedOperation()))
        {
            return $this->url.'http/'. $action;
        }
        return '';        
    }

    /**
     * returns list of allowed actions
     * @return array
     */
    protected function getAllowedOperation()
    {
        return array(
            self::API_ACTION_SEND_MSG,
            self::API_ACTION_AUTH,
            self::API_ACTION_PING,
            self::API_ACTION_QUERY_MSG,
            self::API_ACTION_BAL_QUERY,
            self::API_ACTION_GET_MSG_CHARGE,
            self::API_ACTION_STOP_MSG);
    }
    
    protected function extractAPIResponse($ApiResponse)
    {
        $responseStatus = array();
        preg_match_all("/([A-Za-z]+):((.(?![A-Za-z]+:))*)/", $ApiResponse, $matches);
        foreach ($matches[1] as $index => $status) 
        {
            $responseStatus[strtolower($status)] = trim($matches[2][$index]);
        }
        return $responseStatus;
    }
    
    private function _validate($text, $to)
    {
        if(empty($to))
        {
            throw new \InvalidArgumentException("Empty to address.");
        }
        
        if(empty($text))
        {
            throw new \InvalidArgumentException("Nothing to send, empty text provided.");
        }
    }
}