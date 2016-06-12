<?php
namespace Atmosphere\Database;

/**
* MySQL class
*
* this class has been created for Othal Framework
*
* @version 0.1
* @author Olaf Erlandsen <olaftriskel@gmail.com>
* @project MySQLClass
*/
class Database
{
	private static $servicesFodler = "services" ;
	private $options = array(
		"hostname"	=>	"localhost",
		"port"		=>	null,
		"username"	=>	"root",
		"password"	=>	null,
		"database"	=>	null,
		"service"	=>	null,		
	);
	public function __construct() {
	}
	
	public function __call( $method , $args )
	{
		Debug::alert("The method :method: does not exist on class :class:.",array(
			'class'		=>	get_class( $this ),
			'method'	=>	$method,
		));
	}
	
	public static function factory( array $options = array() )
	{
		

		if( !empty( $options ) ){
			if( array_key_exists( "service" , $options ) AND is_string( $options["service"] ) ){
    			$class = "\\Atmosphere\\Database\\Services\\". ucfirst(strtolower( $options["service"]));
    			return new $class( $options );
			}else{
				trigger_error("Error");
			}
		}else{
			trigger_error("Error");
		}
	}
}