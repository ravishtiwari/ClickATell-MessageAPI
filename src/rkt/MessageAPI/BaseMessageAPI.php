<?php
/**+----------------------------------------------------------------------+
* |   rkt\MessageAPI\BaseMessageAPI                                             |
* +-----------------------------------------------------------------------+
* | Copyright (c) 2014 Ravish Tiwari                                      |
* | (Actually no copyright, you are free to use this code)                |
* +-----------------------------------------------------------------------+
* | This source file is subject to version 3.0 of the PHP license,        |
* | that is bundled with this package in the file LICENSE, and is         |
* | available at through the world-wide-web at                            |
* | http://www.php.net/license/3_01.txt.                                  |
* | If you did not receive a copy of the PHP license and are unable to    |
* | obtain it through the world-wide-web, please send a note to           |
* | license@php.net so we can mail you a copy immediately.                |
* +-----------------------------------------------------------------------+
* | Author(s): Ravish Kumar Tiwari  <ravishktiwari@hotmail.com>           |
* +-----------------------------------------------------------------------+
*/

/**
 * Base message api. 
 * This class serves as base class 
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 * @copyright 2013 Ravish Tiwari.
 * @license http://www.php.net/license/3_01.txt PHP License
 * @access public
 * @package   rkt\MessageAPI\BaseMessageAPI
 */
namespace rkt\MessageAPI;

use   rkt\MessageAPI\Exception;
use   rkt\MessageAPI\HttpClient;
use   HttpClient\BaseHttpClient;
use   rkt\MessageAPI\HttpAPI\HttpMessageAPI as HttpAPI;
abstract class BaseMessageAPI 
{
    
    /**
     * ClickTell API URL
     */
    const API_BASE_URL = "http://api.clickatell.com/";

    /**
     * ClickTell API SECURE URL
     */
    const API_BASE_URL_SECURE = "https://api.clickatell.com/";
    
    /**
     * Message delivered to intended user
     */
    const MSG_STATUS_SENT       = 'MESSAGE_SENT';

    /**
     * Message delivery to user pending
     */
    const MSG_STATUS_PENDING    = 'MESSAGE_PENDING';

    /**
     * Message delievery  to user fail
     */
    const MSG_STATUS_FAILED     = 'MESSAGE_DELIVERY_FAIL';
    
    
    //use API mode : http or xml
    
    /**
     * api perform http operation 
     */
    const API_HTTP = 'http';
    
    const API_XML = 'xml';

    //Constant for different API actions such as auth, sendmsg, ping, token_pay etc
    /**
     * Auth action
     */
    const API_ACTION_AUTH = 'auth';
    
    /**
     * do ping action to check validity of session
     */
    const API_ACTION_PING = 'ping';
    
    /**
     * send message action
     */
    const API_ACTION_SEND_MSG = 'sendmsg';

    /**
     * query message status
     */
    const API_ACTION_QUERY_MSG = 'querymsg';
    
    const API_ACTION_STOP_MSG = 'delmsg';
    
    /**
     * query balance
     */
    const API_ACTION_BAL_QUERY = 'getbalance';
    
    
    /**
     * query amount charged for a message
     */
    const API_ACTION_GET_MSG_CHARGE = 'getmsgcharge';
    
    //MMS Actions
    /**
     * Push MMS
     */
    const API_ACTION_IND_PUSH = 'ind_push';
    
    /**
     * WAP Push Service Indication 
     */
    const API_ACTION_SI_PUSH = 'si_push';
    
    //http batch actions
    
    /**
     * start batch 
     */
    const API_ACTION_START_BATCH = 'startbatch';
    
    /**
     * send item to existing batch
     */
    const API_ACTION_SEND_ITEM = 'senditem';
    
    /**
     * end http batch operation
     */
    const API_ACTION_END_BATCH = 'endbatch';
        
    /**
     * use curl
     */
    const HTTP_CLIENT_CURL = 'curl';
    
    /**
     * use fsock
     */
    const HTTP_CLIENT_SOCKET = 'fsock';
    
    /**
     * Clicktell api places constraint on max to addresses per send command. 
     * Limit is different for get and post request.
     * 
     * Max to address per send command for request method GET
     */    
    const MAX_TO_COUNT_FOR_GET = 100;
    
    /**
     * Max to address per send command for request method GET
     */
    const MAX_TO_COUNT_FOR_POST = 300;


    /**
     * SMS Gateway ID
     * @var string ClickTell API client ID
     */
    protected $apiClientId;

