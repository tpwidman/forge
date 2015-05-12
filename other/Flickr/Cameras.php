<?php

namespace zeekee\other\Flickr;

class Cameras extends Flickr
{

    public function __construct($api_key = null)
    {
        parent::__construct($api_key);
        $this->method = str_replace('\\', '.', strtolower(get_class()));
    }




    

}