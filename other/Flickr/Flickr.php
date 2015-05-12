<?php

namespace zeekee\other\Flickr;

class Flickr
{

    private $_apiEndpointUrl = 'https://api.flickr.com/services/rest/';
    private $_recordsPerRequest = 15;
    private $_returnFormat = 'php_serial';
    private $_method = '';
    private $_apiKey = '';
    private $_apiSecret = '';
    private $_apiUser = '';
    private $_vars;
    private $variables = array();
    public $result;    
    protected $error;

    /**
     * [__construct]
     * 
     * 
     */
    public function __construct($key = '')
    {
        // key (required)
        if (isset($key)) {
            $this->_apiKey = (string) $key;
        } else {
            throw new Exception('Must provide a Flickr API key.');
        }
    }

    /**
     * [__destruct]
     * 
     * 
     * 
     */
    public function __destruct()
    {

    }

    /**
    *
    * @set undefined vars
    * 
    * @param string $index
    * @param mixed $value
    * 
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
    * @return void
    *
    */
    public function __get($index)
    {
        return $this->_vars[$index];
    }


    private function curlGET($url, $queryString) 
    {


        $referer = $this->_apiEndpointUrl;
        $url = $url . '?' . $queryString;    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_REFERER, $referer);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
        $str = curl_exec($curl);
        curl_close($curl);

        return $str;
    }    

    /**
     * post data in fields to url
     * 
     * @param  string $cUrl     [URL to post]
     * @param  mixed string|array $cFields  [array or string of values to post]
     * @param  string &$cResult [string reference to pass back any errors]
     * @return boolean 
     */
    private static function curlPOST($cUrl, $cFields, &$cResult)
    {

        $ch = curl_init();                  // URL of gateway for cURL to post to
        curl_setopt($ch, CURLOPT_URL, $this->_servername . $this->_url);
        curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cPost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
            curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt');
        }
        $cResult = curl_exec($ch);

        if (!$cResult) {
            $cResult = curl_error($ch) . '::' . curl_errno($ch);
            curl_close($ch);

            return false;

        } else {
            return true;
        }
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function requestResult()
    {
        if ($this->result['stat'] == 'ok') { 
            return true;
        } else { 
            $this->error = $this->result['code'] . ' : ' . $this->result['message'];
            return false;
        }
    }

     /**
     * 
     * 
     * @return unserailized data 
     */ 
    public function sendRequest($params = array())
    {

        $array = array(
            'method' => $this->_method,
            'api_key' => $this->_apiKey,
            'format' => $this->_returnFormat
        );

        if (!empty($this->_apiUser)) { $array['user_id'] = $this->_apiUser; }

        is_array($params) ? $array = array_merge($params, $array) : false;

        $queryString = http_build_query($array);

        $this->result = unserialize($this->curlGet($this->_apiEndpointUrl, $queryString));        

        return $this->result;
    
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function setMethod($method = null) 
    {
        !is_null($this->_method) ? $this->_method = $method : $this->_method = 'flickr.test.echo';
        $this->_method = str_replace('zeekee.other.', '', $this->_method);
        return $this;
    }

    /**
     * set the user_id field to be assocaiated with all requests.
     * 
     * 
     */ 
    public function setUserId($id) { 
        $this->_apiUser = $id;
        return $this;
    }

}
