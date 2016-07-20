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
class Stampscom
{

    private $IntegrationID = '';

    private $Authenticator;
    
    //API LOGIN
    
    private $Username = "XXXXXXXXXXXXXXX";
    
    private $Password = "XXXXXXXXXXXXXXX";
    
    private $wsdl = "https://swsim.stamps.com/swsim/swsimv42.asmx?wsdl";
    
    public $client;
    
    public $output;
    
    private $version = 42;

    public $functions;

    public $types;

    public $sample = false;
    
    public $insurance = true;

    private $Indicium;

    public $data;

    private $error;

    public $url;

    private $rates;

    private $shipTo;

    private $shipFromZipCode;

    private $shipFrom;

    public $selectedRate;

    private $address;

    private $account;

    private $trackEvents;

    private $ControlTotal = 0;

    public $ServiceType = array(
        "US-FC" =>  "USPS First-Class Mail",
        "US-MM" =>  "USPS Media Mail",
        "US-PP" =>  "USPS Parcel Post ",
        "US-PM" =>  "USPS Priority Mail",
        "US-XM" =>  "USPS Priority Mail Express",
        "US-EMI" =>  "USPS Priority Mail Express International",
        "US-PMI" =>  "USPS Priority Mail International",
        "US-FCI" =>  "USPS First Class Mail International",
        "US-CM" =>  "USPS Critical Mail",
        "US-PS" =>  "USPS Parcel Select",
        "US-LM" =>  "USPS Library Mail"
    );

    public $URLS = array(
    "HomePage" => 'Store home page.',
    "AccountSettingsPage" => 'Account settings page.',
    "EditCostCodesPage" => ' Edit Cost Codes page.',
    "OnlineReportsPage" => ' Online Reports page.',
    "HelpPage" => ' Help page.',
    "OnlineReportingHistory" => ' Search Print page.',
    "OnlineReportingRefund" => ' Refund page.',
    "OnlineReportingPickup" => ' USPS Pick-Up page.',
    "OnlineReportingSCAN" => ' SCAN Manifest page.',
    "OnlineReportingClaim" => ' File Claim page.',
    "StoreChangePlan" => ' Change Billing Plan page.',
    "WebClientHome" => ' Web postage printing.',
    "ReportsBalances" => ' Balances report.',
    "ReportsExpenses" => ' Expenses report.',
    "ReportsPrints" => ' Prints report.',
    "StoreBuyPostage" => ' Buy postage.',
    "StoreMeters" => ' Meter list (for enterprise accounts only).',
    "StoreUsers" => ' Use list (for multi-user accounts only).',
    "StorePaymentMethods" => ' Payment methods.',
    "StoreCorpContactInfo" => ' Corporate contact information.',
    "StoreMeterUsers" => ' Meter user list (for enterprise accounts only).',
    "StoreMeterSettings" => ' Meter settings.',
    "StoreMeterAddress" => ' Meter address.',
    "StoreShippingAddresses" => ' Shipping address.',
    "StoreReferAFriend" => ' Refer-a-friend.',
    "StoreAccountCredit" => ' Account credit.',
    "StoreReorder" => ' Re-order.',
    "StoreMyProfile" => ' User profile.',
    "StorePassword" => ' Change password.',
    "StoreCommPreferences" => ' Communication preferences.',
    "StoreNetStampsLabels" => ' Purchase NetStamps labels.',
    "StoreShippingLabels" => ' Purchase shipping labels.',
    "StoreMailingLabels" => ' Purchase mailing labels.',
    "StoreScalesAndPrinters" => ' Purchase scales and printers.',
    "StoreFreeUSPSSupplies" => ' Order free USPS supplies.',
    "StoreBubbleMailers" => ' Purchase bubble mailers.',
    "StoreShippingSupplies" => ' Purchase shipping supplies.',
    "StoreScales" => ' Purchase scales.',
    "StoreAveryNetStampsLabels" => ' Purchase Avery NetStamps labels.',
    "StoreAveryMailingLabels" => ' Purchase Avery mailing labels.',
    "StoreMeterContactInfo" => ' Meter contact information.',
    "StoreEditMeterAddress" => ' Edit meter address.',
    "StoreHome" => ' Online store home page.',
    "StoreAccount" => ' Account information.',
    "StoreCostCode" => ' Cost codes.',
    "StoreHistory" => ' History.',
    "StoreFaq" => ' FAQ',
    "StoreCustomerHome" => 'Customer home page.');

