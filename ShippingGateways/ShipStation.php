<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
/**
 * A set of classes/methods making it easier to use the ShipStation API, please note 
 * that you are responsible for any charges incurred by using the Google API.  We always ensure
 * our clients have proper licensing
 */  
class ShipStation
{

    private $apikey;
    private $apisecret;
    private $endpoint;
    private $httpMethod;
    private $authenticationToken;

    private $server = 'https://ssapi.shipstation.com';

    private $boxWidth = 0;
    private $boxHeight = 0;
    private $boxDepth = 0;
    private $originZipCode = '';
    private $address;
    private $toCity;
    private $toState;
    private $toPostalCode;
    private $toCountry;
    private $boxes;
    private $methods = array();

    /**
     * @ignore
     */
    function __construct($username = 'xxxxxxxxxxxx', $password = 'xxxxxxxxxx')
    {

        $this->apikey = $username;
        $this->apisecret = $password;    
        $this->buildAuthenticationToken();
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * [buildAuthenticationToken description]
     * @return [type] [description]
     */
    private function buildAuthenticationToken()
    {
        $this->authenticationToken = base64_encode($this->apikey . ':' . $this->apisecret);       

    }    

    /**
     * post data in fields to url
     * 
     * @param  string $cUrl   [URL]
     * @return boolean 
     */
    private function curl($endpoint, $data = '', $headers = array(), $method = 'GET', $debug = 0)
    {        

        $url = $this->server . $endpoint;

        $headers['Authorization'] = 'Basic ' . $this->authenticationToken;        

        $output = array();

        $httpAuth = false;

        $httpAuthValue = false;

        $curlHeaders = array();

        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                if (array_key_exists('HTTPAUTH', $headers)) {
                    $httpAuth = true;
                    $httpAuthValue = $headers['HTTPAUTH'];
                } else { 
                    $curlHeaders[] = "$key: $value";
                }
            }                    
        }

        if (is_array($headers)) { 
            if (array_key_exists('Content-Type', $headers) && strpos($headers['Content-Type'], 'xml') > 0 ) {
                $query = $data;
            } else {     
                if (is_object($data) || is_array($data)) {            
                    $query = trim(http_build_query($data, '', '&'));    
                } else { 
                    $query = $data;
                }
            }
        } else { 
            if (is_object($data) || is_array($data)) {            
                $query = trim(http_build_query($data, '', '&'));    
            } else { 
                $query = $data;
            }
        }

        ($method == 'GET' && !empty($query)) ? $url = $url . '?'. $query : false;

