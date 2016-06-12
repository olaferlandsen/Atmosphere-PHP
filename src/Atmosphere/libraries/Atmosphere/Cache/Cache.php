<?php
namespace Cuinzy\Cache
{
    class Cache
    {
    	public static $engine;
    	public static $engineObject ;
    	public static $prefix;
    	public static $ttl;
    	public static $enable;
    	
    	public static function __callStatic( $method , $args )
    	{
    	 	if (method_exists( self::$engineObject , $method)) {
    	 	    if (!empty(self::$prefix)) {
    	 	        call_user_func_array(
    	 	            array(self::$engineObject,"setPrefix"),
    	 	            array(self::$prefix)
    	 	        );
    	 	    }
    	 		return call_user_func_array(
    	 		    array(
    	 		        self::$engineObject,
    	 		        $method
    	 		    ),
    	 		    $args
    	 		);
    	 	} else {
    	 		trigger_error("Cache method '$method' does not exists.",E_USER_ERROR);
    	 	}
    	}
    	
    	public static function setPrefix($prefix)
    	{
    	    if (is_string($prefix)) {
    	        self::$prefix = $prefix;
    	        return true;
    	    }
    	    return false;
    	}
    	public static function setDefaultTimeToLive($prefix)
    	{
    	    if (is_integer($ttl)) {
    	        self::$ttl = $ttl;
    	        return true;
    	    }
    	    return false;
    	}
    	public static function engine( $engine = null , $prefix = null)
    	{
    	    self::setPrefix($prefix);
    		if (!empty($engine)) {
				$engine = "Cuinzy\\Cache\\Engines\\".ucfirst(strtolower($engine));
				self::$engineObject = new $engine();
				return self::$engineObject;
    		}else{
    			trigger_error("Cache $engine is empty.",E_USER_ERROR);
    		}
    		return false;
    	}
    }
}