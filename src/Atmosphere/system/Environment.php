<?php
namespace Atmosphere
{
    class Environment
    {
    	const REGEX_REFERENCE	= "/\&\#\:(?<key>[^\;]+)(\;)?/i";
    	const REGEX_FUNCTION	= "#^\&fn\:((?<class>[a-z0-9\_\\\]+)\:{1,2})?(?<func>[a-z0-9\_]+)(\((?<params>.*)\))?#i";
    	const REGEX_CONST       = "/^\&const\:(?<name>[a-z0-9_]+)/i";
    	/**
    	*
    	*/
    	protected static $config = array();
    	
    	/**
    	*
    	*/
    	public static function set( $key , $value = null )
    	{
    	    if (empty($key)) {
    	        return false;
    	    }
    	    // Added & to $source here
    	    if (!function_exists("\Atmosphere\_AtmosphereEnvironmentSetRv")) {
                    function _AtmosphereEnvironmentSetRv (&$source, $array_keys, $value) {
                        if (count($array_keys) == 1) {
                            $source[$array_keys[0]] = $value;
                        } else {
                            // No need for return statement
                            \Atmosphere\_AtmosphereEnvironmentSetRv(
                                $source[$array_keys[0]],
                                array_slice($array_keys, 1),
                                $value
                            );
                        }
                    }
            }
    	    if (array_key_exists($key, self::$config)) {
    	        self::$config[$key] = $value;
    	    } elseif (self::exists($key)) { // recursive
    	        if (strpos($key, ".")) {
                    $keys = explode(".", $key);
                    if (count($keys) > 0) {
                        if (array_key_exists($keys[0], self::$config)) {
                            

                            // No assignment
                            _AtmosphereEnvironmentSetRv(self::$config, $keys, $value);
                        } else {echo "[".__LINE__."]";}
                    } else {echo "[".__LINE__."]";}
                } else {echo "[".__LINE__."]";}
    	    } else { // si no existe en ningun caso, crea la llave y con el valor
    	        self::$config[$key] = $value;
    	    }
    		return false;
    	}
    	/**
    	*
    	*/
    	public static function seekReferences( $source = true )
    	{
    		if( is_string( $source ) )
    		{
    			$matches = array();
    			if( preg_match( self::REGEX_REFERENCE ,$source ,$matches) ) {
    				if( self::exists( $matches["key"] ) ) {
    					return preg_replace( self::REGEX_REFERENCE , self::get( $matches["key"] ) , $source );
    				}
    			} elseif ( preg_match( self::REGEX_FUNCTION ,$source ,$matches) ) {
    				if (!empty( $matches["params"])) {
    					$params = preg_split("#(?<!\\\),#" , $matches["params"]);
    					if (count( $matches["params"] ) > 0) {
    						$matches["params"] = $params;
    					} else {
    						$matches["params"] = array($matches["params"]);
    					}
    				} else {
    					$matches["params"] = array();
    				}
    				if (!empty( $matches["func"] ) AND empty($matches["class"])) {
    					if (function_exists($matches["func"])) {
    					    
    					    $MethodChecker = new ReflectionMethod($matches["class"],$matches["func"]);
    					    if(!$MethodChecker->isStatic()){
    					        return call_user_func_array(
    					            array(
        					            $matches["class"],
        					            $matches["func"]
    					            ),
    					            $matches["params"]
    					        );
    					    }
    					    
    						return call_user_func_array( $matches["func"] , $matches["params"] );
    					} else {
    						trigger_error("Call to undefined function ". $matches["func"] . " on environment json",E_USER_ERROR);
    					}
    				} elseif (!empty( $matches["func"] ) AND !empty($matches["class"])) {
    					if (class_exists( $matches["class"])) {
    						if (method_exists( $matches["class"] , $matches["func"])) {
    						    
    							return call_user_func_array(
    							    array(
        							    $matches["class"],
        							    $matches["func"]
    							    ),
    							    $matches["params"]
    							);
    							
    						} else {
    							trigger_error("Call to undefined method ". $matches["class"]."::".$matches["func"]." on environment json",E_USER_ERROR);
    						}
    					} else {
    						trigger_error("Class ". $matches["class"]." not found on environment json",E_USER_ERROR);
    					}
    				}
    			} elseif (preg_match(self::REGEX_CONST, $source, $matches)) {
    			    if (!empty($matches["name"]) AND defined($matches["name"])) {
    			        return constant($matches["name"]);
    			    }
    			}else{
    				return $source;
    			}
    		}else if( is_array( $source ) )
    		{
    			return array_map( array( get_class() ,"seekReferences" ) , $source );
    		}
    		return $source;
    	}
    	/**
    	*
    	*/
    	public static function _unset( $key )
    	{
    		if( array_key_exists( $key , self::$config ) )
    		{
    			unset( self::$config[ $key ] );
    		}
    	}
    	/**
    	*
    	*/
    	public static function get( $key , $default = null )
    	{
    	    $currentIndex = self::$config;
    	    $count = 0;
    		if (strpos($key, '.')) {
    		    $keys = explode('.', $key);
    			foreach ($keys AS $key) {
    			    if (array_key_exists($key, $currentIndex)) {
    			        $currentIndex = $currentIndex[$key];
    			    }
    			}
    			return  self::seekReferences( $currentIndex );
    		} elseif (array_key_exists($key, self::$config)) {
    			return self::seekReferences(self::$config[ $key ]);
    		}
    		
    		return $default;
    		
    	}
    	/**
    	*
    	*/
    	public static function multiple( )
    	{
    		$elements = array();
    		if( func_num_args() > 0 )
    		{
    			array_map(function( $element ) use ( &$elements )
    			{
    				if( Environment::exists( $element ) )
    				{
    					$elements[] = Environment::get( $element );
    				}
    			},func_get_args());
    		}
    		return $elements;
    	}
    	/**
    	*
    	*/
    	public static function exists( $key )
    	{
    		if( strpos( $key , '.' ) )
    		{
    			$keys = explode( '.' , $key );
    			$currentIndex = self::$config;
    			$count = 0;
    			foreach( $keys AS $index )
    			{
    				if( is_array( $currentIndex ) AND array_key_exists( $index , $currentIndex ) )
    				{
    					$count++;
    					$currentIndex = $currentIndex[ $index ];
    				}
    			}
    			if( $count <= 1 )
    			{
    				return false;
    			}
    			return true;
    		}
    		return array_key_exists( $key , self::$config );
    	}
    	/**
    	*
    	*/
    	public static function fetch($startFrom = null,$parent = null)
    	{
    	    if (empty($startFrom)) {
    	        $env = self::$config;
    	    } else {
    	        $env = $startFrom;
    	    }
    	    foreach ($env AS $index => &$itm ) {
    	        //echo "[$parent.$index]";
    	        if(is_string($itm)){
    	            $itm = Environment::get($itm);
    	        } elseif (is_array($itm)) {
    	            foreach ($itm AS $k => &$v ) {
    	                if (is_string($v)) {
    	                    $v = Environment::get($v, $v);
    	                }
    	            }
    	            //$itm = Environment::fetch($itm,$index);
    	        }
    	    }
    	    return $env;
    	}
    	/**
    	*
    	*/
    	public static function config()
    	{
    	    return self::$config;
    	}
    	/**
    	*
    	*/
    	public static function register()
    	{
    		if (function_exists ('class_alias')) {
    		    if (!class_exists('\Atmosphere\Env')) {
    			    class_alias( '\Atmosphere\Environment' , '\Atmosphere\Env' ) ;
    		    }
    		    if (!class_exists('Atmosphere\Env')) {
    			    class_alias( '\Atmosphere\Environment' , 'Atmosphere\Env' ) ;
    		    }
    		    if (!class_exists('\Env')) {
    			    class_alias( '\Atmosphere\Environment' , '\Env' ) ;
    		    }
    		    if (!class_exists('Env')) {
    			    class_alias( '\Atmosphere\Environment' , 'Env' ) ;
    		    }
    		}
    	}
    }
}