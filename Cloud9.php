<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 *
 * Test API URL: https://partner-east-test.cloud9ortho.com/GetData.ashx
 * Username: ZeeKee
 * Password: pa$$word
 * Client ID: 35f38cb1-7792-4f5f-9c72-4802b46d19c9
 * 
 * https://partner.cloud9ortho.com or https://partner-east.cloud9ortho.com 
 * to https://atl-partner.cloud9ortho.com on the agreed upon date and time.
 */  
class Cloud9
{

    private $username;

    private $password;

    private $clientId;

    private $server = 'https://partner-east-test.cloud9ortho.com/GetData.ashx';

    //private $server = 'https://atl-partner-test.cloud9ortho.com/GetData.ashx';

    private $requestXml = array(
        'ClientID' => '',
        'UserName' => '',
        'Password' => '',
        'Procedure' => '',
        'Parameters' => array()
    );

    private $xmlHeader = '<GetDataRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.practica.ws/cloud9/partners/">';


    /**
     * @ignore
     */
    public function __construct($username = '', $password = '', $clientId = '')
    {
        $this->requestXml['ClientID'] = $clientId;
        $this->requestXml['UserName'] = $username; 
        $this->requestXml['Password'] = $password;
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
    private function curl($endpoint, $data = '', $headers = array(), $method = 'GET', $debug = 0)
    {        

        $url = $endpoint;

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

    /**
     * [return a properly formated timestamp for this platform]
     * @param  string  $date     [a string representing the datetime value]
     * @param  boolean $dateOnly [return only a formated date string not a datetime]
     * @return string            [the properly formated timestamp.]
     */
    private function datetimestamp($date, $dateOnly = true) 
    { 
        
        if ($dateOnly) { 
            empty($date) ? $date = date('m/d/Y 00:00:00 \A\M') : $date = date('m/d/Y', strtotime($date));
        } else { 
            empty($date) ? $date = date('m/d/Y 00:00 \A\M') : $date = date('m/d/Y 00:00 \A\M', strtotime($date));
        }
        return $date;
    }

    /**
     * [dumpout the contents of a variable]
     * @param  [various] $var [a variable to be dumped.]
     * @return none
     */
    private function dump($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    /**
     * 
     * @param $date valid date stamp - 12/30/1999 12:00:00 AM
     * 
     */ 
    public function GetAppointmentsByDate($date = '') 
    { 
        $this->requestXml['Procedure'] = 'GetAppointmentsByDate';
        $this->requestXml['Parameters']['dtAppointment'] = $this->datetimestamp($date);
        return $this->processRequsest();

    }


    /**
     * 
     */ 
    public function GetAppointmentListByDate($date) 
    {
        $this->requestXml['Procedure'] = 'GetAppointmentListByDate';

        $start = date("m/d/Y 00:00:00", strtotime($date));
        $end = date("m/d/Y 23:59:59", strtotime($date));
        $this->requestXml['Parameters']['dtAppointment'] = $start;
        $this->requestXml['Parameters']['dtAppointmentEnd'] = $end;
        return $this->processRequsest();
    }

    /**
     * 
     * @param $date valid date stamp - 12/30/1999 12:00:00 AM
     * 
     */ 
    public function GetAppointmentListSince($date = '') 
    { 
    
        $this->requestXml['Procedure'] = 'GetAppointmentListSince';
        $this->requestXml['Parameters']['dtSince'] = $this->datetimestamp($date);
        return $this->processRequsest();
    
    }


    

    public function getLocations()
    {
        $this->requestXml['Procedure'] = 'GetLocations';    
        $response = $this->processRequsest();        

        if ($response->ResponseStatus == 'Success') { 
            $array = array();
            foreach ($response->Records->Record as $n => $record) { 
                $array[] = $record;
            }
            return $array;
        } else { 
            return array();
        }

    }

    
    /**
     * 
     * This stored procedure provides a list of all responsible party phone numbers in the database.
     *
     * @return array [all the values returned = key is the ResponsiblePartyGUID]
     */ 
    public function getResponsiblePartyPhone() 
    { 
        $array = array();
        
        $this->requestXml['Procedure'] = 'GetResponsiblePartyPhone';        
        
        $response = $this->processRequsest();        
        if ($response->ResponseStatus == 'Success') {             
            foreach ($response->Records->Record as $n => $record) {                 
                !array_key_exists(strval($record->ResponsiblePartyGUID), $array) ? $array[strval($record->ResponsiblePartyGUID)] = array() : false;
                $array[strval($record->ResponsiblePartyGUID)][] = $record;
            }        
        }
        return $array;        
    }

    /**
     * 
     * This stored procedure provides a list of all employees in the database.
     *
     * @return array [all the values returned = key is the EmployeeGUID]
     */ 
    public function getStaff() 
    { 
        $array = array();
        
        $this->requestXml['Procedure'] = 'GetStaff';        
        
        $response = $this->processRequsest();
        
        if ($response->ResponseStatus == 'Success') {             
            foreach ($response->Records->Record as $n => $record) {                 
                $array[strval($record->EmployeeGUID)] = $record;                
            }        
        }
        return $array;        
    }

    /**
     * 
     */ 
    private function parseResponse($response) 
    { 

        return simplexml_load_string($response->response);

    }

    /**
     * 
     */ 
    private function processRequsest() 
    { 

        $xml = $this->xmlCreate('GetDataRequest', $this->requestXml);

        $xml = str_replace('<GetDataRequest>', $this->xmlHeader, $xml);

        //echo htmlentities($xml);

        $resp = $this->curl($this->server, $xml, array('Content-Type' => 'text/xml'), 'POST');

        return $this->parseResponse($resp);

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
