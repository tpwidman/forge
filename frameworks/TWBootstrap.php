<?php
/**
 * @author Nicolas Colbert <ncolbert@zeekee.com>
 * @copyright 2002 Zeekee Interactive
 */
/**
 *                                                                   
 * A twitter bootstrap abstraction class
 * 
*                                                                    
 */
class TWBootstrap
{
    
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
     * 
     * return a well-formed HTML mark-up to insert an alert
     * @param string $message the string to be placed in the body.
     * @param string $type the type of alert that you want to display [success = default, info, warning, danger]
     * 
     * @return string
     */ 
    public function alert($message, $type = 'success', $id = '')
    {        
        if ($type != 'success' && $type != 'warning' && $type != 'danger') {
            $type = 'success';
        }
        empty($id) ? $id = $type . 'Alert' : false;
        $string = '<div class="alert alert-' . $type . ' alert-dismissible fade in flash" id="' . $id . '" role="alert">' . $message . '</div>';
        return $string;
    }
}
