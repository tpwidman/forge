<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * 
 * @copyright 2002 Zeekee Interactive
 * 
 * 
 * Your demo account is created and will expire on 10/28/2016. If you require more time please email us requesting an extension a few days before your account expires.
 
Use the following link and login credentials to access it.
 
     https://demo.myvirtualmerchant.com/VirtualMerchantDemo/login.do
 
     Account ID: 007576
     User ID: 007576
     Password: Abcd.1234
 
When submitting transactions via your shopping cart be sure to send them to the demo API URL and use the credentials listed below.
     https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do
 
     ssl_merchant_id: 007576
     ssl_user_id: webpage
     ssl_pin: 69DT15
 
You should be able to find all the information you need in the Developer Guide and the User Guide. These guides are available on the Converge (previously Virtual Merchant) website and direct links are below for your convenience.
 
     https://demo.myvirtualmerchant.com/VirtualMerchantDemo/download/developerGuide.pdf
     https://demo.myvirtualmerchant.com/VirtualMerchantDemo/download/userGuide.pdf     
 *
 *
 *
 *426397 000000 5262  00  Successful
4000120000001154    101 Declined
4000130000001724    102 Referral B
4000160000004147    103 Referral A
4009830000001985    205 Comms Error
MasterCard  5425230000004415    00  Successful
5114610000004778    101 Declined
5114630000009791    102 Referral B
5121220000006921    103 Referral A
5135020000005871    205 Comms Error
American Express    374101000000608 00  Successful
375425000003    101 Declined
375425000000907 102 Referral B
343452000000306 103 Referral A
 *
 * 
 * 
 * 
 * 
 * 
 * 
 */
class Elavon extends \Anvil
{


    private $urlProcess;
    private $urlBatch;
    private $urlXML;
    private $urlAccount;
    private $merchantId;                 // Converge ID as provided by Elavon
    private $userId;                     // Converge user ID as configured on Converge (case sensitive)
    private $pin;                        // provided by Converge
    private $request;
    private $cart;
    private $debug = 0;

