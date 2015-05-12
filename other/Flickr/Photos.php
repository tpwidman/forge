<?php
namespace zeekee\other\Flickr;

class Photos extends Flickr
{

    public function __construct($api_key = null)
    {
        parent::__construct($api_key);
        $this->method = str_replace('\\', '.', strtolower(get_class()));
    }    /**
     * 
     * 
     * 
     */ 
    public function getInfo($id, $secret)
    {
		$params = array(
    		'photo_id' => $id,
    		'secret' => $secret
		);

    	$this->setMethod($this->method. '.getInfo');    	
    	$this->sendRequest($params);

    	if ($this->requestResult()) {            
			return (object) $this->result['photo'];
    	} else { 
    		return array();
    	}    	    
    }


    public function getSizes($id = 0)
    {
		$params = array(
    		'photo_id' => $id
		);

		$return = array();

    	$this->setMethod($this->method. '.getSizes');    	
    	$this->sendRequest($params);

    	if ($this->requestResult()) {
    		foreach ($this->result['sizes']['size'] as $n => $size) {
    			$return[str_replace(' ', '_', strtolower($size['label']))] = $size;
    		}
			return (object) $return;
    	} else { 
    		return array();
    	}    	  

    }


}