    /**
     * SMS Gateway User ID
     * @var string ClickTell User id
     */
    protected $userId;

    /**
     * SMS Gateway password
     * @var string  ClickTell API password
     */
    protected $password;

    /**
     *
     * @var string API session id
     */
    protected  $apiSessionId = '';
    
    /**
     *
     * @var array last message id 
     */
    protected $messageId;

    /**
     *
     * use SSL
     * @var bool use ssl
     */
    protected $useSSL = true;


    /**
     *
     * API url to use
     * @var string API url to use
     */
    protected $url;
    
    /**
     *
     * Http Client instance
     * @var HttpClient\BaseHttpClient 
     */
    protected $httpClient;
    
    /**
     *
     * numbers 
     * @var \ArrayObject $numbers
     */
    protected $numbers;

    /**
     * Error codes generated by Clickatell Gateway
     * @var array
     */
    protected $errorMessages = array (
        '001' => 'Authentication failed',
        '002' => 'Unknown username or password',
        '003' => 'Session ID expired',
        '004' => 'Account frozen',
        '005' => 'Missing session ID',
        '007' => 'IP lockdown violation',
        '101' => 'Invalid or missing parameters',
        '102' => 'Invalid UDH. (User Data Header)',
        '103' => 'Unknown apismgid (API Message ID)',
        '104' => 'Unknown climsgid (Client Message ID)',
        '105' => 'Invalid Destination Address',
        '106' => 'Invalid Source Address',
        '107' => 'Empty message',
        '108' => 'Invalid or missing api_id',
        '109' => 'Missing message ID',
        '110' => 'Error with email message',
        '111' => 'Invalid Protocol',
        '112' => 'Invalid msg_type',
        '113' => 'Max message parts exceeded',
        '114' => 'Cannot route message',
        '115' => 'Message Expired',
        '116' => 'Invalid Unicode Data',
        '120' => 'Invalid delivery date',
        '121' => 'Destination mobile number blocked',
        '122' => 'Destination mobile opted out',
        '123' => 'Invalid Sender ID',
        '128' => 'Number delisted',
        '130' => 'Maximum MT limitexceeded until <UNIXTIME STAMP>',
        '201' => 'Invalid batch ID',
        '202' => 'No batch template',
        '301' => 'No credit left',
        '302' => 'Max allowed credit'
    );

    /**
     * Status of the message sent
     *
     * @var array
     */
    protected $msgStatus = array (
        '001' => 'Message unknown',
        '002' => 'Message queued',
        '003' => 'Delivered',
        '004' => 'Received by recipient',
        '005' => 'Error with message',
        '006' => 'User cancelled message delivery',
        '007' => 'Error delivering message',
        '008' => 'OK',
        '009' => 'Routing error',
        '010' => 'Message expired',
        '011' => 'Message queued for later delivery',
        '012' => 'Out of credit'
    );
    
    /**
     * Message type
     * @var array  
     */
    protected $msgTypes = array (
        'SMS_TEXT',
        'SMS_FLASH',
        'SMS_NOKIA_OLOGO',
        'SMS_NOKIA_GLOGO',
        'SMS_NOKIA_PICTURE',
        'SMS_NOKIA_RINGTONE',
        'SMS_NOKIA_RTTL',
        'SMS_NOKIA_CLEAN',
        'SMS_NOKIA_VCARD',
        'SMS_NOKIA_VCAL',
    );

    /**
     * Required features. FEAT_8BIT, FEAT_UDH, FEAT_UCS2 and FEAT_CONCAT are
     * set by default by Clickatell.
     */
    const FEAT_TEXT = 1;
    const FEAT_8BIT = 2;
    const FEAT_UDH = 4;
    const FEAT_UCS2 = 8;
    const FEAT_ALPHA = 16;
    const FEAT_NUMBER = 32;
    const FEAT_FLASH = 512;
    const FEAT_DELIVACK = 8192;
    const FEAT_CONCAT = 16384;

    /**
     *
     * http client type (curl|fsock)
     * @var string $_httpClientType 
     */
    private $_httpClientType;
    
    private $_apiInited = false;

    abstract protected function buildUri($action);
    
    abstract public function __construct($apiUserId, $apiPassword, $apiClientId);

    /**
     * Authnicate user from ClickATell Messaging getaway and set session 
     * @return void
     * @throws Exception\MessageAPIException
     */
    abstract public function authnicate();
    
    /**
     * @return string API type 
     */
    abstract public function getAPIType();

