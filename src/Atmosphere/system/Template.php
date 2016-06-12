<?php
namespace Atmosphere
{
    class Template extends Atmosphere
    {
        
    	public $display = false;
    	private $vars = array();
    	private $tpl = array();
    	private $templateEngine = null;
    	public function __construct($application = null)
    	{
    		if (is_null($this->templateEngine)) {
    		    include_once(\Atmosphere\Environment::get('filesystem.libraries').'Smarty/Smarty.php');
    			$this->templateEngine = new \Smarty();
    			$this->templateEngine->muteExpectedErrors();
    			$this->templateEngine->setCompileDir	(\Atmosphere\Environment::get("filesystem.compile"));
    			$this->templateEngine->setCacheDir		(\Atmosphere\Environment::get("filesystem.cache"));
    			$this->templateEngine->setTemplateDir	(\Atmosphere\Environment::get("filesystem.templates"));
    			$this->templateEngine->loadFilter('output', 'trimwhitespace');
    		}
    		
    	}
    	public function _unset( $key )
    	{
    		return $this->templateEngine->clear_assign( $key );
    	}
    	public function setArray( array $data )
    	{
    		return $this->templateEngine->assign( $key );
    		return true;
    	}
    	public function set( $key , $value = null )
    	{
    		$this->vars[] = array(
    		    "name"  =>  $key,
    		    "time"  =>  time(),
    		    "size"  =>  count($value),
    		    "value" =>  $value,
    		);
    		return $this->templateEngine->assign( $key , $value );
    		return false;
    	}
    	public function load( $template , $sets = null )
    	{
    		if (is_array($sets) and count($sets) > 0) {
    		    foreach ($sets AS $k => $v) {
    		        self::set($k,$v);
    		    }
    		}
    		$templatePath = \Atmosphere\Environment::get("filesystem.templates") . $template;
    		if (file_exists ($templatePath) and is_file ($templatePath)) {
    		    if (filesize($templatePath) == 0 ) {
    		        trigger_error("Tempalte \"$template\" is empty", E_USER_ERROR);
    		    }
    			$this->tpl[time()] = $templatePath;
    		} else {
    			trigger_error("Tempalte \"$template\" does not exists", E_USER_ERROR);
    		}
    	}
    	public function fetchVariables()
    	{
    		return $this->vars;
    	}
    	public function fetchTemplates()
    	{
    	    return $this->tpl;
    	}
    	public function display($template = null , $vars = null )
    	{
    	    if (!empty($template)) {
    	        $this->load($template);
    	    }
    	    if (!empty($template)) {
    	        $this->setArray($vars);
    	    }
    		$this->display = true;
    		foreach ($this->tpl AS $template) {
    			$this->templateEngine->display( $template );
    		}
    	}
    	public function countTemplates()
    	{
    	    return count($this->tpl);
    	}
    	public function isDisplayed()
    	{
    	    return $this->display;
    	}
    	public function enableCache()
    	{
    	    $this->templateEngine->caching = 1;
    	}
    	public function disableCache()
    	{
    	    $this->templateEngine->caching = 0;
    	}
    }
}