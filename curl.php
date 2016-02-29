<?php

class Curl {

	public $username;
	public $password;
	
	public $url;
	
	public $rawhtml;
	public $encoded;
	
	public function __construct() {
	
				
	
	}
	
	public function stringExists($str) {
	
		return strstr($this->rawhtml, $str) !== false;
		
	}	

	public function run($html = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_COOKIESESSION, false);
		curl_setopt($ch, CURLOPT_POST, 3);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "inputUserName=" . $this->username . "&inputPassword=" . $this->password . "&nothankyou=1");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: en-US,en;q=0.5"));
		$data = curl_exec($ch);

		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//return $data;
		//return ($httpcode>=200 && $httpcode<300) ? $data : false;
		if ($html) {
			$this->encoded = nl2br(htmlspecialchars($data));
		} else {
			$this->rawhtml = $data;
		}
	}

}
