<?php

namespace Atmosphere\Utilities{
    class Utilities
    {
    	public static function isMultidimentialArray (array $array = array())
    	{
    		foreach ($array AS $value) {
    			if(is_array($value)){
    			    return true;
    			}
    		}
    		return false;
    	}
    	public static function isMultibyteString( $string )
    	{
    		if (mb_strlen($string, 'UTF-8') < strlen($string)) {
    			return true;
    		}
    		return false;
    	}
    	public static function isAscii($string)
    	{
    		return !preg_match('/[^\x00-\x7F]/S', $string);
    	}
    	public static function httpResponseCode( $code = null )
    	{
    		if (!function_exists("http_response_code")) {
        		function http_response_code ($code = null) {
        		    if(empty($code)){
        		        return false;
        		    }
        			// else 
        			switch ($code) {
        				case 100: $text = 'Continue'; break;
        				case 101: $text = 'Switching Protocols'; break;
        				case 200: $text = 'OK'; break;
        				case 201: $text = 'Created'; break;
        				case 202: $text = 'Accepted'; break;
        				case 203: $text = 'Non-Authoritative Information'; break;
        				case 204: $text = 'No Content'; break;
        				case 205: $text = 'Reset Content'; break;
        				case 206: $text = 'Partial Content'; break;
        				case 300: $text = 'Multiple Choices'; break;
        				case 301: $text = 'Moved Permanently'; break;
        				case 302: $text = 'Moved Temporarily'; break;
        				case 303: $text = 'See Other'; break;
        				case 304: $text = 'Not Modified'; break;
        				case 305: $text = 'Use Proxy'; break;
        				case 400: $text = 'Bad Request'; break;
        				case 401: $text = 'Unauthorized'; break;
        				case 402: $text = 'Payment Required'; break;
        				case 403: $text = 'Forbidden'; break;
        				case 404: $text = 'Not Found'; break;
        				case 405: $text = 'Method Not Allowed'; break;
        				case 406: $text = 'Not Acceptable'; break;
        				case 407: $text = 'Proxy Authentication Required'; break;
        				case 408: $text = 'Request Time-out'; break;
        				case 409: $text = 'Conflict'; break;
        				case 410: $text = 'Gone'; break;
        				case 411: $text = 'Length Required'; break;
        				case 412: $text = 'Precondition Failed'; break;
        				case 413: $text = 'Request Entity Too Large'; break;
        				case 414: $text = 'Request-URI Too Large'; break;
        				case 415: $text = 'Unsupported Media Type'; break;
        				case 500: $text = 'Internal Server Error'; break;
        				case 501: $text = 'Not Implemented'; break;
        				case 502: $text = 'Bad Gateway'; break;
        				case 503: $text = 'Service Unavailable'; break;
        				case 504: $text = 'Gateway Time-out'; break;
        				case 505: $text = 'HTTP Version not supported'; break;
        				default:
        					trigger_error('Unknown http status code "' . htmlentities($code) . '"' , E_USER_ERROR );
        				break;
        			}
        			$protocol = "HTTP/1.0";
        			if (array_key_exists( "SERVER_PROTOCOL", $_SERVER) AND !empty($_SERVER["SERVER_PROTOCOL"])) {
        				$protocol = $_SERVER['SERVER_PROTOCOL'];
        			}
        			header("$protocol $code $text", true, $code);
        			$GLOBALS['http_response_code'] = $code;
        		}
        	}
        	$GLOBALS['http_response_code'] = $code;
        	return http_response_code( $code );
    	}
    	public static function uuid( $separator = "-" )
    	{
    	    return sprintf( "%04x%04x$separator%04x$separator%04x$separator%04x$separator%04x%04x%04x",
    	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    	        mt_rand( 0, 0xffff ),
    	        mt_rand( 0, 0x0fff ) | 0x4000,
    	        mt_rand( 0, 0x3fff ) | 0x8000,
    	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    	    );
    	}
    }
}