    /**
     *
     * check if a session is still active
     * @return boolean 
     * @throws Exception\HttpException
     */
    abstract public function isSessionAlive();
    
    /**
     * Query users account balance
     * @return long account balance
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    abstract public function getAccountBalance();
    
    /**
     *
     * Query amount charged for sending a particular message
     * 
     * @param type $messageId
     * @return type
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    abstract public function getMessageCharge($messageId);
    
    /**
     *
     * return status of a message sent
     * @param String $messageId
     * @return boolean
     */
    abstract public function getMessageStatus($messageId);

    /**
     * send a message using gateway 
     * 
     * @param string $text
     * @param array $options
     * @return mixed array Message id if sent to one user, array (number=>messageId, number2=>MessageId) when sent to multiple users 
     * @throws \InvalidArgumentException
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    abstract public function sendMessage($text, $to,  $options = array());

    /**
     *
     * delete/stop a message sent
     * @param string $messageId id of the message
     * @return boolean
     * @throws Exception\MessageAPIException
     * @throws Exception\HttpException
     */
    abstract public function stopMessage($messageId);

    
    /**
     * lists allowed operations by instance
     * @returns array of allowed operations by instance
     */
    abstract protected function getAllowedOperation();
    
    abstract protected function extractAPIResponse($apiResponse);

    /**
     * Get api instance of specific type
     * @param string $apiUserId your click a tell user name
     * @param string $apiPassword account password 
     * @param string $apiClientId Click a tell api id
     * @param string $type API type
     * @return \rkt\MessageAPI\BaseMessageAPI
     * @throws Exception\MessageAPIException
     */
    public static function getAPI($apiUserId, $apiPassword, $apiClientId, $type = self::API_HTTP)
    {
        switch ($type)
        {
            case self::API_HTTP:
                return new HttpAPI($apiUserId, $apiPassword, $apiClientId);
            case self::API_XML:
                throw new Exception\MessageAPIException("XML Api not implemented yet");
            default :
                new \Exception("Invalid API instance type");
        }
    }
    
    /**
     * Initiate SMS API
     * @param String $apiUserId
     * @param String $apiPassword
     * @param String $apiClientId
     * @param boolean $useSSL
     * @param String $httpClient
     */
    public function initAPI($useSSL = true, $httpClientType = self::HTTP_CLIENT_CURL)
    {
        $this->useSSL = $useSSL;
        $this->url = $useSSL ? self::API_BASE_URL_SECURE : self::API_BASE_URL;
        $this->_httpClientType = $httpClientType;
        $this->httpClient = $this->getHttpClient();
        $this->_apiInited = true;
        $this->authnicate();
    }

    /**
     *
     * @return HttpClient\BaseHttpClient
     */
    public function getHttpClient()
    {
        if(!$this->httpClient instanceof BaseHttpClient)
        {
            $this->httpClient = HttpClient\BaseHttpClient::getInstance($this->_httpClientType);
        }
        return $this->httpClient;
    }
    
    /**
     *
     * returns api message id of last message sent
     * @return mixed 
     */
    public function getLastMessageId()
    {
        return $this->messageId;
    }
    
    /**
     *
     * returns api session id
     * @return string api session id
     */
    public function getApiSessionId()
    {
        if(empty($this->apiSessionId))
        {
            $this->authnicate();
        }
        return $this->apiSessionId;
    }

    /**
     *  Add 'TO' numbers 
     * 
     *  @param mix (string|array) $numbers numbers to add 
     */
    public function addNumbers($numbers)
    {
        if(!is_array($numbers))
        {
            $numbers = array($numbers);
        }
        foreach ($numbers as  $number) 
        {
            $this->numbers[] = $number;
        }
    }

    /**
     * Returns error message for specified error number
     * @return string
     */
    public function getErrorMessage($errorNumber)
    {
        $errorMessage = '';
        if(array_key_exists($errorNumber, $this->errorMessages))
        {
            $errorMessage = $this->errorMessages[$errorNumber];
        }
        return $errorMessage;
    }
  
    /**
     *
     * Check if api has been inited or not
     * @throws Exception\MessageAPIException 
     */    
    protected function apiInitCheck()
    {
        if(!$this->_apiInited)
        {
            throw new Exception\MessageAPIException("Please init() API.");
        }
    }
    
    /**
     *
     * clean number
     * @param string $number
     */
    protected function cleanNumber(&$number)
    {
        /* format $to number */
        $charsToClean = array ("+", " ", "(", ")", "\r", "\n", "\r\n");
        $number = str_replace($charsToClean, "", trim($number));
    }
}