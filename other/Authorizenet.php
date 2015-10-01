<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
/**
 * A set of classes/methods making it easier to use the Google API, please note 
 * that you are responsible for any charges incurred by using the Google API.  We always ensure
 * our clients have proper licensing
 */  
class Authorizenet
{

    private $server = 'https://secure2.authorize.net/gateway/transact.dll';

    /**
     * @ignore
     */
    public function __construct()
    {

    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    public function authorizeCapture()
    {

    }

    public function authorizeOnly()
    {

    }

    public function captureAuthorization()
    {

    }


    public function captureOnly()
    {

    }

    public function credit()
    {

    }

    public function unlinkedCredit()
    {

    }

    public function void()
    {

    }

    public function enableTestMode()
    {
        $this->server = 'https://test.authorize.net/gateway/transact.dll';
    }

}
