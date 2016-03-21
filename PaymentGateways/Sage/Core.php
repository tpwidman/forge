<?php

namespace Sage;

class Core
{

    /**
     *    Merchant Id Number:  
     *    VT_ID / M_ID:  
     *    User Id:  
     *    Password:  
     */ 

    private $_vars;
    private $_xml;
    protected $_server = 'https://gateway.sagepayments.net/web_services/gateway/api/';
    private $_endpoint = '';
    private $_postHeaders = array();
    private $_response;
    private $_headerSent;    
    private $_debugPost = array('headers' => '', 'response' => '', 'query' => '');

    private $_VaultTransactionRequest = array(
        'RequestType' => '',
        'Token' => ''
        );


    protected $mKey = '';
    protected $mId = '';
    protected $applicationId = 'ZEEKTYFO1000000WMSYABY3USEN';
    protected $verb = 'POST';
   
    public $debug = 0;
    
    /**
     * @param $merchantId [The merchant id associated with this Sage Transcation]
     *      
     */
    public function __construct($mId, $mKey, $applicationId)
    {

        $this->mKey = $mKey;
        $this->mId = $mId;
        $this->applicationId = $applicationId;
        $this->resetHeaders();

    }

    /**
     * [__destruct description]
     */
    public function __destruct()
    {

    }

    /**
    *
    * @set undefined vars
    * @param string $index
    * @param mixed $value
    * @return void
    *
    */
    public function __set($index, $value)
    {
        $this->_vars[$index] = $value;
    }


    /**
    *
    * @get variables
    *
    * @param mixed $index
    *
    * @return mixed
    *
    */
    public function __get($index)
    {
        return $this->_vars[$index];
    }

    /**
     * 
     */
    public function curlPOST($server, $data, $headers)
    {

        # $server = 'http://requestb.in/1m3vgak1';

        $curlHeaders = array(
            );

        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                $curlHeaders[] = "$key: $value";
            }                    
        }

        if (is_array($headers)) { 
            if (strpos($headers['Content-Type'], 'xml') > 0 ) {
                $query = $data;
            } else { 
                $query = http_build_query($data, '', '&');    
            }
        } else { 
            $query = http_build_query($data, '', '&');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);                
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        
        ($this->debug) ? curl_setopt($ch, CURLINFO_HEADER_OUT, true) : false;
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, trim($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);        
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        empty($response) ? $response = curl_error($ch) : false;
        curl_close($ch);

        $this->_response['response'] = $response;

        if ($this->debug) { 
            $this->_response['headers'] = $headerSent;    
            $this->_response['query'] = htmlentities($query) ;                    
        }
        
        return $response;

    }

    /**
     * 
     * 
     */
     public function debug() {
        echo '<pre>';
        print_r($this->_response);
        echo '</pre>';

     } 


    /**
     * 
     * 
     */
     public function dumpVariable($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';

     } 

    /**
     * 
     * 
     */ 
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * 
     * 
     * 
     */ 
    protected function getHeaders()
    {
        return $this->_postHeaders;
    }
   
    /**
     * 
     * 
     */ 
    public function getResponse()
    {
        return $this->_server;
    }

    /**
     * 
     * 
     */ 
    public function getServer()
    {
        return $this->_server;
    }

    /**
    * function to determine if string is JSON format.
    * 
    * @param  $string - the string to test
    * 
    * @return  true/false
    */ 
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);    
    }


    /**
     * 
     * 
     */ 
    public function makeHmac($content)
    {

        return base64_encode(hash_hmac('sha1', $this->verb . $this->_server . $this->_endpoint . $content, $this->mKey, true));        

    }

    /**
     * 
     * 
     * 
     */ 
    protected function parseResponse($response)
    {

        if ($this->isJson($response)) { 
            $array = json_decode($response);
        } else { 
            $array = simplexml_load_string($response);
        }  

        return $array;

    }       

    /**
    * 
    * 
    * 
    */ 
    public function resetHeaders()
    {
        $this->_postHeaders = array();
        # $this->_postHeaders['Cache-Control'] = 'no-cache';
        $this->_postHeaders['charset'] = '';
        $this->_postHeaders['Content-Type'] = 'application/xml; utf-8';
        $this->_postHeaders['Accept'] = 'application/xml';

    }

    public function setAuthenticationHeader($payload)
    {

        $hmac = $this->makeHmac($payload);

        $this->_postHeaders['Authentication'] = $this->mId . ':' .$hmac;

    }


    /**
    * 
    * 
    * 
    */ 
    public function setEndPoint($var)
    {
        $this->_endpoint = $var;
    }

    /**
    * 
    * 
    * 
    */ 
    public function setHeader($key, $value)
    {
        $this->_postHeaders[$key] = $value;
    }

    /**
     * 
     * 
     */ 
    public function merchantId($id)
    {
        $this->merchantId = $id;
    }

    /**
    * 
    * 
    * 
    */ 
    public function setVerb($verb)
    {
        $this->verb = $verb;
    }

    /**
     * 
     * 
     */
    protected function pi()
    {
        return rand();
    }

    /**
     * [xmlCreate]
     * @param  string $startTag
     * @param  array $array    
     * @return string          
     */
    protected function xmlCreate($startTag, $array)
    {
        $xml = new \XmlWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'utf-8');
        $xml->startElement($startTag);
        $this->xmlWrite($xml, $array);
        $xml->endElement();
        return $xml->outputMemory(true);
    }

    /**
     * [writeXml]
     * @param  XMLWriter $xml  : Standard XMLWriter Class
     * @param  array
     * @return string
     */
    protected function xmlWrite(\XMLWriter $xml, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml->startElement($key);
                $this->xmlWrite($xml, $value);
                $xml->endElement();
                continue;
            }
            $xml->writeElement($key, $value);
        }
    }
}
    