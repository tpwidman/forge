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
class Google
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
     * Use GoogleMaps API to determine Lon/Lat - this may require a license depending on how you are using the application.
     *                
     * @param  string $address Address Line 1
     * @param  string $city    [City]
     * @param  string $state   [State]
     * @param  string $zip     [Zip Code]
     * @param  string $country [Country]
     * 
     * @return object
     */
    public function getAddressLonLat($address = '', $city = '', $state = '', $zip = '', $country = 'US')
    {

        $return = array('lon' => '', 'lat' => '');
        $input = array();
        !empty($address) ? $input[] = urlencode($address) : false;
        !empty($city) ? $input[] = urlencode($city) : false;
        !empty($state) ? $input[] = urlencode($state) : false;
        !empty($zip) ? $input[] = urlencode($zip) : false;
        !empty($country) ? $input[] = urlencode($country) : false;
        $map = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . implode(',+', $input) . '&sensor=true');

        $array = json_decode($map, true);
        if ($array['status'] != 'ZERO_RESULTS') {
            $return['lat'] = $array['results'][0]['geometry']['location']['lat'];
            $return['lon'] = $array['results'][0]['geometry']['location']['lng'];
        }
        return (object) $return;
    }

    /**
     * Use GoogleMaps API to determine Lon/Lat - this may require a license depending on how you are using the application.
     *                
     * @param  string $address [the string address we are looking for - it can accept parital]
     * @return object
     */
    public function getMapInformation($address = '')
    {
        $return = array();
        $map = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=true');
        $array = json_decode($map, true);
        $return['lat'] = $array['results'][0]['geometry']['location']['lat'];
        $return['lon'] = $array['results'][0]['geometry']['location']['lng'];
        foreach ($array['results'][0]['address_components'] as $k => $arr) {
            $return[$arr['types'][0]] = $arr['long_name'];
        }      
        $return['county'] = trim(str_replace('County', '', $return['administrative_area_level_2']));
        $return['state'] = $return['administrative_area_level_1'];

        return (object) $return;
    } 


    /**
     * return an HTML image call to generate the 
     * @param string $url [
     * - url: http://www.foonster.com
     * - email address: mailto:wherever@example.com
     * - MECARD:N:Owen,Sean;ADR:76 9th Avenue, 4th Floor, New York, NY 10011;TEL:+12125551212;EMAIL:srowen@example.com;
     * - sms:+15105550101?body=hello%20there
     * - geo:40.71872,-73.98905,100]
     * 
     * @param integer $size [the image size, note this is a square]
     * @param integer $ec_level [the error correction level to use when rendering the image.]
     * @param integer $margin [the margin to assign to the image.]
     * 
     * 
     * @return string [valid html img string]
     */ 
    public static function qrCode($url, $altText = 'QR Code', $size = 250, $ec_level = 1, $margin = 0)
    {
        $url = urlencode($url);
        return  '<img src="http://chart.apis.google.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=' . $ec_level.'|'.$margin . '&chl=' . $url . '" alt="' . $altText . '" widhtHeight="' . $size . '" widhtHeight="' . $size . '"/>';
    }
}
