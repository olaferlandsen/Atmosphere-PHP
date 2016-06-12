<?php
namespace Atmosphere
{
    class Routes
    {
    	private static $routes = array();
    	
    	public static function set( $key , $regexp , $controller )
    	{
    		if( array_key_exists( $key , self::$routes ) )
    		{
    			return self::replace( $key , $regexp , $controller );
    		}else{
    			return self::add( $key , $regexp , $controller );
    		}
    	}
    	public static function add( $key , $regexp , $controller )
    	{
    		if( !array_key_exists( $key , self::$routes ) )
    		{
    			//$controller = str_replace( '\\' ,'/' , $controller );
    			//$controller = preg_replace( '/(\/+)/'  , '/'  , $controller );
    			self::$routes[ $key ] = array( 'regexp'	=>	$regexp , 'controller' => $controller);
    			return true;
    		}
    	}
    	public static function get( $key  )
    	{
    		if( array_key_exists( $key , self::$routes ) )
    		{
    			return self::$routes[ $key ];
    		}
    	}
    	public static function replace( $key , $regexp , $controller )
    	{
    		if( array_key_exists( $regexp , self::$routes ) )
    		{
    			$controller = str_replace( '\\' ,'/' , $controller );
    			$controller = preg_replace( '/(\/+)/'  , '/'  , $controller );
    			self::$routes[ $key ] = array( 'regexp'	=>	$regexp , 'controller' => $controller);
    			return true;
    		}
    	}
    	public static function delete( $key )
    	{
    		if( array_key_exists( $key , self::$routes ) )
    		{
    			unset( self::$routes[ $key ] );
    		}
    	}
    	public static function exists( $key )
    	{
    		return array_key_exists( $key , self::$routes );
    	}
    	public static function fetch()
    	{
    	 	return self::$routes;
    	}
    	
    	public static function yaml( file $yaml )
    	{
    		if( function_exists('yaml_parse_file') )
    		{
    			$yaml = yaml_parse_file( $yaml );
    		}else if( Modules::exists( 'Spyc' ) )
    		{
    			$yaml = Spyc::YAMLLoad( $yaml );
    		}else{
    			return false;
    		}
    		foreach( $yaml AS $regexp => $controller )
    		{
    			self::set( sha1( $regexp ) , $regexp , $controller );
    		}
    		return true;
    	}
    }
}