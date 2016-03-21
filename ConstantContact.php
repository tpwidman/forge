<?php
/**
 * This is a basic clean up to allow an easier way to 
 * interface with Constant Contact.
 * 
 * 
 * @author  Zeekee Interactive (N.Colbert)
 * 
 * 
 */ 

namespace zeekee;

use Ctct\ConstantContact AS CC;
use Ctct\Components\Contacts\Contact AS Contact;
use Ctct\Components\Contacts\ContactList AS ContactList;
use Ctct\Components\Contacts\EmailAddress AS CCEmailAddress;
use Ctct\Exceptions\CtctException;

class ConstantContact
{
    private $_vars;    
    private $_errorMessage;
    private $cc;
    private $apiKey;
    private $accessToken;
    
    /**
    * Injects dependencies into the class.
    *
    * @param $cc object An instance of CtCt\ConstantContact
    * @return void
    */
    function __construct($apikey = '', $token = '')
    {
        $this->apiKey = $apikey;
        $this->accessToken = $token;

        $this->cc = new CC($this->apiKey);
    
    }

    /**
     * 
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
     */ 
    public function addContact($email, $firstName, $lastName, $lists = array(), $additional = array(), $actualUser = true)
    {
        try {
            
            $contact = new Contact();
            
            $contact->addEmail($email);

            foreach ($lists as $v) { 
                $contact->addList($v);
            }
        
            $contact->first_name = $firstName;
            
            $contact->last_name = $lastName;

            !empty($additional['company']) ? $contact->company_name = $additional['company'] : false;

            !empty($additional['job_title']) ? $contact->job_title = $additional['job_title'] : false;

            !empty($additional['work_phone']) ? $contact->work_phone = $additional['work_phone'] : false;

            //$contact->addAddress(Address::create( array("address_type"=>"BUSINESS","line1"=>$street,"city"=>$city,"state"=>$state,"postal_code"=>$zip)));
            
            $this->cc->addContact($this->accessToken, $contact, $actualUser);
            
            return true;
        
        } catch (CtctException $ex) {
            $error = (array) $ex->getErrors();
            $this->_errorMessage = $error[0]['error_message'];
            return false;            
        }        

    }
 
    /**
     * 
     * 
     */ 
    public function editContact()
    {

    }

    /**
     * generate a access token
     * 
     */ 
    public function generateToken()
    {

    }

    public function getContactByEmail()
    {
        $response = $cc->getContactByEmail($this->accessToken, $_POST['email']);
    }

    /**
     * Fetches an array of lists and returns them in a name to ID order, for quick array look up.   
     * 
     *  @return array
     */ 
    public function getList($id)
    {
        $list = $this->cc->getList($this->accessToken, $id);

        return $list;
        
    }

    /**
     * Fetches an array of lists and returns them in a name to ID order, for quick array look up.   
     * 
     *  @return array
     */ 
    public function getListContactsByEmail($id)
    {
        
        $array = array();
        
        $contacts = $this->cc->getContactsFromList($this->accessToken, $id);

        if (is_array($contacts->results)) { 
            foreach ($contacts->results as $n => $contact) { 
                foreach ($contact->email_addresses as $i => $person) { 
                    $array[$person->email_address] = $contact;
                }
            }
        }

        return $array;
        
    }

    /**
     * Fetches an array of lists and returns them in a name to ID order, 
     * for quick array look up.   
     * 
     *  @return array
     */ 
    public function getLists()
    {
        $lists = $this->cc->getLists($this->accessToken);
        $organized_list = array();
        foreach($lists as $list) {
            $organized_list[$list->name] = $list;
        }
        return $organized_list;
    }

    /**
     * Get the contents of the _errorMessage variable.
     * 
     * @return string 
     */ 
    public function lastError()
    {
        return $this->_errorMessage;
    }

    /**
     * 
     * 
     * 
     */ 
    public function removeContact()
    {

    }
    
}
