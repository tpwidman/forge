<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 *
 * 
 * https://partner.cloud9ortho.com or https://partner-east.cloud9ortho.com 
 * to https://atl-partner.cloud9ortho.com on the agreed upon date and time.
 */  
class Cloud9
{

    private $username;

    private $password;

    private $clientId;

    private $server = 'https://partner.cloud9ortho.com/GetData.ashx';

    private $requestXml = array(
        'ClientID' => '',
        'UserName' => '',
        'Password' => '',
        'Procedure' => '',
        'Parameters' => array()
    );

    private $xmlHeader = '<GetDataRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.practica.ws/cloud9/partners/">';

    private $errorMessage;

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
     * 
     * 
     */ 
    private function datetimestamp($date, $dateOnly = true) 
    { 
        
        if ($dateOnly) { 
            empty($date) ? $date = date('m/d/Y') : $date = date('m/d/Y', strtotime($date));
        } else { 
            empty($date) ? $date = date('m/d/Y 00:00 \A\M') : $date = date('m/d/Y 00:00 \A\M', strtotime($date));
        }

        return $date;

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
     * [GetAppointmentStatusDescriptions description]
     */
    public function GetAppointmentStatusDescriptions()
    {
        $this->requestXml['Procedure'] = 'GetAppointmentStatusDescriptions';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }


    /**
     * 
     */ 
    public function GetAppointmentListByPatient($clientId) 
    {
        $this->requestXml['Procedure'] = 'GetAppointmentListByPatient';
        $this->requestXml['Parameters']['patGUID'] = $clientId;
        $response = $this->processRequsest();        
        return $this->parseResponse($response);            
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

    /**
     * [GetAppointmentStatusDescriptions description]
     */
    public function GetContracts()
    {
        $this->requestXml['Procedure'] = 'GetContracts';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);         
    }

    /**
     * [GetFeeSchedules description]
     */
    public function GetFeeSchedules()
    {
        $this->requestXml['Procedure'] = 'GetFeeSchedules';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetFeeScheduleEntries description]
     */
    public function GetFeeScheduleEntries($guid)
    {
        $this->requestXml['Procedure'] = 'GetFeeScheduleEntries';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);           
    }


    /**
     * [GetInsuranceContracts description]
     */
    public function GetInsuranceContracts()
    {
        $this->requestXml['Procedure'] = 'GetInsuranceContracts';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * [GetFeeScheduleTypes description]
     */
    public function GetFeeScheduleTypes()
    {
        $this->requestXml['Procedure'] = 'GetFeeScheduleTypes';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * This will return all the locations configured in the system.
     *
     * return array [an associative array with the LocationCode as the key]
     */
    public function GetLocations()
    {
        $return = array();
        $this->requestXml['Procedure'] = 'GetLocations';    
        $response = $this->processRequsest();        
        $array = $this->parseResponse($response);       
        foreach ($array as $k => $v) {             
            $v = (array) $v;            
            $return[$v['LocationCode']] = (object) $v;
        }
        return $return;
    }

    /**
     * [GetPartnerClientWindow description]
     */
    public function GetPartnerClientWindow()
    {
        $this->requestXml['Procedure'] = 'GetPartnerClientWindow';    
        $response = $this->processRequsest();        

        return $this->parseResponse($response);       
    }

    /**
     * [GetPatientAddress description]
     */
    public function GetPatientAddress()
    {
        $this->requestXml['Procedure'] = 'GetPatientAddress';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

     /**
     * [GetPatientEmail description]
     */
    public function GetPatientEmail()
    {
        $this->requestXml['Procedure'] = 'GetPatientEmail';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetPatientInsurancePolicies description]
     */
    public function GetPatientInsurancePolicies()
    {
        $this->requestXml['Procedure'] = 'GetPatientInsurancePolicies';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }
    
    /**
     * [GetPatientList description]
     */
    public function GetPatientList($date = '')
    {
        $this->requestXml['Procedure'] = 'GetPatientList';  
        $response = $this->processRequsest();  
        return $this->parseResponse($response);
    }

    /**
     * [GetPatientPhone description]
     */
    public function GetPatientPhone()
    {
        $this->requestXml['Procedure'] = 'GetPatientPhone';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetPatientReferralLinks description]
     */
    public function GetPatientReferralLinks()
    {
        $this->requestXml['Procedure'] = 'GetPatientReferralLinks';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetPatientResponsiblePartyLinks description]
     */
    public function GetPatientResponsiblePartyLinks()
    {
        $this->requestXml['Procedure'] = 'GetPatientResponsiblePartyLinks';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetResponsiblePartyEmail description]
     */
    public function GetResponsiblePartyEmail()
    {
        $this->requestXml['Procedure'] = 'GetResponsiblePartyEmail';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    public function GetResponsiblePartyEmailByPatient($clientId) 
    {
        $this->requestXml['Procedure'] = 'GetResponsiblePartyEmailByPatient';
        $this->requestXml['Parameters']['patGUID'] = $clientId;
        $response = $this->processRequsest();        
        return $this->parseResponse($response);               
    }

    

     /**
     * [GetPatientStatuses description]
     */
    public function GetPatientStatuses()
    {
        $this->requestXml['Procedure'] = 'GetPatientStatuses';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

     /**
     * [GetResponsiblePartyList description]
     */
    public function GetResponsiblePartyList()
    {
        $this->requestXml['Procedure'] = 'GetResponsiblePartyList';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

     /**
     * [GetResponsiblePartyPhone] This stored procedure provides a list of all 
     * responsible party phone numbers in the database.
     *
     * @return array [list of all responsible party phone numbers in the database]
     */
    public function GetResponsiblePartyPhone()
    {
        $this->requestXml['Procedure'] = 'GetResponsiblePartyPhone';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }
    
    /**
     * [GetReferrals] This stored procedure provides a list of every professional 
     * in the database who is a potential referral source.
     *
     * @return array [list of every professional in the database who is a potential referral source]
     */
    public function GetReferrals()
    {
        $this->requestXml['Procedure'] = 'GetReferrals';    
        $response = $this->processRequsest();        
        return $this->parseResponse($response);       
    }

    /**
     * [GetStaff] This stored procedure provides a .
     *
     * @return  array [list of all employees in the database underlying 
     * record is an object]
     */
    public function GetStaff()
    {
        $this->requestXml['Procedure'] = 'GetStaff';    
        $response = $this->processRequsest();                
        return $this->parseResponse($response);        
    }


    /**
     * 
     */ 
    private function parseResponse($response) 
    { 
        if ($response->ResponseStatus == 'Success') { 
            return $this->parseRecord($response->Records->Record);
        } else { 
            return array(
                'code' => $response->ErrorCode,
                'message' => $response->ErrorMessage,
                );
        }
    }

    /**
     * [parseRecord]
     * @param  [type] $record [description]
     * @return [array] an array of all the records.
     */
    private function parseRecord($record) 
    {
        $array = array();
        foreach ($record as $n => $record) { 
            $array[] = $record;
        }
        return $array;
    }

    /**
     * 
     */ 
    private function processRequsest() 
    { 
        $xml = $this->xmlCreate('GetDataRequest', $this->requestXml);
        $xml = str_replace('<GetDataRequest>', $this->xmlHeader, $xml);
        $resp = $this->curl($this->server, $xml, array('Content-Type' => 'text/xml'), 'POST');
        return simplexml_load_string($resp->response);
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
