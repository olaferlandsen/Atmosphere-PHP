<?php
/**
*
*/

namespace Atmosphere {
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Environment.php');
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Rewrite.php');
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Controller.php');
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Libraries.php');
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Model.php');
    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Typehint.php');
    
    class Atmosphere
    {
        /**
    	*
    	*/
    	private static $workspace;
    	/**
    	*
    	*/
    	private $application;
    	/**
    	*
    	*/
    	private $segments;
    	/**
    	*
    	*/
    	private $segmentId = 0;
    	/**
    	*
    	*/
    	private $errorHandler = array();
    	private $shutdownHandler = array();
    	/**
    	*
    	*/
    	private function setErrorHandler ($handler)
    	{
    	    $this->setErrorHandler[] = $handler;
    	}
    	private function setShutdownHandler ($handler)
    	{
    	    $this->shutdownHandler[] = $handler;
    	}
    	
    	public function errorHandler ()
    	{
    	    $args = func_get_args();
    	    foreach ($this->setErrorHandler AS $handler) {
    	        call_user_func_array($handler, $args);
    	    }
    	}
    	public function shutdownHandler ()
    	{
    	    array_map('register_shutdown_function', $this->shutdownHandler);
    	}
    	
    	public function __construct($workspace = false)
    	{
    	    if (defined('ATMOSPHERE_ROOT')) {
    	        header('Secure-Workspace:' . basename(ATMOSPHERE_ROOT));
    	    }
    	    
    	    
    	    if(!empty($workspace) and is_string($workspace)){
    	        $this->setWorkspace($workspace);
    	    }
    		$this->basedir	= $this->workspace();
    		$workspace        = $this->workspace();
    		$this->segments =	Rewrite::segments();
    		$this->_segments =	Rewrite::segments();
    		$_GET			=	Rewrite::getMethod();
    		\Atmosphere\Environment::register();
    		$this->environment();
    		
    		
    		
    		$this->setErrorHandler(array('\\Atmosphere\Typehint\Typehint', 'handleTypehint'));
    		
    		
    		if (\Atmosphere\Environment::get('core.enabled.debug', true) === true) {
    		    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Debug.php');
    		}
    		
    		if (\Atmosphere\Environment::get('core.enabled.routes', true) === true) {
    		    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Routes.php');
    		}
    		
    		
    		if (\Atmosphere\Environment::get('core.enabled.template', true) === true) {
    		    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Template.php');
    		}
    		
    		if (\Atmosphere\Environment::get('core.enabled.config', true) === true) {
    		    require_once ( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ControllerConfig.php');
    		}
    		
    		
    		if (class_exists('\Atmosphere\Debug')) {
    		    \Atmosphere\Debug::register();
        		if (\Atmosphere\Environment::get('debug.enable', false) === true) {
        	        \Atmosphere\Debug::enable();
        	        \Atmosphere\Debug::setLinkToCss(Environment::get('debug.linkToCss'));
        	        \Atmosphere\Debug::setLinkToJs(Environment::get('debug.linkToJs'));
        	        $this->setErrorHandler(array('\\Atmosphere\Debug', 'errorHandler'));
    	            $this->setShutdownHandler(array('\\Atmosphere\Debug', 'display'));
        	    } else {
        	        \Atmosphere\Debug::disable();
        	    }
        	}
    	    
    	    
    	    set_error_handler(array($this, 'errorHandler'));
    	    register_shutdown_function(array($this, 'shutdownHandler'));
    		$this->application	=	array(
    		    'fantasySegments'   =>  $this->segments,
    		    'segments'  =>  array(),
    		    'name'      =>  Environment::get('defaults.applications.folder'),
    			'path'		=>	Environment::get('filesystem.applications').DIRECTORY_SEPARATOR,
    			'fantasyName'=> null,
    			'urlPath'   =>  join('/', $this->segments),
    			'controller'=>	array(
    				'file'		=>	Environment::get("defaults.controllers.controller.file"),
    				'class'		=>	Environment::get("defaults.controllers.controller.class"),
    				'method'	=>	array(
    				    'originalName'      =>  null,
    				    'originalExists'    =>  false,
    					'static'	        =>	false,
    					'name'		        =>	Environment::get("defaults.controllers.methods.default",'index'),
    					'isCallable'        =>  false,
    					'args'		        =>	array(),
    					'numArgs'	        =>	0,
    					'fillRemainingArgs' =>	Environment::get('defaults.controllers.methods.fillRemainingArgs', false)
    				)
    			)
    		);
    		
    		function autoload ($className) {
    		    $workspace = \Atmosphere\Env::get('filesystem.libraries');
                $className = ltrim($className, '\\');
                $fileName  = '';
                $namespace = '';
                if ($lastNsPos = strrpos($className, '\\')) {
                    $namespace = substr($className, 0, $lastNsPos);
                    $className = substr($className, $lastNsPos + 1);
                    $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                }
                $ifNotWork = $fileName. str_replace('_', DIRECTORY_SEPARATOR, $className) . DIRECTORY_SEPARATOR . $className. '.php';
                $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className). '.php';
                
                
                $fileName = $workspace.$fileName;
                $ifNotWork = $workspace.$ifNotWork;
                if (file_exists($fileName) AND is_file($fileName)) {
                    include_once($fileName);
                } elseif (file_exists($ifNotWork) AND is_file($ifNotWork)) {
                    include_once($ifNotWork);
                }
                
            }
            
    		if (function_exists('spl_autoload_register')) {
    		    spl_autoload_register('\Atmosphere\autoload');
    		} else {
    		    function __autoload($className) {
    		        \Atmosphere\autoload($className);
    		    }
    		}
    		// routes
    		if (class_exists('\Atmosphere\Routes') AND Environment::exists('routes')) {
    		    foreach (Environment::get('routes') AS $route) {
    		        if (array_key_exists('regexp', $route) and  preg_match($route['regexp'], Rewrite::segmentsToString())) {
    		            if (array_key_exists('call', $route)) {
        		            $this->segments =	Rewrite::segments(preg_replace(
        	                    $route['regexp'],
        	                    $route['call'],
        	                    Rewrite::segmentsToString()
        	                ));
    	                }
    	                break;
    		        } elseif( array_key_exists("disableUrl", $route) and !empty($route["disableUrl"]) ) {
    		            $matches = array();
    		            if (preg_match($route["disableUrl"], Rewrite::segmentsToString(), $matches)) {
    		                $this->segments = array();
    		                
    		                // redirection, use param "redirectTo"
    		                if (array_key_exists("redirectTo", $route) and !empty($route["redirectTo"]) ) {
    		                    $redirectTo = preg_replace(
            	                    $route["disableUrl"],
            	                    $route["redirectTo"],
            	                    Rewrite::segmentsToString()
            	                );
    		                    if (array_key_exists("redirectCode" ,$route)) {
    		                        header(
    		                            "Location:".$redirectTo,
    		                            true,
    		                            (int)$route["redirectCode"]
    		                        );
    		                    } else {
    		                        header("Location:".$redirectTo);
    		                    }
    		                    exit;
    		                }
    		            }
    		        }
    		    }
    		}
    	}
    	
    	
    	/**
    	*
    	*/
    	public function init()
    	{
    	    
    	    $this->application['segments'] = $this->segments;
    	    
    		/**
    		*	1.: Application
    		*/
    		Environment::set('application' , $this->application);
    		if (array_key_exists($this->segmentId, $this->segments)) {
    			$path = $this->application["path"] . $this->segments[$this->segmentId];
    			if (file_exists($path) AND is_dir($path)) {
    				$this->application["path"] .= $this->segments[$this->segmentId].DIRECTORY_SEPARATOR;
    				$this->application['name'] = $this->segments[$this->segmentId];
    				$this->application['fantasyName'] = $this->_segments[$this->segmentId];
    				if (!is_dir($this->application["path"])) {
    					trigger_error("No such file or directory classes",E_USER_ERROR);
    				}
    				$this->segmentId++;
    			} else {
    				$this->application["path"] .= Environment::get("defaults.applications.folder").DIRECTORY_SEPARATOR;
    			}
    		}else{
    			$this->application["path"] .= Environment::get("defaults.applications.folder").DIRECTORY_SEPARATOR;
    		}
    		
    		/**
    		*	2.: Controller
    		*/
    		Environment::set('application', $this->application);
    		if (array_key_exists($this->segmentId, $this->segments)) {
    			$file = $this->application["path"] . strtolower( $this->segments[ $this->segmentId ] ) . ".php";
    			if (file_exists( $file ) AND is_file($file)) {
    				$this->application['controller']["file"] = strtolower( $this->segments[ $this->segmentId ] ) . ".php";
    				$this->application['controller']['class'] = '\Atmosphere\Controller\\'.$this->segments[ $this->segmentId ];
    				
    				$this->segmentId++;
    			} else {
    				$this->application['controller']["file"] = Environment::get("defaults.controllers.controller.file");
    				$this->application['controller']['class'] = Environment::get("defaults.controllers.controller.class");
    			}
    		} else {
    			$this->application['controller']["file"] = Environment::get("defaults.controllers.controller.file");
    			$this->application['controller']['class'] = Environment::get("defaults.controllers.controller.class");
    		}
    		
    		/**
    		*	3.: Method
    		*/
    		Environment::set('application' , $this->application);
    		if (array_key_exists( $this->segmentId , $this->segments )) {
    			$isInclude = include_once( $this->application["path"] . $this->application['controller']["file"] );
    			
    			if (class_exists($this->application['controller']['class'])) {
    				if (is_subclass_of($this->application['controller']['class'], '\Atmosphere\Controller\Controller' )) {
    					
    					$this->application['controller']['method']['originalName'] = str_replace(
    					    '-',
    					    '_',
    					    $this->segments[ $this->segmentId ]
    					);
    					
    					if (
    					    method_exists($this->application['controller']['class'],  $this->application['controller']['method']['originalName'])
    					    AND !method_exists('\Atmosphere\Controller\Controller', $this->application['controller']['method']['originalName'])
    					) {
    					    $this->application['controller']['method']['originalExists'] = true;
    						if (
    						    is_callable(array(
    						            $this->application['controller']['class'],
    						            $this->application['controller']['method']['originalName']
    						    ), true)
    						) {
    						    $this->application['controller']['method']['isCallable'] = true;
    							$this->application['controller']['method']['name'] = $this->application['controller']['method']['originalName'];
    							$this->segmentId++;
    						}
    					} else {
    					    $this->application['controller']['method']['isCallable']= is_callable(array(
    					            $this->application['controller']['class'],
    					            $this->application['controller']['method']['name']
    					    ), true);
    					}
    				} else {
    				    trigger_error(
    				        "Should extend its controller \"".$this->application[ 'controller' ]['class']."\" to the class \"\Atmosphere\Controller\Controller\"",
    				        E_USER_ERROR
    				    );
    				}
    			} elseif (!class_exists($this->application['controller']['class']) and $isInclude === true) {
    			    trigger_error("Class file is included, but the class ".$this->application['controller']['class']." does not exists in file",E_USER_ERROR);
    			} else {
    				trigger_error("Class ".$this->application['controller']['class']." does not exists",E_USER_ERROR);
    			}
    		}
    		
    		/**
    		* 4.: Args
    		*/
    		Environment::set('application' , $this->application);
    		if (array_key_exists($this->segmentId, $this->segments)) {
    			$this->application['controller']['method'][ "args" ] = array_slice( $this->segments, $this->segmentId );
    		}
    		
    		/**
    		* 5.: re-include class
    		*/
    		Environment::set('application' , $this->application);
    		if( !class_exists( $this->application['controller']['class'] ) ) {
    		    if (file_exists($this->application["path"] . $this->application['controller']["file"])) {
    			    include_once( $this->application["path"] . $this->application['controller']["file"] );
    		    } else {
    		        trigger_error('Controller file "'.$this->application["path"].$this->application['controller']["file"].'" does not exists', E_USER_ERROR);
    		    }
    		}
    		
    		/**
    		* 6.: Fill remaining args
    		*/
    		Environment::set('application' , $this->application);
    		if (method_exists(
    		    $this->application['controller']['class'],
    		    $this->application['controller']['method']['name']
    		)) {
        		$ReflectionMethod = new \ReflectionMethod(
        		    $this->application['controller']['class'],
        		    $this->application['controller']['method']['name']
        		);
        		$this->application['controller']['method']['numArgs'] = $ReflectionMethod->getNumberOfRequiredParameters();
        		$this->application['controller']['method']['static'] = $ReflectionMethod->isStatic();
        		if (count( $this->application['controller']['method']["args"] ) < $this->application['controller']['method']['numArgs']) {
        			if ($this->application['controller']['method']["fillRemainingArgs"] === true) {
        				$fill = $this->application['controller']['method']['numArgs'] - count( $this->application['controller']['method']["args"] );
        				$this->application['controller']['method']["args"] += array_fill( count($this->application['controller']['method']["args"]) , $fill, null );
        			}
        		}
    	    } else {
    	        trigger_error('Method "'.$this->application['controller']['class'].'->'.$this->application['controller']['method']['name'].'(...)" does not exist in "'.$this->application["path"].$this->application['controller']["file"].'"', E_USER_ERROR);
    	    }
    	    
    	    /**
    		* 7.: Load controllers config classes
    		*/
    		array_map(function( $file ) {
    		    if (!empty($file) and is_string($file)) {
        			if (file_exists( $file ) AND is_file( $file )) {
        				include_once( $file );
        				$class= preg_replace('/([a-z0-9\-]+)(\.[a-z0-9]+)/i', '$1', '\Atmosphere\Controller\\'.basename($file))."ControllerConfig";
        				if (class_exists($class)) {
        				    if (is_subclass_of($class, '\Atmosphere\Controller\Controller')) {
        				        $called = new $class($this->application);
        				        if (method_exists($called, '_config')) {
        				            $called->_config($this->application);
        				        }
        				        if (method_exists($called, Env::get('defaults.controllers.methods.default'))) {
        				            $called->_config($this->application);
        				        }        				    } else {
        				        trigger_error("Controller config not is Controller subclass", E_USER_ERROR);
        				    }
        				} else {
        				    trigger_error("$class controller config not exists", E_USER_ERROR);
        				}
        			} else {
        				trigger_error("Controller configuration file $file does not found.", E_USER_ERROR);
        			}
    		    }
    		}, Environment::get('core.controllerConf', []));
    	    
    		/**
    		* 8.: Execute user method
    		*/
    		
    		
    			
    			
    			
    			
    			
    		Environment::set('application' , $this->application);
    		if( $this->application['controller']['method']['static'] === true ) {
    			call_user_func_array(array(
    				$this->application['controller']['class'],
    				$this->application['controller']['method']['name']
    			), $this->application['controller']['method']['args']);
    		} else {
    			$controllerObject = $this->application['controller']['class'];
    			$controllerObject = new $controllerObject();
    			$controllerObject->controller = $this->application['controller'];
    			$controllerObject->controller["path"] = $this->application["path"] . $controllerObject->controller["file"];
    			
    			
    			/**
    			* 7.: Execute config method if exists
    			*/
    			
    			
    			
			
			
    			if( method_exists( $controllerObject , Environment::get('defaults.controllers.methods.config') ) ) {
    				if( is_callable( array($controllerObject , Environment::get('defaults.controllers.methods.config') ) ) ) {
    				    Environment::set('application' , $this->application);
    					call_user_func_array(
    						array($controllerObject, Environment::get('defaults.controllers.methods.config')),
    						$this->application['controller']['method']["args"]
    					);
    				} else {
    					trigger_error( Environment::get("defaults.controllers.name")."::".Environment::get("defaults.controllers.methods.config") . " is private method. Change to public method." ,E_USER_ERROR );
    				}
    			}
    			
    			Environment::set('application' , $this->application);
    			call_user_func_array(
    				array($controllerObject,$this->application['controller']['method']['name']),
    				$this->application['controller']['method']["args"]
    			);
    		}
    		Environment::set('application' , $this->application);
    	}
    	/**
    	*
    	*/
    	private function environment()
    	{
    		$environments = include_once($this->workspace(). 'system/Environment.php');
    		if (!empty($environments)) {
    			foreach ( $environments AS $key => $value) {
    				Environment::set( $key , $value );
    			}
    		}
    	}
    	/**
    	*
    	*/
    	public function setWorkspace($workspace)
    	{
    	    if (!empty($workspace) and is_string($workspace)) {
    	        self::$workspace = $workspace;
    	    }
    	}
    	/**
    	*
    	*/
    	public static function workspace( $concat = null )
    	{
    	    if (!empty(self::$workspace) and is_string(self::$workspace)) {
    	        return rtrim(self::$workspace . DIRECTORY_SEPARATOR , DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR. $concat;
    	    }
    		if (!empty( $concat )) {
    			return dirname(dirname( __FILE__ )) .DIRECTORY_SEPARATOR.$concat;
    		}
    		return dirname(dirname( __FILE__ )) .DIRECTORY_SEPARATOR;
    	}
    }
}