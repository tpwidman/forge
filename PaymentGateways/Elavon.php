<?php
/**
 * 
 * 
 * 
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * 
 * @copyright 2002 Zeekee Interactive
 * 
 * 
 * 
 * 
 */
class Elavon
{


    private $urlProcess;

    private $urlBatch;

    private $urlXML;

    private $urlAccount;

    private $merchantId;                 // Converge ID as provided by Elavon

    private $userId;                     // Converge user ID as configured on Converge (case sensitive)

    private $pin;                        // provided by Converge

    private $payload = array(
        'ssl_merchant_id' => '',         // Converge ID as provided by Elavon
        'ssl_user_id' => '',             // Converge user ID as configured on Converge (case sensitive)
        'ssl_pin' => '',                 // Converge PIN as generated within Converge (case sensitive)
        'ssl_transaction_type' => '',    // Transaction type
        'ssl_show_form' => 'true'        //
        'ssl_card_number' => '',         // Card number (required for hand-keyed transactions where the track data is not present
        'ssl_exp_date' => '',            // Expiration date (required to be used with card number on hand-keyed
        'ssl_amount' => '',              // Converge PIN as generated within Converge (case sensitive)        
        );

    /**
     * 
     * @ignore
     */
    public function __construct($merchantId, $userId, $pin, $mode = 'production')
    {

        $this->merchandId = $merchandId;
        $this->userId = $userId;
        $this->pin = $pin;
        $this->setServer($mode);

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
    public function process()
    {
        
    }

    /**
     * 
     * 
     */ 
    public function processBatch()
    {
        
    }

    /**
     * 
     * 
     */ 
    public function processXML()
    {

    }

    /**
     * 
     * 
     */ 
    public function accountXML()
    {
        
    }

    /**
     * 
     * 
     * 
     */ 
    public function setServer($mode = 'production') 
    { 
        if ($mode == strtolower(trim($mode)) == 'demo') { 
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
