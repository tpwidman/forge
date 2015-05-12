<?php

namespace zeekee\other\Flickr;

class Galleries extends Flickr
{

    public function __construct($api_key = null)
    {
        parent::__construct($api_key);
        $this->method = str_replace('\\', '.', strtolower(get_class()));
    }



 /**
     * get a list of all the photosets available to this id.
     * 
     * 
     */ 
    public function getList()
    {
        $this->setMethod('flickr.galleries.getList');        
        $return = $this->sendToFlickr();

        print_r($return);

        //return (object) $return['photosets']['photoset'];

    }

 /**
     * get a list of all the photos associated with the provided gallery
     * 
     * @param integer $paramname [the id number provided by flickr]
     * @param string  $paramname description
     * @param integer $paramname description
     * @param integer $paramname description
     * 
     * 
     */ 
    public function getPhotos($id = 0, $extras = 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o', $per_page = 500, $page = 1)
    {
        $this->setMethod('flickr.galleries.getPhotos');
        
        $params = array(
            'gallery_id' => $id,
            'extras' => $extras,
            'per_page' => $per_page,
            'page' => $page
        );

        return $this->sendToFlickr($params);

    }

    

}