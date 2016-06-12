<?php
namespace Atmosphere
{
    class Model
    {
    	public static $models = array();
    	
    	public function __get ($property )
    	{
    	    if ($property === 'database') {
    	        \Atmosphere\Debug::log(
    	            'To use the model "'.get_class($this).'", you must first enable it. Use "$this->'.get_class($this).'->_enable();" in you controller class to enable.',
    	            'Model deactivated.'
    	        );
    	    }
    	}
    	public function _enable ()
    	{
    	    if (!property_exists($this, 'databaseFactory')) {
    	        $this->databaseFactory = 'databases.localhost';
    	    }
    	    $this->database = \Atmosphere\Database\Database::factory(\Atmosphere\Environment::get($this->databaseFactory));
    	}
    	
    	public static function __callStatic( $method , $args )
    	{
    		trigger_error("The method $method does not exist in the class Model.", E_USER_ERROR);
    	}
    	
    	public function __call( $method , $args )
    	{
    		trigger_error("The method $method does not exist in the class ".get_class( $this ).".", E_USER_ERROR);
    	}
    	
    	
    	public static function load( $name )
    	{
    	    $model = \Atmosphere\Environment::get( "filesystem.models" ).$name . '.php';
    	    
    	    if (empty($name)) {
    	        \Atmosphere\Debug::log("Model $name is empty.");
    	        return false;
    	    }
    	    
    	    if (!file_exists($model) OR !is_file($model)) {
    	        \Atmosphere\Debug::log('Model file don\'t exists:'. $model);
    	        return false;
    	    }
    	    
    	    if (!require_once($model)) {
    	        \Atmosphere\Debug::log('The model "'.$name.'" could not be included.');
    	        return false;
    	    }
    	    
    	    if (!is_subclass_of($name, '\Atmosphere\Model')) {
    	        \Atmosphere\Debug::log('The model "'.$name.'" not is a subclass of \Atmosphere\Model.');
    	        return false;
    	    }
    	    return new $name();
    	}
    }
}