    function __construct($username = 'xxxxxxxxxxxx', $password = 'xxxxxxxxxx', $integrationId = 'xxxxxxxxxxxxxxxxx', $server = 'production')
    {

        $this->Username = $username;
        $this->Password = $password;
        $this->IntegrationID = $integrationId;        
        $this->setServer($server);
        $this->connect();
    
    }

    public function accountInformation()
    {        
        return $this->account;
    }

    
    /**
     * required
     * 
     */
    public function addInsurance($true = 'true')
    {

        if (strtolower(trim($true)) == '1' || strtolower(trim($true)) == 'true') { 
            $this->insurance = true;
        } else { 
            $this->insurance = false;
        }
    } 

    /**
     * required
     * 
     * 
     */ 
    public function AutoBuy($enable = 'true', $purchase = 0, $trigger = 0)
    {
        
        ($enable == 'true') ? $enable = 1 : $enable = 0;

        $data = array(
            'Authenticator' => $this->Authenticator,
            'AutoBuySettings' => array(
                'AutoBuyEnabled' => $enable
                ));            

        ($enable) ? $data['AutoBuySettings']['PurchaseAmount'] = $purchase : false;
        ($enable) ? $data['AutoBuySettings']['TriggerAmount'] = $trigger : false;

        try {
            $info = $this->client->SetAutoBuy($data);                
            $this->Authenticator = $info->Authenticator;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here           
            $this->data = ''; 
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        } 
        
    }

    /**
     * 
     * recommended
     * 
     */
    public function CarrierPickup() 
    {

    } 


    /**
     * required
     * 
     * 
     * 
     */ 
    public function CancelIndicium($id = '')
    {
        $data = array(
            'Authenticator' => $this->Authenticator,
            'StampsTxID' => $id);            

        try {
            $info = $this->client->CancelIndicium($data);                
            $this->Authenticator = $info->Authenticator;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }          
    }

    /**
     * required
     * 
     */ 
    public function CleanseAddress($address = array(), $fromZipCode = '')
    {
        


        $data = array(
            'Authenticator' => $this->Authenticator,
            'Address' => $this->setAddress($address),
            );        

        !empty($fromZipCode) ? $data['FromZIPCode'] = $fromZipCode : $data['FromZIPCode'] = $this->shipFromZipCode;


        try {
            
            $info = $this->client->CleanseAddress($data);                
            
            $this->Authenticator = $info->Authenticator;
            
            return $info->Address;

        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            

            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }          
    }

    /**
     * recommended
     * 
     * 
     */    
    public function ClickReturn()
    {

    } 


    /**
     * required
     * 
     * 
     */ 
    public function connect()
    {
        $authData = array(
            "Credentials"       => array(
                "IntegrationID"     => $this->IntegrationID,
                "Username"          => $this->Username,
                "Password"          => $this->Password
        ));
        
        try {
            $this->client = new \SoapClient($this->server, array("trace" => 1, "exception" => 1));     
            $auth = $this->client->AuthenticateUser($authData);        
            $this->Authenticator = $auth->Authenticator;
            $this->getAccountInfo();
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }        
    }

    /**
     * recommended
     * 
     * 
     * 
     */ 
    public function CreateScanForm()
    {

    }

    /**
     * required
     * 
     * 
     */ 
    public function CreateIndicium($to, $from, $rate, $id = '', $sample = false)
    {

        $data = array(
            'Authenticator' => $this->Authenticator,
            'IntegratorTxID' => $id,
            'TrackingNumber' => '',
            'Rate' => $rate,
            'From' => $from,
            'To' => $to,
        );

        ($sample) ? $data['SampleOnly'] = 'true' : $data['SampleOnly'] = 'false';

        try {
            
            $return = $this->client->CreateIndicium($data);

            $this->Authenticator = $return->Authenticator;            
            
            $this->ControlTotal = $return->PostageBalance->ControlTotal;            

            $this->Indicium = $return;

            return true;
            
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        } 
        
    }

