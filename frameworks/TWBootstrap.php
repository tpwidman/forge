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
    public function alert($message, $type = 'success')
    {
        if ($type != 'success' && $type != 'warning' && $type != 'danger') {
            $type = 'success';
        }
        $string = '<div class="alert alert-' . $type . ' alert-dismissible fade in flash" id="' . $type . 'Alert" role="alert">';
        $string .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $string .= '<div id="' . $type . 'AlertMessage">' . $message . '</div></div>';
        return $string;
    }

}
