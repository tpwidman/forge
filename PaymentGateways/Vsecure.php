<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * 
 * @copyright 2002 Zeekee Interactive
 * 
 * 
 * American Express Test Card: 370000000000002
 * Discover Test Card: 6011000000000012
 * Visa Test Card: 4111111111111111
 * 
 * 
 * 
 * 
 * 
 */
class Vsecure extends \Anvil
{

    private $host;

    private $processingUrl;

    private $request;

    private $gatewayId;

    private $userId;

    private $cart;

    
    /**
     * 
     * @ignore
     */
    public function __construct($gatewayId = '', $userId = '', $host = 'http://dvrotsos2.kattare.com')
    {

        $this->gatewayId = $gatewayId;
        $this->userId = $userId;
        $this->host = $host;
        $this->buildRequest();

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
        $this->request['ProcessPayment']['Amount'] = substr($value, 0, 12);
    }

    /**
     * 
     * 
     */ 
    public function authorizeCancel() { 
        $this->processingUrl = $this->host . '/vsg2/processauthcancel';


    }

    /**
     * 
     * 
     */ 
    public function authorizeCapture($transaction = array()) { 

         $return = array(
            'authorized' => 0,
            'authorization_code' => '',
            'amount' => 0,
            'transaction_id' => '',
            'message' => ''
            );


        $this->processingUrl = $this->host . '/vsg2/processpayment';

        $postVars = array('param' => trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $this->xmlCreate('Request', $this->request))));    

        $request = $this->sendRequest($this->processingUrl, $postVars, array('Content-Type' => 'multipart/form-data'), 'POST');

        $xmlResponse = new \SimpleXMLElement($request->response);

        $return['message'] = '[' . $xmlResponse->ResponseCode . '] [' . substr($transaction->payment['cc_number'], -4) . ']' . $this->responseCodeText($xmlResponse->ResponseCode);        

        if ($xmlResponse->Status == 0) { 
            $return['authorized'] = 1;
            $return['transaction_type'] = 'AUTH_CAPTURE';
            $return['amount'] = (int) substr($xmlResponse->TransactionAmount, 0, -2) . '.' . substr($xmlResponse->TransactionAmount, -2);
            $return['authorization_code'] = (string) $xmlResponse->AuthIdentificationResponse;
            $return['transaction_id'] = (string) $xmlResponse->ReferenceNumber;        
        } else { 
            $return['authorized'] = 0;
            
        }

        return (object) $return;

    }

    /**
     * 
     * 
     */ 
    public function authorizeOnly() { 
        $this->processingUrl = $this->host . '/vsg2/processauth';

        $return = array(
            'authorized' => 0,
            'authorization_code' => '',
            'amount' => 0,
            'transaction_id' => '',
            'message' => ''
            );


        $this->processingUrl = $this->host . '/vsg2/processpayment';

        $postVars = array('param' => trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $this->xmlCreate('Request', $this->request))));    

        $request = $this->sendRequest($this->processingUrl, $postVars, array('Content-Type' => 'multipart/form-data'), 'POST');

        $xmlResponse = new \SimpleXMLElement($request->response);

        $return['message'] = '[' . $xmlResponse->ResponseCode . '] [' . substr($this->request['ProcessPayment']['AccountNumber'], -4) . ']' . $this->responseCodeText($xmlResponse->ResponseCode);        
        if ($xmlResponse->Status == 0) { 
            $return['authorized'] = 1;
            $return['transaction_type'] = 'AUTH_ONLY';
            $return['amount'] = (int) substr($xmlResponse->TransactionAmount, 0, -2) . '.' . substr($xmlResponse->TransactionAmount, -2);
            $return['authorization_code'] = (string) $xmlResponse->AuthIdentificationResponse;
            $return['transaction_id'] = (string) $xmlResponse->ReferenceNumber;        
        } else { 
            $return['authorized'] = 0;
            
        }

        return (object) $return;
    }


    /**
     * 
     * 
     */ 
    public function billing_address($street = '', $zip = '')
    {        
        $this->request['ProcessPayment']['AvsStreet'] = substr($street, 0 ,80);
        $this->request['ProcessPayment']['AvsZip'] = substr($zip, 0 ,10);
    }

    /**
     * 
     * 
     * 
     */ 
    private function buildRequest()
    {

        $request = array(
            'MerchantData' => array(
                'Platform' => 'Tyfoon WMS',
                'UserId' => '',
                'GID' => $this->gatewayId,
                'Tid' => ''),
            'ProcessPayment' => array(
                'Amount' => '',
                'AccountNumber' => '',
                'ExpirationMonth' => '',
                'ExpirationYear' => '',
                'Cvv' => '',
                'CardHolderFirstName' => '',
                'CardHolderLastName' => '',
                'AvsZip' => '',
                'AvsStreet' => '',
                'TypeOfSale' => '',
                'Cf1' => '',
                'Cf2' => '',
                'Cf3' => '',
                'IndustryType1' => '',
                'ApplicationId' => '',
                'Recurring' => array()),
            'Level2PurchaseInfo' => array(),
            'Level3PurchaseInfo' => array()
        );

        $this->request = $request;
    }

    /**
     * 
     * 
     */ 
    public function captureOnly() { 
        $this->processingUrl = $this->host . '/vsg2/processcaptureonly';

    }

    /**
     * 
     * 
     */ 
    public function card_authorized_user($firstname = '', $lastname = '')
    {
        if (empty($lastname)) { 
            list($firstname, $lastname) = preg_split("/\ /", $firstname);
        }
        $this->request['ProcessPayment']['CardHolderFirstName'] = substr($firstname, 0 ,40);
        $this->request['ProcessPayment']['CardHolderLastName'] = substr($lastname, 0 ,40);
    }

    /**
     * 
     * 
     */ 
    public function card_cvv($value = '')
    {
        $value = $this->scrubVar($value, 'WHOLE_NUM');
        $this->request['ProcessPayment']['Cvv'] = substr($value, 0 ,4);
    }

    /**
     * 
     * 
     */ 
    public function card_exp_date($value = '')
    {
        $month = date('m', strtotime($value));
        $year = date('y', strtotime($value));
        $this->request['ProcessPayment']['ExpirationMonth'] = $month;
        $this->request['ProcessPayment']['ExpirationYear'] = $year;
    }

    /**
     * 
     * 
     */ 
    public function card_number($value = '')
    {
        $value = $this->scrubVar($value, 'WHOLE_NUM');
        $this->request['ProcessPayment']['AccountNumber'] = substr($value, 0 ,19);
    }


    /**
     * 
     * 
     */ 
    public function createToken() { 
        $this->processingUrl = $this->host . '/vsg2/createtoken';

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
     */ 
    public function deleteToken() { 
        $this->processingUrl = $this->host . '/vsg2/deletetoken';
    }

    /**
     * 
     * 
     */ 
    public function queryToken() { 
        $this->processingUrl = $this->host . '/vsg2/querytoken';
    }

    /**
     * 
     * 
     */
    private function responseCodeText($code = '') { 

        $code = 'x' . $code;

        $codes = array(
            'x00' => 'Approved or completed successfully',
            'x01' => 'Refer to card issuer',
            'x02' => 'Refer to card issuer\'s special conditions',
            'x03' => 'Invalid merchant',
            'x04' => 'Pick up',
            'x05' => 'Do not honor',
            'x06' => 'Error',
            'x08' => 'Honor with identification',
            'x10' => 'Approved, partial amount approved',
            'x11' => 'Approved (VIP)',
            'x12' => 'Invalid transaction',
            'x13' => 'Invalid amount',
            'x14' => 'Invalid card number (no such number)',
            'x15' => 'No such issuer',
            'x19' => 'Try again',
            'x21' => 'Reversal error',
            'x22' => 'Reversal error',
            'x25' => 'Unable to locate record on file',
            'x26' => 'Referred ID not in DL database',
            'x27' => 'Referred – Call Center',
            'x28' => 'Referred skip trace info needed',
            'x29' => 'Hard negative info on file',
            'x30' => 'Format error (may also be a reversal)',
            'x33' => 'Expired card; pick up',
            'x34' => 'Suspected fraud; pick up',
            'x36' => 'Restricted card; pick up',
            'x40' => 'Total unavailable',
            'x41' => 'Lost card; pick up',
            'x43' => 'Stolen card; pick up',
            'x51' => 'Insufficient funds',
            'x52' => 'No checking account',
            'x53' => 'No savings account',
            'x54' => 'Expired card',
            'x55' => 'Incorrect PIN',
            'x57' => 'Transaction not permitted by card holder',
            'x61' => 'Exceeds withdrawal amount limit',
            'x62' => 'Restricted card',
            'x65' => 'Exceeds withdrawal frequency limit',
            'x66' => 'Card acceptor; call acquirer security',
            'x67' => 'Hard capture; pick up',
            'x75' => 'Allowable number of PIN tries exceeded',
            'x76' => 'Key synchronization error',
            'x7U' => 'Amount too large',
            'x7V' => 'Duplicate return',
            'x7W' => 'Unsuccessful',
            'x7X' => 'Duplicate reversal',
            'x7Y' => 'Subsystem unavailable',
            'x7Z' => 'Duplicate completion',
            'x82' => 'Count exceeds limit',
            'x85' => 'No reason to decline.',
            'x86' => 'Invalid card security code',
            'x90' => 'System error',
            'x91' => 'Issuer or switch is inoperative (time-out)',
            'x92' => 'Financial institution or intermediate network unknown for routing',
            'x93' => 'Transaction cannot be completed; violation of law',
            'x94' => 'Duplicate transaction',
            'x96' => 'System malfunction',
            'xR0' => 'Not approved.',
            'xR1' => 'Not approved.');

        if (array_key_exists($code, $codes)) { 
            return $codes[$code];
        } else { 
            return 'Response code not found.';
        }

    } 

    /**
     * 
     * 
     * 
     */ 
    private static function sendRequest($url, $data, $headers = array(), $method = 'GET', $debug = 0)
    {        
        $output = array();

        $curlHeaders = array();

        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                $curlHeaders[] = "$key: $value";
            }                    
        }

        if (is_array($headers)) { 
            if (array_key_exists('Content-Type', $headers) && strpos($headers['Content-Type'], 'xml') > 0 ) {
                $query = $data;
            } else { 
                if (is_object($data) || is_array($data)) {            
                    if (array_key_exists('Content-Type', $headers) && $headers['Content-Type'] =='multipart/form-data') {
                        $query = $data;
                    } else { 
                        $query = trim(http_build_query($data, '', '&'));    
                    }
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
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) ? curl_setopt($ch, CURLOPT_CAINFO, 'C:\WINNT\curl-ca-bundle.crt') : false;        
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
     */ 
    public function updateExpDate() { 
        $this->processingUrl = $this->host . '/vsg2/updateexpiration';
    }

    /**
     * 
     * 
     */ 
    public function updateToken() { 
        $this->processingUrl = $this->host . '/vsg2/updatetoken';
    }
    
    /**
     * 
     * 
     */ 
    public function refund() { 
        $this->processingUrl = $this->host . '/vsg2/processrefund';
    }

    /**
     * 
     * 
     */ 
    public function void() { 
        $this->processingUrl = $this->host . '/vsg2/processvoid';
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


