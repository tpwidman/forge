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
class Authorizenet extends Anvil
{

    private $server = 'https://secure.authorize.net/gateway/transact.dll';

    private $debug = 0;

    public $_response = array(); 

    private $login = '95LgR9sV';

    private $password = '69E7C2kh9LcycB4R';

    private $mode;

    private $cart;

    private $trans_code;

    private $auth_code; 

    private $response;

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

        if (!empty($id)) { 
            //$this->subscriptionXml['merchantAuthentication']['name'] = trim($id);
            $this->login = trim($id);
        } else { 
            $this->subscriptionXml['merchantAuthentication']['name'] = $this->id;
        }

        if (!empty($key)) { 
            //$this->subscriptionXml['merchantAuthentication']['transactionKey'] = trim($key);
            $this->password = trim($key);
        } else { 
            $this->subscriptionXml['merchantAuthentication']['transactionKey'] = $this->password;
        }
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
     * This function is used to run an AuthorizationCapture
     * 
     * @param  array $transaction [contains all the information to process this transaction.]
     * @return array [returns the results of the transaction.]
     */
    public function authorizeCapture($transaction)
    {
        
        $return = array(
            'authorized' => 0,
            'authorization_code' => '',
            'transaction_id' => '',
            'message' => ''
            );

        $products = array();    

        !is_object($transaction) ? $transaction = (object) $transaction : false;

        !empty($transaction->transaction_mode) ? $this->setServer($transaction->transaction_mode) : false;        
        !empty($transaction->transaction_id) ? $id = $transaction->transaction_id : $id = $this->login;
        !empty($transaction->transaction_pw) ? $pw = $transaction->transaction_pw : $pw = $this->password;

        $this->payment->cc_number = $this->scrubVar($this->payment->cc_number);

        if($transacton['exception']) { 
            
            $aEntry = array(
                'id' => $transaction->item_id,
                'name' => $transaction->item_name,
                'desc' => $transaction->item_desc,
                'qty' => $transaction->item_qty,
                'price' => $transaction->amount,
                'taxable' => $transaction->item_taxable
                );                
            $products[] = "x_line_item=".urlencode( implode( '<|>', $aEntry)) . '&'; 

            $this->cart->total = $transaction->amount;

        } else {

            if (is_array($this->cart->items)) {
                $amount = 0;
                $nLoop = 0;
                foreach ($this->cart->items AS $cKey => $aItem ) {       
                    $aItem = (array) $aItem;                 
                    $itemPrice = ($aItem['price'] + $aItem['setupfee'] + $aItem['handle_charge'] + $aItem['gift_charge']);
                    if ( $nLoop < 30 ) {        
                        $nLoop++;
                        $aEntry = array(
                            'id' => 'item'.$nLoop,
                            'name' => substr(trim($aItem['sku']), 0, 28 ),
                            'desc' => substr( $aItem['title'].' '.strip_tags( $aItem['attribute_text'] ), 0, 254 ),
                            'qty' => $aItem['qty'],
                            'price' => $itemPrice,
                            'taxable' => $aItem['taxable']
                        );                
                        $products[] = "x_line_item=".urlencode( implode( '<|>', $aEntry)) . '&';      

                    }
                    $amount += ($aItem['qty'] * $itemPrice);            
                }  

                $this->cart->amount = $amount;

                if ($this->cart->promotional_discount > 0) {
                    $products = array();
                
                    $aEntry = array(
                        'id' => 'item1',
                        'name' => 'DISCOUNT',
                        'desc' => 'Complex/Discounted transaction - ' . number_format($this->cart->promotional_discount,2),
                        'qty' => 1,
                        'price' => $this->cart->amount,
                        'taxable' => 1
                        );                
                    $products[] = "x_line_item=".urlencode( implode( '<|>', $aEntry)) . '&'; 
                    
                }            

                $this->cart->total = (
                ($this->cart->amount + $this->cart->taxes + $this->cart->handle_charge + $this->cart->ship_charge) - 
                $this->cart->promotional_discount);

            } else { 
            
                $aEntry = array(
                    'id' => 'item1',
                    'name' => 'SIMPLE',
                    'desc' => 'NO ITEM INFORMATION PASSED',
                    'qty' => 1,
                    'price' => $this->cart->amount,
                    'taxable' => 1
                    );                
                $products[] = "x_line_item=".urlencode( implode( '<|>', $aEntry)) . '&'; 

                $this->cart->total = (
                    ($this->cart->amount + $this->cart->taxes + $this->cart->handle_charge + $this->cart->ship_charge) - 
                    $this->cart->promotional_amount);
            } 

        }    

        empty($this->cart->amount) ? $this->cart->amount = '0.00' : $this->cart->amount = $this->scrubVar($this->cart->amount, 'MONEY');
        empty($this->cart->taxes) ? $this->cart->taxes = '0.00' : $this->cart->taxes = $this->scrubVar($this->cart->taxes, 'MONEY');
        empty($this->cart->handle_charge) ? $this->cart->handle_charge = '0.00' : $this->cart->handle_charge = $this->scrubVar($this->cart->handle_charge, 'MONEY');
        empty($this->cart->ship_charge) ? $this->cart->ship_charge = '0.00' : $this->cart->ship_charge = $this->scrubVar($this->cart->ship_charge, 'MONEY');
        // empty($this->cart->gift_charge) ? $this->cart->gift_charge = '0.00' : $this->cart->gift_charge = $this->scrubVar($this->cart->gift_charge, 'MONEY');

        $aAuthorizeNet  = array (
            "x_login"              => $id,
            "x_version"            => "3.1",
            "x_test_request"       => $cType,
            "x_delim_char"         => "|",
            "x_delim_data"         => "TRUE",
            "x_url"                => "FALSE",
            "x_type"               => "AUTH_CAPTURE",
            "x_method"             => "CC",
            "x_tran_key"           => $pw, // 
            "x_invoice_num"        => $this->cart->session,
            "x_relay_response"     => "FALSE",
            "x_card_num"           => $transaction->payment['cc_number'],
            "x_card_code"          => $transaction->payment['cc_cvv2'],
            "x_exp_date"           => $transaction->payment['cc_exp_month'] . $transaction->payment['cc_exp_year'],
            "x_description"        => $transaction->journal,
            "x_tax"                => "Taxes|" . $this->scrubVar($this->cart->taxes),
            "x_freight"            => "Freight<|>" . strip_tags( stripslashes($this->cart->ship_carrier) . '/handling' ) . "<|>" . ($this->cart->ship_charge + $this->cart->handle_charge),
            "x_amount"             => $this->scrubVar($this->cart->total),
            "x_company"            => $this->cart->bill_company,
            "x_first_name"         => $this->cart->bill_name_first,
            "x_last_name"          => $this->cart->bill_name_last,
            "x_address"            => $this->cart->bill_address_1,
            "x_city"               => $this->cart->bill_address_city,
            "x_state"              => $this->cart->bill_address_state, 
            "x_zip"                => $this->cart->bill_address_postal_code,
            "x_country"            => $this->cart->bill_country_iso_3166,
            "x_phone"              => $this->cart->bill_address_phone,
            "x_fax"                => $this->cart->bill_address_altphone, 
            "x_email"              => $this->cart->bill_email,
            "x_ship_to_first_name" => $this->cart->ship_name_first,
            "x_ship_to_last_name"  => $this->cart->ship_name_last,
            "x_ship_to_address"    => $this->cart->ship_address_1,
            "x_ship_to_city"       => $this->cart->ship_address_city,
            "x_ship_to_state"      => $this->cart->ship_address_state,
            "x_ship_to_zip"        => $this->cart->ship_address_postal_code,
            "x_ship_to_country"    => $this->cart->ship_country_iso_3166,
            "x_customer_ip"        => $_SERVER['REMOTE_ADDR']
        );    

        $query = http_build_query($aAuthorizeNet, '', '&') . '&' . implode($products);            

        $http = $this->curl($this->server, $query, array(), 'POST');

        empty($http->response) ? $response = array() : $response = explode('|', trim($http->response));

        ($response[0] == 4) ? $response[0] = 1 : false;    

        $return['amount'] = $this->scrubVar($this->cart->total);
        $return['authorized'] = $response[0];
        $return['authorization_code'] = $response[4];
        $return['transaction_type'] = 'AUTH_CAPTURE';
        $return['transaction_id'] = $response[6];
        $return['message'] = '[' . strtoupper(substr($id, -4)) . ':' . $response[2] . '] [' . substr($transaction->payment['cc_number'], -4) . ']' . $response[3];
        ($this->debug) ? $return['query'] = $query : false;
        ($this->debug) ? $return['response'] = json_encode($http->response) : false;

        return (object) $return;
        
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
    public function cart()
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

    /*
    /**
     * 
     
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
    */
   
    /**
     * 
     * 
     * 
     */ 
    public function debug($mode)
    {
        if (strtolower($mode) == 'on' || strtolower($mode) == 'true' || $mode === true || $mode == 1) { 
            $this->debug = 1;
        } else { 
            $this->debug = 0;
        }
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
     */
    public function setCart($cart)
    {
        $this->cart = clone $cart;
    } 
    
    /**
     * private $server = 'https://secure2.authorize.net/gateway/transact.dll
     * https://test.authorize.net/gateway/transact.dll';
     */ 
    public function setServer($server = 'PRODUCTION')
    {            
        if ($server == 'API') { 
            $this->server = 'https://api2.authorize.net/xml/v1/request.api';
        } else if ($server == 'SANDBOX') { 
            $this->login = '95LgR9sV';
            $this->password = '69E7C2kh9LcycB4R';
            $this->server = 'https://apitest.authorize.net/xml/v1/request.api';
        } else if ($server == 'TEST') { 
            $this->login = '95LgR9sV';
            $this->password = '69E7C2kh9LcycB4R';
            $this->server = 'https://test.authorize.net/gateway/transact.dll';
        } else { 
            $this->server = 'https://secure.authorize.net/gateway/transact.dll';
        }

    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function ping()
    {
        echo date('c');
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