    /**
     * 
     * 
     * 
     */ 
    public function dumpVar($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

     /**
     * required
     * 
     */ 
    public function getAccountInfo()
    {
        $authData = array('Authenticator' => $this->Authenticator);

        try {
            $return = $this->client->GetAccountInfo($authData);
            $this->Authenticator = $return->Authenticator;
            $this->account = $return->AccountInfo;
            $this->ControlTotal = $this->account->PostageBalance->ControlTotal;            
            $this->shipFromZipCode = $return->AccountInfo->LPOZip;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }        
    }


    /**
     * required
     * 
    */
    public function GetPurchaseStatus($transactionId = '') 
    {

        $data = array(
            'Authenticator' => $this->Authenticator,
            'TransactionID' => $transactionId
            ); 

        try {
            $return = $this->client->GetPurchaseStatus($data);
            $this->Authenticator = $return->Authenticator;
            $this->data = $return;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring}) : $transactionId";  
        }        

    }
    

    /**
    * 
    * 
    * 
    */
    public function getSelectedRate()
    {
        return $this->selectedRate;
    }

    /**
     * required
     * 
     * 
     */ 
    public function getRates($toZipCode = '', $fromZipCode = '', $weight , $shipDate, $insured = 0, $package = 'Package')
    {
        
        ($enable == 'true') ? $enable = 1 : $enable = 0;

        $data = array(
            'Authenticator' => $this->Authenticator,
            'Rate' => array(
                'FromZIPCode' => $fromZipCode,
                'ToZIPCode' => $toZipCode,
                'WeightLb' => $weight,
                'PackageType' => $package,
                'ShipDate' => date('Y-m-d', strtotime($shipDate)),
                'InsuredValue' => $insured
                ));            
        try {

            $info = $this->client->GetRates($data);                

            $this->Authenticator = $info->Authenticator;
            
            $this->rates = $info->Rates->Rate;

            return true;

        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        } 
        
    }

    public function Indicium()
    {
        return $this->Indicium;
    }

    public function isConnected()
    {
        if ($this->account->CustomerID > 0) { 
            return true;
        } else { 
            return false;
        }
    }
    
    /**
     * 
     * 
     * $pieces = preg_split('/(?=[A-Z])/',$str);
     */ 
    public function GetURL($url = '')
    {

        $data = array(
            'Authenticator' => $this->Authenticator,
            'URLType' => $url,
            'ApplicationContext' => ''
            );        
    
        try {
            $info = $this->client->GetURL($data);                
            $this->Authenticator = $info->Authenticator;
            $this->url = $info->URL;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }          


    }

    /**
     * 
     * 
     * 
     */
    public function getServiceType($value) 
    {
        return $this->ServiceType[$value];
    } 

    /**
     * 
     * 
     * 
     */ 
    public function lastError()
    {
        return $this->error;
    }    

    /**
     * required
     * 
     */ 
    public function PurchasePostage($amount = 0, $transactionId = '')
    {
        
        $data = array(
            'Authenticator' => $this->Authenticator,
            'PurchaseAmount' => number_format($amount, 2, '.', ''),
            'ControlTotal' => number_format($this->ControlTotal, 2, '.', '')
            );        
        
        !empty($transactionId) ? $data['IntegratorTxID'] = $transactionId : false;
        
        try {
            $info = $this->client->PurchasePostage($data);                
            $this->Authenticator = $info->Authenticator;
            return $info;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (CODE: {$fault->faultcode}, MESSAGE: {$fault->faultstring})";  
        }          
    }

    
    /**
     * 
     * 
     * 
     */ 
    public function rateSchedule()
    {
        return $this->rates;
    }

    /**
     * required
     * 
     */
    public function setRate($selectedRate)
    {
        $rate;
        foreach ($this->rates as $k => $value) { 
            if ($value->ServiceType == $selectedRate) { 
                $rate = $value;
                break;
            }
        }
        unset($rate->AddOns);
        $this->selectedRate = $rate;
     
    } 


    /**
     * required
     * 
     */
    public function setSampleMode($true = 'true')
    {
        if (strtolower(trim($true)) == '1' || strtolower(trim($true)) == 'true') { 
            $this->sample = true;
        } else { 
            $this->sample = false;
        }
    } 


    /**
     * required
     * 
     */
    public function setShipFrom($address)
    {

        $this->shipFrom = $address;
    }

    /**
     * required
     * 
     */
    public function setShipTo($address)
    {

        $this->shipTo = $address;
    } 

    /**
     * 
     * 
     * 
     * 
     */ 
    public function setVersion($version = '') { 
        if (!empty($version) && is_numeric($version)) { 
            $this->version = $version;
        }
    }

    /**
     * 
     * 
     * 
     */ 
    public function setServer($server = 'production') { 
        if (strtolower(trim($server)) == 'testing') { 
            $this->server = 'https://swsim.testing.stamps.com/swsim/swsimv' . $this->version . '.asmx?wsdl';
        } else { 
            $this->server = 'https://swsim.stamps.com/swsim/swsimv' . $this->version . '.asmx?wsdl';
        }

    }

    /**
     * 
     * 
     */ 
    public function setAddress($vars = array(), $which = 'shipto')
    {
        $array = $corrected = array();

        foreach ($vars as $key => $value) { 
            $array[strtolower(trim($key))] = $value;
        }
        
        !empty($array['fullname']) ? $corrected['FullName'] = $array['fullname'] : false;
        !empty($array['nameprefix']) ? $corrected['NamePrefix'] = $array['nameprefix'] : false;
        !empty($array['firstname']) ? $corrected['FirstName'] = $array['firstname'] : false;
        !empty($array['middlename']) ? $corrected['MiddleName'] = $array['middlename'] : false;
        !empty($array['lastname']) ? $corrected['LastName'] = $array['lastname'] : false;
        !empty($array['namesuffix']) ? $corrected['NameSuffix'] = $array['namesuffix'] : false;
        !empty($array['title']) ? $corrected['Title'] = $array['title'] : false;
        !empty($array['department']) ? $corrected['Department'] = $array['department'] : false;
        !empty($array['company']) ? $corrected['Company'] = $array['department'] : false;
        !empty($array['address1']) ? $corrected['Address1'] = $array['address1'] : false;
        !empty($array['address2']) ? $corrected['Address2'] = $array['address2'] : false;
        !empty($array['address3']) ? $corrected['Address3'] = $array['address3'] : false;
        !empty($array['city']) ? $corrected['City'] = $array['city'] : false;
        !empty($array['state']) ? $corrected['State'] = $array['state'] : false;
        !empty($array['zipcode']) ? $corrected['ZIPCode'] = $array['zipcode'] : false;
        !empty($array['zipcodeaddon']) ? $corrected['ZIPCodeAddOn'] = $array['zipcodeaddon'] : false;
        !empty($array['dpb']) ? $corrected['DPB'] = $array['dpb'] : false;
        !empty($array['checkdigit']) ? $corrected['CheckDigit'] = $array['checkdigit'] : false;
        !empty($array['province']) ? $corrected['Province'] = $array['province'] : false;
        !empty($array['country']) ? $corrected['Country'] = $array['country'] : false;
        !empty($array['postalcode']) ? $corrected['PostalCode'] = $array['postalcode'] : false;
        !empty($array['urbanization']) ? $corrected['Urbanization'] = $array['urbanization'] : false;
        !empty($array['phonenumber']) ? $corrected['PhoneNumber'] = $array['phonenumber'] : false;
        !empty($array['fromzipcode']) ? $corrected['FromZIPCode'] = $array['fromzipcode'] : false;

        return $corrected;
     
    }

    /**
     * 
     * 
     * 
     */ 
    public function setFromZipCode($zipcode = '')
    {
        $this->shipFromZipCode = $zipcode;
    }

    public function trackEvents()
    {
        return $this->trackEvents;
    }

    /**
     * optional
     * 
     * 
     */ 
    public function TrackShipment($vars = array())
    {
        $data = array(
            'Authenticator' => $this->Authenticator
            );        

        !empty($vars['StampsTxID']) ? $data['StampsTxID'] = $vars['StampsTxID'] : false;
        !empty($vars['TrackingNumber']) ? $data['TrackingNumber'] = $vars['TrackingNumber'] : false;

        try {
            $info = $this->client->TrackShipment>($data);                
            $this->Authenticator = $info->Authenticator;
            $this->trackEvents = $info->TrackingEvents;
            return true;
        } catch ( SoapFault $fault ) { // Do NOT try and catch "Exception" here            
            $this->data = '';
            $this->error = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";  
        }          




    }
   

}
