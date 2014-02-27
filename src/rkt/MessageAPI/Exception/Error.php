<?php
namespace   rkt\MessageAPI\Exception;

/**
 * Error constants
 *
 * @author Ravish Tiwari <ravishktiwari@hotmail.com>
 */
interface Error 
{
    /**
     * Authorization error
     */
    const ERR_AUTH_FAIL = '001';

    /**
     * Invalid API Param
     */
    const ERR_INVALID_API_PARAM = 3246;
}