    /**
     * 
     */
    public function __construct($merchantId, $userId, $pin, $mode = 'production')
    {

        $this->merchantId = $merchantId;
        $this->userId = $userId;
        $this->pin = $pin;
        if (strtoupper(trim($mode)) == 'TEST') { 
            $this->setServer('demo');
        } else { 
            $this->setServer('production');
        }
        $this->buildRequest();
        $this->setRequest('result_format', 'ASCII'); // HTML other value and is stupid.
        //$this->setRequest('transaction_currency', 'USD');

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
    public function amount($value = '')
    {
        $value = $this->scrubVar($value, 'MONEY');
        $this->request['ssl_amount'] = $value;
    }

    public function authorizeCapture($transaction)
    {
        
        $return = array(
            'authorized' => 0,
            'authorization_code' => '',
            'transaction_id' => '',
            'message' => ''
            );
        $this->setRequest('transaction_type', 'ccsale');
        $this->setRequest('show_form', 'false');        
        $this->bindTransaction($transaction);
        
        $http = $this->curl($this->urlProcess, $this->request, array(), 'POST');

        empty($http->response) ? $response = array() : $response = $this->parseResponse($http->response);
        

        if (!empty($response['id'])) {
            $return['authorized'] = 1;
            $return['amount'] = $this->scrubVar($response['ssl_amount']);    
            $return['authorization_code'] = $response['id'];
            $return['transaction_type'] = 'AUTH_CAPTURE';
            $return['transaction_id'] = $response['id'];
            $return['message'] = $this->request['ssl_description'];
        } else { 
            if (array_key_exists('errorCode', $response)) { 
                $return['message'] = $response['errorName'] . ':' . $response['errorMessage'];
            } elseif ($response['ssl_result'] == 1) {  
                // another idiotic choice - 1 equals error and 0 success
                $return['message'] = $response['ssl_result_message'];
            } elseif ($response['ssl_result'] === 0) {  
                // another idiotic choice - 1 equals error and 0 success
                $return['authorized'] = 1;
                $return['amount'] = $this->scrubVar($response['ssl_amount']);    
                $return['authorization_code'] = $response['ssl_approval_code'];
                $return['transaction_type'] = 'AUTH_CAPTURE';
                $return['transaction_id'] = $response['ssl_txn_id '];
                $return['message'] = $this->request['ssl_description'];
            } else { 
                $return['message'] = 'Unknown process error.';
            }
        }
        
        return (object) $return;

    }


    /**
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
     */ 
    private function bindTransaction($transaction)
    {
        $payment = (object) $transaction['payment'];
        $this->card_number($payment->cc_number);
        $this->card_address($payment->bill_address_1, $payment->bill_address_postal_code);
        $this->card_exp_date($payment->cc_exp_month, $payment->cc_exp_year);
        $this->card_cvv($payment->cc_cvv2);
        $this->setRequest('amount', $payment->amount);
        $this->setRequest('invoice_number', $payment->session);
        $this->setRequest('description', $transaction['journal'] . ' : ' . $payment->amount);        
        
    }

    /**
     * 
     * 
     * 
     */ 
    private function buildRequest()
    {

        $request = array(
            'ssl_transaction_type' => '',    // Transaction type
            'ssl_merchant_id' => $this->merchantId,         // Converge ID as provided by Elavon
            'ssl_user_id' => $this->userId,             // Converge user ID as configured on Converge (case sensitive)
            'ssl_pin' => $this->pin                 // Converge PIN as generated within Converge (case sensitive)            
        );

        $this->request = $request;
    }


    /**
     * 
     * 
     */ 
    public function cancelTransaction()
    {
        
    }

    /**
     * 
     * 
     */ 
    public function card_address($street = '', $zip = '')
    {        
        $this->request['ssl_avs_address'] = $street;
        $this->request['ssl_avs_zip'] = $zip;
    }

    /**
     * 
     * 
     */ 
    public function card_cvv($value = '')
    {
        $value = $this->scrubVar($value, 'WHOLE_NUM');
        $this->request['ssl_cvv2cvc2_indicator'] = 1;        
        $this->request['ssl_cvv2cvc2'] = $value;
    }

    /**
     * This is another company that did not learn a single 
     * thing from the Y2k issue and should be taken
     * out and shot.
     * 
     */ 
    public function card_exp_date($month, $year)
    {

        $month = date('m', strtotime("$month/1/1970"));

        $year = date('y', strtotime("1/1/$year"));

        $this->request['ssl_exp_date'] = $month .  $year;        
    }

    /**
     * 
     * 
     */ 
    public function card_number($value = '')
    {
        $value = $this->scrubVar($value, 'WHOLE_NUM');
        $this->request['ssl_card_number'] = substr($value, 0 ,19);
    }

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

    /**
     * 
     * 
     * 
     */ 
    public function dump($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';        
    }

    /**
     * 
     * 
     * 
     */ 
    private function parseResponse($string = '')
    {
        $return = array();
        $values = preg_split("/\n/", $string);
        foreach ($values as $n => $value) { 
            list($key, $val) = preg_split("/\=/", $value);
            $return[$key] = $val;
        }
        return $return;
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
     * 
     * 
     * 
     * 
     */ 
    public function setRequest($field, $value)
    {
        $fields = array(
            'invoice_number' => array('maxlength' => 25)
            );
        if (array_key_exists($field, $fields)) { 
            $this->request['ssl_' . strtolower(str_replace(' ','_',trim($field)))] = substr(trim($value), 0, $fields[$field]['maxlength']);    
        } else { 
            $this->request['ssl_' . strtolower(str_replace(' ','_',trim($field)))] = trim($value);
        }
        return $this; // for chaining.
    }

    /**
     * 
     * 
     */ 
    public function voidTransaction()
    {
        
    }

    /**
     * 
     * 
     * 
     */ 
    public function setServer($mode = 'production') 
    { 
        if (strtolower(trim($mode)) == 'demo') { 
            $this->urlProcess = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do';
            $this->urlBatch = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/processBatch.do';
            $this->urlXML = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do';
            $this->urlAccount = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/accountxml.do';
        } else { 
            $this->urlProcess = 'https://www.myvirtualmerchant.com/VirtualMerchantDemo/process.do';
            $this->urlBatch = 'https://www.myvirtualmerchant.com/VirtualMerchantDemo/processBatch.do';
            $this->urlXML = 'https://www.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do';
            $this->urlAccount = 'https://www.myvirtualmerchant.com/VirtualMerchantDemo/accountxml.do';
        }
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
