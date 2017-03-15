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
class Usps
{

    private $server = "http://production.shippingapis.com/ShippingAPI.dll";
    private $boxWidth = 0;
    private $boxHeight = 0;
    private $boxDepth = 0;
    private $uspsAccount = '';
    private $uspsPassword = '';
    private $uspsHanldingFee = 0;
    private $originZipCode = '';
    private $address;
    private $city;
    private $state;
    private $postalcode;
    private $country;
    private $boxes;
    private $service = 'ALL';
    private $container = 'VARIABLE';
    private $methods = array(
        '0' => array('label' => 'USPS First Class Mail', 'handle_charge' => 0, 'enabled' => 0),
        '1' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup>', 'handle_charge' => 0, 'enabled' => 0),
        '2' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Hold For Pickup', 'handle_charge' => 0, 'enabled' => 0),
        '3' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup>', 'handle_charge' => 0, 'enabled' => 0),
        '4' => array('label' => 'USPS Retail Ground<sup>™</sup>', 'handle_charge' => 0, 'enabled' => 0),
        '5' => array('label' => 'USPS Bound Printed Matter', 'handle_charge' => 0, 'enabled' => 0),
        '6' => array('label' => 'USPS Media Mail Parcel', 'handle_charge' => 0, 'enabled' => 0),
        '7' => array('label' => 'USPS Library Mail Parcel', 'handle_charge' => 0, 'enabled' => 0),
        '13' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '16' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '17' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Medium Flat Rate Box', 'handle_charge' => 0, 'enabled' => 0),
        '22' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Large Flat Rate Box', 'handle_charge' => 0, 'enabled' => 0),
        '23' => array('label' => '', 'handle_charge' => 0, 'enabled' => 0),
        '25' => array('label' => '', 'handle_charge' => 0, 'enabled' => 0),
        '27' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Flat Rate Envelope Hold For Pickup', 'handle_charge' => 0, 'enabled' => 0),
        '28' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Small Flat Rate Box', 'handle_charge' => 0, 'enabled' => 0),
        '29' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Padded Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '30' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Legal Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '31' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Legal Flat Rate Envelope Hold For Pickup', 'handle_charge' => 0, 'enabled' => 0),
        '38' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Gift Card Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '40' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Window Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '42' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Small Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '44' => array('label' => 'USPS Priority Mail 2-Day<sup>™</sup> Legal Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '62' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Padded Flat Rate Envelope', 'handle_charge' => 0, 'enabled' => 0),
        '63' => array('label' => 'USPS Priority Mail Express 1-Day<sup>™</sup> Padded Flat Rate Envelope Hold For Pickup', 'handle_charge' => 0, 'enabled' => 0),
        );

