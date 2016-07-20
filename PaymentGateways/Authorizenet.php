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

    // private $server = 'https://secure2.authorize.net/gateway/transact.dll';

    private $server = 'https://api.authorize.net/xml/v1/request.api';

    private $debug = 1;

    // $cLogin = '4uN77tP7'; // foonster credentials
    // $cPassword ='7p2748HH3Ad79SjB'; // foonster credentials

    public $_response = array(); 

    private $xmlHeaders = array(
            'Content-Type' => 'application/xml; utf-8', 
            'Accept' => 'application/xml');

    private $subscriptionXml = array(
        'merchantAuthentication' => 
            array(
                'name' => '', 
                'transactionKey' => ''),       
        'refId' => '',
        'subscription' => 
            array(
            'name' => '', 
            'paymentSchedule' => array(
                'interval' => array(
                    'length' => '365',
                    'unit' => 'days'
                    ),
                'startDate' => '',
                'totalOccurrences' => '9999',
                'trialOccurrences' => '0',
                ),
            'amount' => '0', 
            'trialAmount' => '0', 
            'payment' => array(
                /*'creditCard' => array(
                    'cardNumber' => '',
                    'expirationDate' => '',
                    'cardCode' => ''
                    ),
                'bankAccount' => array(
                    'accountType' => '',
                    'routingNumber' => '',
                    'accountNumber' => '',
                    'nameonAccount' => '',
                    'echeckType' => '',
                    'bankName' => ''
                    )*/
                ),
            'order' => array(
                'invoiceNumber' => '',
                'description' => ''
                ),
            'customer' => array(
                'id' => '',
                'email' => '',
                'phoneNumber' => '',
                'faxNumber' => ''
                ), 
            'billTo' => array(
                'firstName' => '',
                'lastName' => '',
                'company' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country' => ''
                ), 
            'shipTo' => array(
                'firstName' => '',
                'lastName' => '',
                'company' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country' => ''
                )
            )
        );


    /**
     * @ignore
     */
    public function __construct($id = null, $key = null)
    {

        $this->subscriptionXml['merchantAuthentication']['name'] = trim($id);
        $this->subscriptionXml['merchantAuthentication']['transactionKey'] = trim($key);

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
     * 
     * 
     */
    public function ARBCreateSubscription()
    {

        
        $xml = $this->getXml();

        $xml = str_replace('<ARBCreateSubscriptionRequest>','<ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);

        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);

        $this->_response = simplexml_load_string($return);

    }

    /**
     * 
     * 
     * 
     */ 
    public function ARBGetSubscriptionStatus($id)
    {

        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),       
        'refId' => '',
        'subscriptionId' => $id);

        $xml = $this->xmlCreate('ARBGetSubscriptionStatusRequest', $array);        

        $xml = str_replace('<ARBGetSubscriptionStatusRequest>','<ARBGetSubscriptionStatusRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);

        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);

        //echo htmlentities($return);

        //return simplexml_load_string($return);

    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBGetSubscription($args = array())
    {
        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),       
        'refId' => $args['refId'],
        'subscriptionId' => $args['subscriptionId']);

        $xml = $this->xmlCreate('ARBGetSubscriptionRequest', $array);        

        $xml = str_replace('<ARBGetSubscriptionRequest>','<ARBGetSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);

        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);

        return simplexml_load_string($return);

    }


    /**
     * 
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetAddress($type = 'BILLTO', $address) { 

        is_object($address) ? $address = (array) $address : false;

        (strtoupper($type) == 'SHIPTO') ? $which = 'shipTo' : $which = 'billTo';

        $this->subscriptionXml['subscription'][$which]['firstName'] = '';
        $this->subscriptionXml['subscription'][$which]['lastName'] = '';
        $this->subscriptionXml['subscription'][$which]['company'] = '';
        $this->subscriptionXml['subscription'][$which]['address'] = '';
        $this->subscriptionXml['subscription'][$which]['city'] = '';
        $this->subscriptionXml['subscription'][$which]['state'] = '';
        $this->subscriptionXml['subscription'][$which]['zip'] = '';
        $this->subscriptionXml['subscription'][$which]['country'] = '';

        !empty($address['firstName']) ? $this->subscriptionXml['subscription'][$which]['firstName'] = $address['firstName'] : false;
        !empty($address['lastName']) ? $this->subscriptionXml['subscription'][$which]['lastName'] = $address['lastName'] : false;
        !empty($address['company']) ? $this->subscriptionXml['subscription'][$which]['company'] = $address['company'] : false;
        !empty($address['address']) ? $this->subscriptionXml['subscription'][$which]['address'] = $address['address'] : false;
        !empty($address['city']) ? $this->subscriptionXml['subscription'][$which]['city'] = $address['city'] : false;
        !empty($address['state']) ? $this->subscriptionXml['subscription'][$which]['state'] = $address['state'] : false;
        !empty($address['zip']) ? $this->subscriptionXml['subscription'][$which]['zip'] = $address['zip'] : false;
        !empty($address['country']) ? $this->subscriptionXml['subscription'][$which]['country'] = $address['country'] : false;

    } 

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetCheckInfo($accountType = '', $routingNumber = '', $accountNumber = '', $nameonAccount = '', $echeckType = '', $bankName = '') { 
        $this->subscriptionXml['subscription']['payment']['bankAccount'] = array();
        $this->subscriptionXml['subscription']['payment']['bankAccount']['accountType'] = $accountType;
        $this->subscriptionXml['subscription']['payment']['bankAccount']['routingNumber'] = $routingNumber;
        $this->subscriptionXml['subscription']['payment']['bankAccount']['accountNumber'] = $accountNumber;
        $this->subscriptionXml['subscription']['payment']['bankAccount']['nameonAccount'] = $nameonAccount;
        $this->subscriptionXml['subscription']['payment']['bankAccount']['echeckType'] = $echeckType;
        $this->subscriptionXml['subscription']['payment']['bankAccount']['bankName'] = $bankName;

    }   

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetCreditCard($cardNumber, $expirationDate, $code) 
    { 

        list($month, $year) = preg_split("/[\/|\-|\.]/", $expirationDate);
        $this->subscriptionXml['subscription']['payment']['creditCard'] = array();
        $this->subscriptionXml['subscription']['payment']['creditCard']['cardNumber'] = $cardNumber;
        $this->subscriptionXml['subscription']['payment']['creditCard']['expirationDate'] = $year . '-' . $month;
        $this->subscriptionXml['subscription']['payment']['creditCard']['cardCode'] = substr($code, 0, 4);
            
    }

    
    /**
     * 
     * 
     * 
     */ 
    public function ARBSetMonthlyTransaction($amount = 0, $startDate, $totalOccurrences = 9999)
    {

        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['length'] = 1;
        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['unit'] = 'months';
        $this->subscriptionXml['subscription']['paymentSchedule']['startDate'] = date('Y-m-d', strtotime($startDate));
        $this->subscriptionXml['subscription']['paymentSchedule']['totalOccurrences'] = $totalOccurrences;
        $this->subscriptionXml['subscription']['amount'] = $amount;

    }   

    /**
     * 
     * 
     * 
     */ 
    public function ARBSetQuarterlyTransaction($amount = 0, $startDate, $totalOccurrences = 9999)
    {

        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['length'] = 90;
        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['unit'] = 'days';
        $this->subscriptionXml['subscription']['paymentSchedule']['startDate'] = date('Y-m-d', strtotime($startDate));
        $this->subscriptionXml['subscription']['paymentSchedule']['totalOccurrences'] = $totalOccurrences;
        $this->subscriptionXml['subscription']['amount'] = $amount;

    }

    /**
     * 
     * 
     * 
     */ 
    public function ARBSetWeeklyTransaction($amount = 0, $startDate, $totalOccurrences = 9999)
    {

        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['length'] = 7;
        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['unit'] = 'days';
        $this->subscriptionXml['subscription']['paymentSchedule']['startDate'] = date('Y-m-d', strtotime($startDate));
        $this->subscriptionXml['subscription']['paymentSchedule']['totalOccurrences'] = $totalOccurrences;        
        $this->subscriptionXml['subscription']['amount'] = $amount;

    }   

    /**
     * 
     * 
     * 
     */ 
    public function ARBSetYearlyTransaction($amount = 0, $startDate, $totalOccurrences = 9999)
    {

        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['length'] = 365;
        $this->subscriptionXml['subscription']['paymentSchedule']['interval']['unit'] = 'days';
        $this->subscriptionXml['subscription']['paymentSchedule']['startDate'] = date('Y-m-d', strtotime($startDate));
        $this->subscriptionXml['subscription']['paymentSchedule']['totalOccurrences'] = $totalOccurrences;
        $this->subscriptionXml['subscription']['amount'] = $amount;

    }   

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetCustomer($id = '', $email = '', $phone = '', $fax = '') { 
        $this->subscriptionXml['subscription']['customer']['id'] = $id;
        $this->subscriptionXml['subscription']['customer']['email'] = $email;
        $this->subscriptionXml['subscription']['customer']['phoneNumber'] = $phone;
        $this->subscriptionXml['subscription']['customer']['faxNumber'] = $fax;
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetOrder($invoice = '', $description = '') { 
        $this->subscriptionXml['subscription']['order']['invoiceNumber'] = substr($invoice, 0, 20);
        $this->subscriptionXml['subscription']['order']['description'] = substr($description, 0, 254);        
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetTrial($amount, $count)
    {
        $this->subscriptionXml['subscription']['trialAmount'] = $amount;
        $this->subscriptionXml['subscription']['paymentSchedule']['trialOccurrences'] = $count;

    }    

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ARBSetValue($variable, $value) { 

        (strtolower(trim($variable)) == 'refid') ? $this->subscriptionXml['refId'] = substr($value, 0, 20) : false;

        (strtolower(trim($variable)) == 'description') ? $this->subscriptionXml['subscription']['name'] = substr($value, 0, 50) : false;

    }

    /**
     * 
     * 
     * 
     */ 
    public function ARBSubscriptionId()
    {

        return $this->_response->subscriptionId;

    }


    /**
     * 
     * 
     * 
     * 
     */
    public function authorizeCapture()
    {

    }

    /**
     * 
     * 
     * 
     * 
     */
    public function authorizeOnly()
    {

    }

    /**
     * 
     * 
     * 
     * 
     */
    public function captureAuthorization()
    {

    }

    /**
     * 
     * 
     * 
     * 
     */
    public function captureOnly()
    {

    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function credit()
    {

    }

    /**
     * 
     */
    private function curlPOST($server, $data, $headers = array())
    {

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

    private function dumpVar($var) 
    { 
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

    public function dumpResponse()
    {

        $this->dumpVar($this->_response);

    }

    /**
     * 
     * 
     * 
     */ 
    public function getResponseCode()
    {

        return $this->_response->messages->resultCode;

    }



    /**
     * This request generates a list of subscriptions. The table below describes the input
     * Element Description
     * searchType Values include:
        * cardExpiringThisMonth
        *  subscriptionActive
        * subscriptionInactive
        * subscriptionExpiringThisMonth
    sorting Contains sorting information.
        * orderBy Order of transactions in response:
        * id
        * name
        * status
        * createTimeStampUTC
        * lastName
        * firstName
        * accountNumber (ordered by last 4 digits only)
        *  amount
        * pastOccurences
        * orderDescending Value: true, false, 1 or 0.
    Format: Boolean
    paging Contains information about list pages.
     limit Value: 1-1000
    Notes: The number of subscriptions per page.
     offset Value: 1-10000
    Notes: The number of pages
     * 
     */ 
    public function ARBGetSubscriptionList($args = array())
    {

        // set search type
        $searchType = 'subscriptionActive';

        $searchTypes = array('cardExpiringThisMonth', 
            'subscriptionActive', 
            'subscriptionInactive', 
            'subscriptionExpiringThisMonth');
        
        in_array($args['searchType'], $searchTypes) ? $searchType = $args['searchType'] : false;

        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),       
        'searchType' => $searchType,
        'sorting' => 
            array(
                'orderBy' => 'id', 
                'orderDescending' => 'false'),       
        'paging' => 
            array(
                'limit' => '1000', 
                'offset' => '1'),       
        );
        $xml = $this->xmlCreate('ARBGetSubscriptionListRequest', $array);        
        $xml = str_replace('<ARBGetSubscriptionListRequest>','<ARBGetSubscriptionListRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);
        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);
        
        $xml = simplexml_load_string($return);

        return $xml->subscriptionDetails->subscriptionDetail;

    }

    /**
     * 
     * 
     * 
     */ 
    public function getError()
    {

        return $this->_response->messages->message->code . ' : ' . $this->_response->messages->message->text;

    }

    /**
     * 
     * 
     * 
     */ 
    public function getXml($array = true)
    {

        if ($array) { 
            return $this->xmlCreate('ARBCreateSubscriptionRequest', $this->subscriptionXml);
        } else { 
            return $this->subscriptionXml;
        }

        
    }


    public function getSettledBatchListRequest($start, $end)
    {


        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),       
        'includeStatistics' => 'true',
        'firstSettlementDate' => date('Y-m-d\T00:00:00', strtotime($start)),
        'lastSettlementDate' => date('Y-m-d\T23:59:59', strtotime($end)));
        $xml = $this->xmlCreate('getSettledBatchListRequest', $array);        
        $xml = str_replace('<getSettledBatchListRequest>','<getSettledBatchListRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);
        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);
        return simplexml_load_string($return);
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function getTransactionListRequest($id)
    {


        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),               
        'batchId' => $id);
        $xml = $this->xmlCreate('getTransactionListRequest', $array);        
        $xml = str_replace('<getTransactionListRequest>','<getTransactionListRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);
        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);
        return simplexml_load_string($return);
    }


    /**
     * 
     * 
     * 
     */ 
    public function getTransactionDetailsRequest($id)
    {


        $array = array(
        'merchantAuthentication' => 
            array(
                'name' => $this->subscriptionXml['merchantAuthentication']['name'], 
                'transactionKey' => $this->subscriptionXml['merchantAuthentication']['transactionKey']),               
        'transId' => $id);
        $xml = $this->xmlCreate('getTransactionDetailsRequest', $array);        
        $xml = str_replace('<getTransactionDetailsRequest>','<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">', $xml);
        $return = $this->curlPost($this->server, $xml, $this->xmlHeaders);
        return simplexml_load_string($return);
    }




    
    /**
     * 
     * 
     * 
     * 
     * 
     */ 
    public function setServer($server = 'PRODUCTION')
    {    
        if ($server == 'TEST') { 
            $this->server = 'https://test.authorize.net/gateway/transact.dll';
        } elseif ($server == 'SANDBOX') { 
            $this->server = 'https://apitest.authorize.net/xml/v1/request.api';
        } else { 
            $this->server = 'https://api2.authorize.net/xml/v1/request.api';
        }
    }


    /**
     * 
     * 
     * 
     * 
     */ 
    public function unlinkedCredit()
    {

    }


    /**
     * 
     * 
     * 
     * 
     */ 
    public function void()
    {

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
