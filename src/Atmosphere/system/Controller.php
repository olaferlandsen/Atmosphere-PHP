<?php
namespace Atmosphere\Controller
{
    class Controller extends \Atmosphere\Atmosphere
    {
        
        public $session   = array();
        /***/
    	public $config  = array();
    	/**
    	*
    	*/
    	public function __get( $property )
    	{
    	}
    	/**
    	*
    	*/
    	public function __construct()
    	{
    	    \Atmosphere\Utilities\Utilities::httpResponseCode(\Atmosphere\Environment::get('defaults.http.responseCode'));
    	    if (class_exists('\Atmosphere\Template')) {
        	    $this->template = new \Atmosphere\Template();
        	    $this->env = new \Atmosphere\Environment();
        	    $this->Atmosphere = \Atmosphere\Environment::get('Atmosphere');
    	    }
    	}
    	/**
    	*
    	*/
    	public function responseCode($code)
    	{
    	    \Atmosphere\Utilities\Utilities::httpResponseCode($code);
    	}
    	
    	public function on ($method, $success, $error = null)
    	{
    	    if (!is_int($success) AND !ctype_digit($success) AND !is_callable($success)) {
    	        trigger_error('You need to define a return action.', E_USER_ERROR);
    	    }
    	    
    	    $callback = null;
    	    if (is_string($method)) {
        	    $method = preg_replace('#\s+#', ' ', $method);
        	    $method = trim($method);
        	    if (empty($method)) {
        	        trigger_error('You must set the method to use.', E_USER_ERROR);
        	    }
        	    $method = strtoupper($method);
        	    $method = explode(',', $method);
        	} elseif (!is_array($method)) {
        	    trigger_error('Only accept string and array params', E_USER_ERROR);
        	}
        	$method = array_map('strtoupper', $method);
    	    if (in_array($_SERVER['REQUEST_METHOD'], $method)) {
    	        $callback = $success;
    	    } else {
    	        $callback = $error;
    	    }
    	    if (is_numeric($callback)) {
    	        if (is_int($callback) OR ctype_digit($callback)) {
    	            return $this->responseCode($callback);
    	        }
    	        trigger_error('You can only use integers', E_USER_ERROR);
    	    } elseif (is_callable($callback)) {
    	        return call_user_func($callback);
    	    }
    	}
    	
    	public function onPut($success, $error = null)
    	{
    	    return $this->on('put', $success, $error);
    	}
    	public function onDelete($success, $error = null)
    	{
    	    return $this->on('delete', $success, $error);
    	}
    	public function onHeader($success, $error = null)
    	{
    	    return $this->on('header', $success, $error);
    	}
    	public function onPost($success, $error = null)
    	{
    	    return $this->on('post', $success, $error);
    	}
    	public function onGet($success, $error = null)
    	{
    	    return $this->on('get', $success, $error);
    	}
    	public function onXHR ($success, $error = null) {
    	    $callback = null;
    	    if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) OR empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    	        $callback = $error;
    	    } elseif (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    	        $callback = $error;
    	    } else {
    	        $callback = $success;
    	    }
    	    if (is_numeric($callback)) {
    	        if (is_int($callback) OR ctype_digit($callback)) {
    	            return $this->responseCode($callback);
    	        }
    	        trigger_error('You can only use integers', E_USER_ERROR);
    	    } elseif (is_callable($callback)) {
    	        return call_user_func($callback);
    	    }
    	    trigger_error('You need to define a return action.', E_USER_ERROR);
    	}
    	/**
    	*
    	*/
    	public function __destruct()
    	{
        	if (!is_array($this->Atmosphere) AND $this->Atmosphere !== null) {
        	    trigger_error('$this->Atmosphere property is reserved.', E_USER_ERROR);
        	}
        	
        	if (class_exists('\Atmosphere\Template')) {
            	$this->template->set('Atmosphere', $this->Atmosphere);
            	
            	if (count($this->template->fetchTemplates()) > 0) {
        	        $this->template->display();
        	    }
    	    }
    	}
    	/**
    	*
    	*/
    	public function import ($model)
    	{
    	    if (is_array($model)) {
    	        foreach ($model AS $name) {
    	            $tmp = \Atmosphere\model::load($name);;
    	            if ($tmp !== false) {
    	                $this->{$name} = $tmp;
    	            }
    	        }
    	    } else {
    	        $tmp = \Atmosphere\model::load($model);;
	            if ($tmp !== false) {
	                $this->{$model} = $tmp;
	            }
            }
    	}
    }
}