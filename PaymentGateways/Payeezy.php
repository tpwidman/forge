<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2016 Zeekee Interactive
 */
class Payeezy
{

    private $server = 'https://api.payeezy.com/v1/transactions';

    public $currency_code = 'USD';

    private $debug = 0;

    private $timestamp = 0;

    private $_response = array(); 

    private $hashAlgorithm = "sha256";

    private $merchantToken = "";

    private $apiKey = '';

    private $apiSecret = '';

    private $_vars = array();

    private $transaction_type = '';

    private $payload = array();

    private $items = array();

    private $level2 = array();

    private $level3 = array();
    
    /**
     * @ignore
     */
    public function __construct()
    {
        $this->timestamp = strval(time()*1000); //time stamp in milli seconds
        $this->nonce = strval(hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong))));

    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * @ignore
     */
    public function __set($index, $value)
    {
        $this->_vars[$index] = $value;
    }
    
    /**
     * @ignore
     */
    public function __get($index)
    {
        return $this->_vars[$index];
    }

    /**
     * 
     * 
     * 
     */ 
    public function addItem($args = array())
    {

        $this->items[uniqid()] = $args;

    }

    /**
     * 
     * 
     * 
     */ 
    private function buildHeaders()
    {

        return array(
            'Content-Type' => 'application/json',
            'apikey' => strval($this->apiKey),
            'token' => strval($this->merchantToken),
            'Authorization' => $this->generateAuthorizationHeader(),
            'nonce' => $this->nonce,
            'timestamp' => $this->timestamp);
    }    

    public function getResponse()
    {
        return $this->_response;
    }

    public function debug($value)
    {
        $this->debug = $value;
    }

    /**
     * @ignore
     */
    public function generateHMAC()
    {
        $data = $this->apiKey . $this->nonce . $this->timestamp . $this->merchantToken . self::getPayload();
        return hash_hmac ($this->hashAlgorithm , $data , $this->apiSecret, false);
    } 

    /**
     * 
     * 
     */ 
    public function generateAuthorizationHeader()
    {
        return base64_encode(self::generateHMAC());        
    }

    /**
     * 
     * 
     * 
     */
    private function getPayload($transaction_type = '', $args = array()) 
    { 


        if ($transaction_type == "void" || 
            $transaction_type == 'refund' && isset($args['transaction_tag'])) {

            $data = array(
                "method" => $args['method_name'],
                "transaction_type" => $transaction_type,
                "amount" => $args['amount'],
                "currency_code" => $args['currency_code'],
                "transaction_tag" => $args['transaction_tag'],
            );

        } else { 

            $data = array(
                'merchant_ref'=> $this->merchant_ref,
                'transaction_type'=> $this->transaction_type,
                'method'=> 'credit_card',
                'amount'=> preg_replace("/[^0-9]/", '', $this->amount),
                'currency_code'=> $this->currency_code,
                'credit_card'=> array(
                    'type'=> $this->credit_card->type,
                    'cardholder_name'=> $this->credit_card->cardholder_name,
                    'card_number'=> $this->credit_card->card_number,
                    'exp_date'=> $this->credit_card->exp_date,
                    'cvv'=> $this->credit_card->cvv),
            );

            if (!empty($this->billingAddress->street)) { 
                !empty($this->billingAddress->name) ? $data['billing_address']['name'] = $this->billingAddress->name : false;
                !empty($this->billingAddress->city) ? $data['billing_address']['city'] = $this->billingAddress->city : false;
                !empty($this->billingAddress->country) ? $data['billing_address']['country'] = $this->billingAddress->country : false;
                !empty($this->billingAddress->email) ? $data['billing_address']['email'] = $this->billingAddress->email : false;
                !empty($this->billingAddress->street) ? $data['billing_address']['street'] = $this->billingAddress->street : false;
                !empty($this->billingAddress->state) ? $data['billing_address']['state_province'] = $this->billingAddress->state : false;
                !empty($this->billingAddress->zip) ? $data['billing_address']['zip_postal_code'] = $this->billingAddress->zip : false;
                !empty($this->billingAddress->phone) ? $data['billing_address']['phone']['number'] = $this->billingAddress->phone : false;
                !empty($this->billingAddress->phone) ? $data['billing_address']['phone']['type'] = 'PHONE' : false;
            }

            if (sizeof($this->items) > 0) { 
                $total = 0;
                
                $data['line_items'] = $this->items;

                isset($this->level2['tax1_amount']) ? $total += $this->level2['tax1_amount'] : false;
                isset($this->level2['tax2_amount']) ? $total += $this->level2['tax2_amount'] : false;
                isset($this->level3['alt_tax_amount']) ? $total += $this->level3['alt_tax_amount'] : false;

                foreach ($this->items as $i => $item) { 
                    if (isset($item['gross_net_indicator'])) { 
                        if ($item['gross_net_indicator'] == 0) { 
                            isset($item['tax_amount']) ? $total += $item['tax_amount'] : false;
                            isset($item['line_item_total']) ? $total += $item['line_item_total'] : false;
                        } else {                             
                            isset($item['line_item_total']) ? $total += $item['line_item_total'] : false;
                        }
                    } else { 
                        isset($item['tax_amount']) ? $total += $item['tax_amount'] : false;
                        isset($item['line_item_total']) ? $total += $item['line_item_total'] : false;
                    }
                }

                $this->amount = preg_replace("/[^0-9]/", '', $total);

            }

        }

        return json_encode($data, JSON_FORCE_OBJECT);
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function parseResponse($response)
    {

        $return = array(
            'authorized' => 0,
            'authcode' => '',
            'response_code' => '',
            'raw' => ''
            );

        $response = json_decode($response, 1);

        if (strtolower($response['transaction_status']) == 'approved') { 

            $return['authorized'] = 1;
            $return['authcode'] = $response['transaction_id'] . '::' . $response['transaction_tag'];
            $return['response_code'] = $response['transaction_id'] . '::' . $response['transaction_tag'];
            $return['raw'] = json_encode($response);

        } else {
            if (array_key_exists('Error', $response)) { 
                $return['response_code'] = $response['Error']['messages'][0]['code'] . ' : ' . $response['Error']['messages'][0]['description'];
            } else { 
                $return['response_code'] = 'UNKNOWN: '. $response['correlation_id'] . ' : ' . $response['transaction_status'];
            }
            $return['raw'] = json_encode($response);
        }

        return $return;        
    }

    /**
     * 
     * 
     * 
     * 
     */ 
    public function postTransaction($url = '')
    {

        empty($url) ? $url = $this->server : false;

        $return = $this->runCURLPost($url, self::getPayload(), self::buildHeaders());

        return self::parseResponse($return);

        
    }

    /**
     * 
     */
    private function runCURLPost($server, $data, $headers = array())
    {

        $curlHeaders = array();

        if (sizeof($headers) > 0) {
            foreach($headers as $key => $value) { 
                $curlHeaders[] = "$key: $value";
            }                    
        }

        is_array($data) ? $query = http_build_query($data, '', '&') : $query = $data;

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
    public function setApiKey($value)
    {
        $this->apiKey = $value;

        return $this;
    }

    /**
     * 
     * 
     */
    public function setApiSecret($value)
    {
        $this->apiSecret = $value;

        return $this;
    } 

    /**
     * 
     * 
     * 
     * 
     */ 
    public function setAddress($type = 'billing', $args = array())
    {
        
        $array = array(
                "name" => '',
                "street" => '',
                "city" => '',
                "state_province" => '',
                "zip_postal_code" => '',
                "country" => '',
                "phone" => '',
                "email" => '');

        empty($args['country']) ? $args['country'] = 'US' : false;

        if (strtolower(trim($type)) == 'shipping') { 
            $this->shippingAddress = (object) $array;

            !empty($args['name']) ? $this->shippingAddress->name = trim($args['name']) : false;
            !empty($args['street']) ? $this->shippingAddress->street = trim($args['street']) : false;
            !empty($args['city']) ? $this->shippingAddress->city = trim($args['city']) : false;
            !empty($args['state']) ? $this->shippingAddress->state = trim($args['state']) : false;
            !empty($args['zip']) ? $this->shippingAddress->zip = trim($args['zip']) : false;
            !empty($args['country']) ? $this->shippingAddress->country = trim($args['country']) : false;
            !empty($args['phone']) ? $this->shippingAddress->phone = trim($args['phone']) : false;
            !empty($args['email']) ? $this->shippingAddress->email = trim($args['email']) : false;

        } else {

            $this->billingAddress = new StdClass();

            !empty($args['name']) ? $this->billingAddress->name = trim($args['name']) : false;
            !empty($args['street']) ? $this->billingAddress->street = trim($args['street']) : false;
            !empty($args['city']) ? $this->billingAddress->city = trim($args['city']) : false;
            !empty($args['state']) ? $this->billingAddress->state = trim($args['state']) : false;
            !empty($args['zip']) ? $this->billingAddress->zip = trim($args['zip']) : false;
            !empty($args['country']) ? $this->billingAddress->country = trim($args['country']) : false;
            !empty($args['phone']) ? $this->billingAddress->phone = trim($args['phone']) : false;
            !empty($args['email']) ? $this->billingAddress->email = trim($args['email']) : false;

        }

        return $this;    
    }

    /**
     * 
     * 
     * 
     */ 
    public function setCreditCardInfo($args = array())
    {
        $this->credit_card = (object) array('card_number' => '',
            'type' => '',
            'cardholder_name' => '',
            'cvv' => '',
            'exp_date' => '',
            );
        
        $this->credit_card->card_number = strval(trim(preg_replace("/[^0-9]/", '', $args['card_number'])));
        $this->credit_card->type = strtolower(trim($args['card_type']));
        $this->credit_card->cardholder_name = trim($args['card_holder_name']);
        $this->credit_card->cvv = strval(trim($args['card_cvv']));
        $this->credit_card->exp_date = strval(trim($args['card_expiry']));

    }

    /**
     * 
     * 
     * 
     */
    public function setCurrency($value = 'USD')
    {
        $this->currency_code = strtoupper(trim($value));
        return $this;
    } 

    /**
     * 
     * 
     */ 
    public function setMerchantReference($value)
    {
        $this->merchant_ref = $value;
        return $this;
    }

    /**
     * 
     * 
     */ 
    public function setMerchantToken($value)
    {
        $this->merchantToken = $value;
        return $this;
    }

    /**
     * 
     * 
     * 
     */ 
    public function setSandboxMode()
    {
        $this->server = 'https://api-cert.payeezy.com/v1/transactions';
    }

    /**
     * 
     * 
     */ 
    public function setTransactionType($value)
    {
        $value = strtolower(trim($value));

        if ($value == 'authorize') { 
            $this->payload['transaction_type'] = 'authorize';
        } else { 
            $this->payload['transaction_type'] = 'purchase';
        }
    }

    /**
    * Payeezy
    *
    * Purchase Transaction
    */

    public function purchase($args = array())
    {
        $this->transaction_type = 'purchase';
        $this->payload = $this->getPayload("purchase", $args);        
        return $this->postTransaction();
    }

    /**
     * Payeezy German Direct Debit
     *
     * Purchase Transaction
     */

    public function processPurchaseTransactionWithAVSDirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "purchaseGDDAVS");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy German Direct Debit
     *
     * Credit Transaction
     */

    public function processCreditTransactionWithAVSDirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "creditGDDAVS");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy German Direct Debit
     *
     * Purchase Transaction
     */

    public function processPurchaseTransactionWithSoftDescDirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "purchaseGDDSoftDesc");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy German Direct Debit
     *
     * Credit Transaction
     */

    public function processCreditTransactionWithSoftDescDirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "creditGDDSoftDesc");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy German Direct Debit
     *
     * Purchase Transaction
     */

    public function processPurchaseTransactionWithL2L3DirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "purchaseGDDL2L3");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy German Direct Debit
     *
     * Credit Transaction
     */

    public function processCreditTransactionWithL2L3DirectDebit($args = array())
    {
        $payload = $this->getPayload($args, "creditGDDL2L3");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    
    /**
     * Payeezy
     *
     * Capture Transaction
     */

    public function capture($args = array())
    {
        $payload = $this->getPayload($args, "capture");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy
     *
     * Void Transaction
     */

    public function void($args = array())
    {
        $payload = $this->getPayload($args, "void");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy
     *
     * Refund Transaction
     */

    public function refund($args = array())
    {
        $payload = $this->getPayload($args, "refund");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }


//ET121823

    /**
     * Payeezy
     *
     * split Transaction
     */

    public function split_shipment($args = array())
    {
        $payload = $this->getPayload($args, "split");
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Telecheck
     *
     * Purchase Transaction
     */

    public function telecheck_purchase($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'purchase');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Telecheck
     *
     * Void Transaction
     */

    public function telecheck_void($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'void');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Telecheck
     *
     * Tagged Void Transaction
     */

    public function telecheck_tagged_void($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'void');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Telecheck
     *
     * Tagged Refund Transaction
     */

    public function telecheck_tagged_refund($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'refund');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }


    /**
     * Payeezy Value Check
     *
     * Purchase Transaction
     */

    public function valuelink_purchase($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'purchase');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value Check
     *
     * Refund Transaction
     */

    public function valuelink_refund($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'refund');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value Check
     *
     * Void Transaction
     */

    public function valuelink_void($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'void');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value Check
     *
     * Cashout Transaction
     */

    public function valuelink_cashout($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'cashout');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value check
     *
     * Reload Transaction
     */

    public function valuelink_reload($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'reload');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value check
     *
     * Partial Purchase Transaction
     */

    public function valuelink_partial_purchase($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'partial_purchase');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value check
     *
     * Activation Transaction
     */

    public function valuelink_activation($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'activation');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
    }

    /**
     * Payeezy Value check
     *
     * Deactivation Transaction
     */

      public function valuelink_deactivation($args = array())
    {
      $payload = $this->getTeleCheckValueLinkPayLoad($args, 'deactivation');
      $headerArray = $this->hmacAuthorizationToken($payload);
      return $this->postTransaction($payload, $headerArray);
      }

      /**
       * Payeezy Value check
       *
       * Balance Inquiry Transaction
       */

    public function valuelink_balance_inquiry($args = array())
    {
        $payload = $this->getTeleCheckValueLinkPayLoad($args, 'balance_inquiry');
        $headerArray = $this->hmacAuthorizationToken($payload);
        return $this->postTransaction($payload, $headerArray);
    }
    

}
