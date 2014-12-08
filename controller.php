<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class LatLongUpdaterPackage extends Package {

	protected $pkgHandle = 'lat_long_updater';
	protected $appVersionRequired = '5.6.2.1';
	protected $pkgVersion = '0.9.1';
	
	public function getPackageDescription() {
		return t("Automatically retrieves Latitude and Longitude values for records that have addresses");
	}
	
	public function getPackageName() {
		return t("Lat/Long Updater");
	}
	
	public function getPackageHandle() {
		return $this->pkgHandle;
	}
     
    public function on_start() {
        Events::extend('on_page_version_approve', 'LatLongUpdaterPackage', 'doUpdate',  __FILE__);
    }

    public function doUpdate($page) {
        if ($page->getAttribute('manual_lat_long')) {
            return true;
        }

        $address = $page->getAttribute('street_address') . ' ' . $page->getAttribute('city') .  ' '. $page->getAttribute('state') . ' '. $page->getAttribute('zip_code');

        if (!$address) {
            return true;
        }

        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";

        $request = $url . urlencode($address);
        $response = LatLongUpdaterPackage::remoteCall($request);
        $response = json_decode($response, true);

        if($response['status'] == 'OK'){
            $lat = $response["results"][0]["geometry"]["location"]["lat"];
            $long = $response["results"][0]["geometry"]["location"]["lng"];

            $page->setAttribute('lat', $lat);
            $page->setAttribute('long', $long);
        }else{
            return false;
        }
	}

	public function remoteCall($url) {
		if (!$url) {
			return false;	
		}  
		  
		$curl = curl_init();
		$opts = array();
		$opts[CURLOPT_URL] = $url;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		$opts[CURLOPT_CONNECTTIMEOUT] = 10;
		$opts[CURLOPT_TIMEOUT] = 20;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		
		curl_setopt_array($curl, $opts);
		$rbody = curl_exec($curl);

		curl_close($curl);	  
 	 	return $rbody;
	}

	public function install() {
		$pkg = parent::install();
	}
	 
	public function uninstall() {
		parent::uninstall();
	}
}
