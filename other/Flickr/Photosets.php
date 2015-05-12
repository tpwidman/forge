<?php
namespace zeekee\forge\other\Flickr;

class Photosets extends Flickr
{

	private $method;

    public function __construct($api_key = null)
    {
    	parent::__construct($api_key);
    	$this->method = str_replace('\\', '.', strtolower(get_class()));
    }

    public function addPhoto()
    {

    }

    public function create()
    {
    	
    }

	public function delete()
    {
    	
    }

    public function editMeta()
    {
    	
    }

    public function editPhotos()
    {
    	
    }

    public function getContext()
    {
    	
    }

    /**
     * 
     * 
     * 
     */ 
	public function getInfo($id = 0)
    {
    	$params = array(
    		'photoset_id' => $id
		);

    	$this->setMethod($this->method. '.getInfo');    	
    	$this->sendRequest($params);

    	if ($this->requestResult()) {
			return (object) $this->result['photoset'];
    	} else { 
    		return array();
    	}    	
    	
    }

    /**
     * Returns the photosets belonging to the specified user.
     * 
     * Authentication
     * 
     * This method does not require authentication.
     * 
     * @param string $page [The page of results to get. Currently, if this is not provided, all sets are returned, but this behaviour may change in future.]
     * @param integr $per_page [The number of sets to get per page. If paging is enabled, the maximum number of sets per page is 500.]
     * @param string $paramname [primary_photo_extras A comma-delimited list of extra information to fetch for the primary photo. Currently supported fields are: license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o] 
     * 
     */ 
    public function getList($page = 1, $per_page = 500, $extras = 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o')
    {
    	
    	$params = array(
    		'page' => $page,
    		'per_page' => $per_page,
    		'primary_photo_extras' => $extras
    		);        

    	$this->setMethod($this->method . '.getList');
    	
    	$this->sendRequest($params);

    	if ($this->requestResult()) {
			return $this->result['photosets']['photoset'];
    	} else { 
    		return array();
    	}    	    
    }

    /**
     * Get all the photos in a collection - however it does assume all are JPG's.  If they vary 
     * you have to use the photos class to get more information.
     * 
     * 
     */ 
    public function getPhotos($id = 0)
    {
		$params = array(
    		'photoset_id' => $id
		);

    	$this->setMethod($this->method. '.getPhotos');    	
    	$this->sendRequest($params);

    	if ($this->requestResult()) {
            $photoset = array();

            foreach ($this->result['photoset']['photo'] as $n => $value) {
                $value = (object) $value;
                $this->result['photoset']['photo'][$n]['thumbnail'] = 'https://farm' . $value->farm . '.staticflickr.com/' . $value->server  . '/' . $value->id . '_' . $value->secret  . '_t.jpg';
                $this->result['photoset']['photo'][$n]['square'] = 'https://farm' . $value->farm . '.staticflickr.com/' . $value->server  . '/' . $value->id . '_' . $value->secret  . '_sq.jpg';
                $this->result['photoset']['photo'][$n]['normal'] = 'https://farm' . $value->farm . '.staticflickr.com/' . $value->server  . '/' . $value->id . '_' . $value->secret  . '.jpg';
                $this->result['photoset']['photo'][$n]['original'] = 'https://farm' . $value->farm . '.staticflickr.com/' . $value->server  . '/' . $value->id . '_' . $value->secret  . '_o.jpg';
            }
			return (object) $this->result['photoset'];
    	} else {     		
    		return array();
    	}    	    	
    }

	public function orderSets()
    {
    	
    }

 	public function removePhoto()
    {
    	
    }

    public function removePhotos()
    {
    	
    }

	public function setPrimaryPhoto()
    {
    	
    }

}