    /**
     * @ignore
     */
    public function __construct($account, $password)
    {
        $this->uspsAccount = $account;
        $this->uspsPassword = $password;
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * post data in fields to url
     * 
     * @param  string $cUrl   [URL]
     * @return boolean 
     */
    private static function curl($url, $data, $headers = array(), $method = 'GET', $debug = 0)
    {        
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


    /**
     * [getAddress description]
     * @return [type] [description]
     */
    public function getAddress() 
    {
        return $this->address . ',' 
        . $this->city . ',' 
        . $this->state . ',' 
        . $this->postalcode . ',' 
        . $this->country;        
    }

    /**
     * [getRates description]
     * @return [type] [description]
     */
    public function getRates() { 

        $cPackages = '';

        $aRates = array();

        foreach ($this->boxes AS $nBox => $aBox) {
            $nPound = 0;
            $nOunze = ( $aBox['weight'] * 16 );                        
            if ($this->country != 'US' && $this->country != '234') {            
                (empty($aBox['width']) || $aBox['width'] == 0) ? $aBox['width'] = $this->boxWidth : false;
                (empty($aBox['height']) || $aBox['height'] == 0) ? $aBox['height'] = $this->boxHeight : false;
                (empty($aBox['depth']) || $aBox['depth'] == 0) ? $aBox['depth'] = $this->boxDepth : false;            
                $cPackages .= "<Package ID=\"$nBox\">";
                $cPackages .= '<Pounds>' . $nPound . '</Pounds>';
                $cPackages .= '<Ounces>' . $nOunze . '</Ounces>';
                //$cPackages .= '<Machinable>True</Machinable>';
                $cPackages .= '<MailType>Package</MailType>';
                $cPackages .= '<ValueOfContents>' . $aBox['value'] . '</ValueOfContents>';
                $cPackages .= '<Country>' . $this->country . '</Country>';
                $cPackages .= '<Container>Rectangular</Container>';
                //$cPackages .= '<Size>Regular</Size>';
                $cPackages .= '<Width>' . $aBox['width'] . '</Width>';
                $cPackages .= '<Length>' . $aBox['depth'] . '</Length>';
                $cPackages .= '<Height>' . $aBox['height'] . '</Height>';
                $cPackages .= '<Girth>' . ( ( 2 * $aBox['height'] ) + ( 2 * $aBox['width'] ) ) . '</Girth>';
                $cPackages .= '<OriginZip>' . $this->originZipCode . '</OriginZip>';
                $cPackages .= '<CommercialFlag>N</CommercialFlag>';
                $cPackages .= '</Package>';
            } else {
                $cPackages .= "<Package ID=\"$nBox\"><Service>"
                . $this->service . "</Service><FirstClassMailType>FLAT</FirstClassMailType><ZipOrigination>"
                . $this->originZipCode . "</ZipOrigination><ZipDestination>"
                . str_replace(' ', '', $this->postalcode) . "</ZipDestination><Pounds>"
                . $nPound . "</Pounds><Ounces>"
                . $nOunze . "</Ounces><Container>" . $this->container . "</Container><Size>REGULAR</Size><Machinable>True</Machinable></Package>";
            }
        }        

        if ($this->country != 'US' && $this->country != '234') {
            $data = "API=IntlRateV2&XML=<IntlRateV2Request USERID=\""
            . $this->uspsAccount
            . "\" PASSWORD=\""
            . $this->uspsPassword
            . "\"><Revision>2</Revision>";
            $data .= $cPackages;
            $data .= '</IntlRateV2Request>';
        } else {
            $data = "API=RateV4&XML=<RateV4Request USERID=\""
            . $this->uspsAccount
            . "\" PASSWORD=\""
            . $this->uspsPassword
            . "\">";
            $data .= $cPackages;
            $data .= '</RateV4Request>';
        }

        $result = $this->curl($this->server, $data, array(), 'POST');

        //$data = strstr($result, '<?');
       
        if (!empty($result->response)) {
            $xml_parser = xml_parser_create();
            xml_parse_into_struct($xml_parser, $result->response, $vals, $index);
            xml_parser_free($xml_parser);
            $params = $level = array();
            foreach ($vals AS $xml_elem) {
                if ($xml_elem['type'] == 'open') {
                    if ( array_key_exists('attributes', $xml_elem ) ) {
                        list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
                    } else {
                        $level[$xml_elem['level']] = $xml_elem['tag'];
                    }
                }
                if ($xml_elem['type'] == 'complete') {
                    $start_level = 1;
                    $php_stmt = '$params';
                    while ($start_level < $xml_elem['level']) {
                        $php_stmt .= '[$level['.$start_level.']]';
                        $start_level++;
                    }
                    $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
                    eval($php_stmt);
                }
            }
        } else {
            $params['ERROR'] = array( 
                'DESCRIPTION' => 
                '80040b1a: Authorization failure.  You are not authorized to connect to this server.' );
        }

        if ($this->country != 'US' && $this->country != '234') {            
            if (!empty($params['ERROR'])) {                
                $aRates['ERROR'] = $params['ERROR']['DESCRIPTION'];
            } else {
                foreach ($params['INTLRATEV2RESPONSE'] AS $nPackage => $aPackage) {
                    foreach ($aPackage AS $c => $v) {
                        if ( is_numeric( $c ) ) {
                            if (
                                ( $c == 15 && $this->methods[0]['enabled'] )
                                ||
                                ( $c == 1 && $this->methods[3]['enabled'])
                                ||
                                ( $c == 2 && $this->methods[1]['enabled']))
                            {
                                if (!array_key_exists( $aPackage[$c]['SVCDESCRIPTION'], $aRates ) ) {
                                    $aPackage[$c]['RATE'] = ($aPackage[$c]['POSTAGE'] + $aBoxes[$nPackage]['handling'] + $this->uspsHanldingFee);
                                    $aPackage[$c]['ADDRESS'] = $this->getAddress();
                                    $aRates[$aPackage[$c]['SVCDESCRIPTION']] = $aPackage[$c];
                                } else {
                                    $aRates[$aPackage[$c]['SVCDESCRIPTION']]['RATE'] += ($aPackage[$c]['POSTAGE'] + $aBoxes[$nPackage]['handling'] + $this->uspsHanldingFee);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (!empty($params['RATEV4RESPONSE'][0]['ERROR'])) {
                $aRates['ERROR'] = $params['RATEV4RESPONSE'][0]['ERROR']['DESCRIPTION'];
            } else {
                foreach ($params['RATEV4RESPONSE'] AS $nPackage => $aPackage) {
                    foreach ($aPackage AS $c => $v) {
                        if (is_numeric($c)) {
                            if ($v['RATE'] > 0 && $this->methods[$c]['enabled']) {
                                if ( !array_key_exists($aPackage[$c]['MAILSERVICE'], $aRates)) {
                                    $aPackage[$c]['RATE'] = ( $aPackage[$c]['RATE'] + $aBoxes[$nPackage]['handling'] + $this->uspsHanldingFee );
                                    $aPackage[$c]['ADDRESS'] = $this->getAddress();
                                    $aRates[$aPackage[$c]['MAILSERVICE']] = $aPackage[$c];
                                } else {
                                    $aRates[$aPackage[$c]['MAILSERVICE']]['RATE'] += ( $aPackage[$c]['RATE'] + $aBoxes[$nPackage]['handling'] + $this->uspsHanldingFee );
                                }
                            }
                        }
                    }
                }
            }
        }
        return $aRates;
    }

    /**
     * [setAddress description]
     */
    public function setAddress($vars = array())
    {

        if (!empty($vars['address'])) { 
            $this->address = $vars['address'];
            !empty($vars['city']) ? $this->city = $vars['city'] : false;
            !empty($vars['state']) ? $this->state = $vars['state'] : false;
            !empty($vars['postalcode']) ? $this->postalcode = $vars['postalcode'] : false;
            !empty($vars['country']) ? $this->country = $vars['country'] : false;
        } else {
            $this->address = '';
            $this->city = '';
            $this->state = '';
            $this->postalcode = '';
            $this->country = '';
        }
    }

    /**
     * [setAvailableMethods description]
     */
    public function setAvailableMethods($methods = array())
    { 
        foreach ($methods as $key => $value) { 
            if (preg_match("/^usps_method/is", $key)) {             
                $method = substr($key, (strrpos($key, '_')+1));
                if (array_key_exists($method, $this->methods)) {  
                    ($value) ? $this->methods[$method]['enabled'] = 1 : false;
                }                   
            }
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