        $ch = curl_init();                  // URL of gateway for cURL to post to
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/'); // to prevent error code 500
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) ? curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt') : false;

        if ($httpAuth) { 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $httpAuthValue);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders); 
    
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);                
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);            
        }

        if ($debug) { 
            curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        } else { 
            curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response       
            curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        }
        
        $response = curl_exec($ch);    
        $output = new StdClass();
        
        if ($debug) { 
            $output = (object) curl_getinfo($ch);
            $output->query = trim($query);
            $output->headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        }
                
        if (!$response) {
            $output->response = curl_error($ch) . '::' . curl_errno($ch); 
        } else { 
            $output->response = $response;    
        }    

        return $output;
    }   

    public function dump($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    } 


    /**
     * [getAutenticationToken description]
     * @return [type] [description]
     */
    public function getAutenticationToken()
    {
        return $this->authenticationToken;
    }
    
    /**
     * [getCarrier description]
     * @return [type] [description]
     */
    public function getCarrier($carrier = '')
    {
        // error
        return $this->curl('/carriers/getcarrier', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [getCustomer description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCustomer($id)
    {
        // error
        return $this->curl('/carriers/' . $id,'', array(),'GET', 0);

    }

    /**
     * [getRates description]
     * @return [type]       [description]
     */
    public function getRates()
    {        

        $return = array();

        $carriers = $this->listCarriers();

        foreach ($this->boxes as $n => $box) { 
            foreach ($carriers as $key => $value) {                 
                $return[$value->code] = array();
                $vars = array(
                    'carrier' => $value->code,
                    'fromPostalCode' => $this->originZipCode,
                    'toState' => $this->toState,
                    'toCountry' => $this->toCountry,
                    'toPostalCode' => $this->toPostalCode,
                    'toCity' => $this->toCity,
                    'weight' => ($box['weight']*16) // conversion to ounces
                    );

                $rates = json_decode($this->getRate($vars)->response);

                foreach ($rates as $r => $rate) { 
                    $return[$value->code][$rate->serviceName] = array(
                        'MAILSERVICE' => $rate->serviceName,
                        'RATE' => $rate->shipmentCost,
                        'ADDRESS' => $this->getAddress()
                        );
                }                
            }
        }

        if (sizeof($carriers) == 0) { 
            $return['ERROR']['ERROR'] = array(
                'MAILSERVICE' => 'ERROR',
                'RATE' => '',
                'ADDRESS' => $this->getAddress()
            );
        }

        return $return;

    }

    /**
     * [getAddress description]
     * @return [type] [description]
     */
    public function getAddress() 
    {
        return $this->address . ',' 
        . $this->toCity . ',' 
        . $this->toState . ',' 
        . $this->toPostalCode . ',' 
        . $this->toCountry;        
    }

    /**
     * [getRate description]
     * @param  [type] $vars [description]
     * @return [type]       [description]
     */
    private function getRate($vars)
    {
        
        /*
        "dimensions" => array(
                'length' => 7, 
                'width' => 6, 
                'height' => 5, 
                'units' => 'inches'),  
         */

        $query = array(
            "carrierCode" => $vars['carrier'],
            "serviceCode" => null,
            "packageCode" => null,
            "fromPostalCode" => $vars['fromPostalCode'],
            "toState" => $vars['toState'],
            "toCountry" => $vars['toCountry'],
            "toPostalCode" => $vars['toPostalCode'],
            "toCity" => $vars['toCity'],
            "weight" => array('value' => $vars['weight'], 'units' => 'ounces'),            
            "confirmation" => "delivery",
            "residential" => 'false'
            );
        return $this->curl('/shipments/getrates', $query, array(), 'POST');

    }

    /**
     * [listCarriers description]
     * @return [type] [description]
     */
    public function listCarriers()
    {
        return json_decode($this->curl('/carriers','',array())->response);

    }

    /**
     * [listCustomers description]
     * @return [type] [description]
     */
    public function listCustomers($vars = array())
    {
        return $this->curl('/customers', $vars, array());

    }

    /**
     * [listCustomers description]
     * @return [type] [description]
     */
    public function listFulfillments($vars = array())
    {
        return $this->curl('/fulfillments', $vars, array());

    }

     /**
     * [listPackages description]
     * @param  string $carrier [description]
     * @return [type]          [description]
     */
    public function listPackages($carrier = '')
    {
        // error
        return $this->curl('/carriers/listpackages', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [listServices description]
     * @param  string $carrier [description]
     * @return [type]          [description]
     */
    public function listServices($carrier = '')
    {
        // error
        return $this->curl('/carriers/listservices', 
            array('carrierCode' => $carrier), 
            array(),'GET', 0);

    }

    /**
     * [listTags description]
     * @return [type] [description]
     */
    public function listTags()
    {
        return $this->curl('/accounts/listtags','',array());

    }

    /**
     * [setServer description]
     */
    public function setServer()
    {

    }

    /**
     * [setAddress description]
     */
    public function setAddress($vars = array())
    {

        if (!empty($vars['address'])) { 
            $this->address = $vars['address'];
            !empty($vars['city']) ? $this->toCity = $vars['city'] : false;
            !empty($vars['state']) ? $this->toState = $vars['state'] : false;
            !empty($vars['postalcode']) ? $this->toPostalCode = $vars['postalcode'] : false;
            !empty($vars['country']) ? $this->toCountry = $vars['country'] : false;
        } else {
            $this->address = '';
            $this->toCity = '';
            $this->toState = '';
            $this->toPostalCode = '';
            $this->toCountry = '';
        }
    }

    /**
     * [setBoxes description]
     * @param [type] $boxes [description]
     */
    public function setBoxes($boxes) { 
        $this->boxes = $boxes;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setDefaultBox($height, $width, $depth) 
    {
        $this->boxDepth = $depth;
        $this->boxWidth = $width;
        $this->boxHeight - $height;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setHandlingFee($fee) 
    {
        $this->uspsHanldingFee = $fee;
    }

    /**
     * [set the handling fee associated with all requests]
     */
    public function setOriginZipCode($code) 
    {
        $this->originZipCode = $code;
    }

}
