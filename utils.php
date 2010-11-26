<?php

class Utils {
	
	var $xml_uri = null;
	var $xml = null; // SimpleXMLElement object
	
	function __construct() {
		$this->xml_uri = $this->parse_uri();
		$this->xml = $this->xmlstring2simplexmlobject();
	}
	
	// calculate the xml uri 
	function parse_uri() {
		$xml_uri = null;
		try {
			// try POST/GET
			$params = array_merge($_GET, $_POST);
			if($params["uri"]!="") {
				$uri = urldecode($params["uri"]);
				$full_uri = preg_replace("/[\/]*$/i", "", $uri);
				$uri = substr($full_uri, 0, strrpos($full_uri, "/"));
				$page = substr($full_uri, strrpos($full_uri, "/")+1);
			}
			// otherwise try referer
			else if(isset($_SERVER["HTTP_REFERER"])){
				$http_referer = $_SERVER["HTTP_REFERER"];
				$http_referer = preg_replace("/[\/]*$/i", "", $http_referer);
				$uri = substr($http_referer, 0, strrpos($http_referer, "/"));
				$page = substr($http_referer, strrpos($http_referer, "/")+1);
			}
			else throw new ParameterPassingException("Keine oder fehlerhafte Parameterübergabe, kein Referer");
			
			$uri = preg_replace("/[\/]*$/i", "", $uri);
			//echo "referer: ".$_SERVER["HTTP_REFERER"]."<br />host: ".$_SERVER["HTTP_HOST"]."<br />uri: ".$uri."<br />page: ".$page;
			$xml_uri = $uri . "/Special:Export/" . $page . "?history=1";
		} catch(ParameterPassingException $e) {
			die($e);
		}
		return $xml_uri;
	}
	function xmlstring2simplexmlobject() {
		$xml_object = null;
		try {
			if(!$this->url_exists($this->xml_uri)) throw new IOException("Konnte die angegebene XML-Datei <strong>$this->xml_uri</strong> nicht finden");
			$xml_object = simplexml_load_file($this->xml_uri);
		} catch(IOException $e) {
			die($e);
		}
		return $xml_object;
	}
	function get_xml_object() {
		return $this->xml;
	}
	function get_time_diff($timestamp, $timestamp_to=null) {
		// elapsed time in days
		if($timestamp_to == null)
			return number_format((time()-strtotime($timestamp))/86400, 1) . " Tage";
		return number_format((strtotime($timestamp_to)-strtotime($timestamp))/86400, 1);
	}
	function get_xml_uri() {
		return $this->xml_uri;
	}
	// taken from http://php.net/manual/en/function.file-exists.php
	function url_exists($url) {
	    // Version 4.x supported
	    $handle   = curl_init($url);
	    if (false === $handle)
	    {
	        return false;
	    }
	    curl_setopt($handle, CURLOPT_HEADER, false);
	    curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
	    curl_setopt($handle, CURLOPT_NOBODY, true);
	    curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
	    $connectable = curl_exec($handle);
	    curl_close($handle);   
	    return $connectable;
	}
}

?>