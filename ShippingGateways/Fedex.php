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
class Fedex
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
        '0' => array('label' => 'USPS First Class Mail', 'handle_charge' => 0, 'enabled' => 0));
    /**
     * @ignore
     */
    public function __construct()
    {

    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        
    }

    /**
     * [getRates description]
     * @return [type] [description]
     */
    public function getRates()
    {
        $aRates = array();

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
