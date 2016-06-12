<?php
namespace Cuinzy\Cache
{
    class CacheBase
    {
    	public $enabled;
    	public $setDefaultTimeToLive;
    	public $prefix;
    	

    	/**
        *
        */
        public function getInfo()
        {
            \Debug::log($this, "Cache::getInfo");
            return array(
                "prefix"        =>  $this->prefix,
                "defaultTtl"    =>  $this->setDefaultTimeToLive,
                "enabled"       =>  $this->isEnable()
            );
        }
    	/**
        *
        */
        
    	public function enable($enable = true)
    	{
    	    if ($enable === true) {
    	        $this->enabled = true;
    	    }
    	    $this->enabled = false;
    	}
    	/**
        *
        */
    	public function isEnable()
    	{
    	    if (is_null($this->enabled)) {
    	        if (class_exists("\Environment")) {
        	        if (\Environment::exists("cache.enable")) {
        	            $this->enable(\Environment::get("cache.enable"));
        	        }
        	    }
    	    }
    	    
    	    if ($this->enabled === true) {
    	        return true;
    	    }
    	    return false;
    	}
    	/**
        *
        */
        public function setDefaultTimeToLive($ttl)
        {
            if (is_integer($ttl)) {
                $this->setDefaultTimeToLive = $ttl;
            }
            return $this;
        }
        /**
        *
        */
        public function setPrefix($prefix)
        {
            if (is_string($prefix) and !empty($prefix)) {
                $this->prefix = preg_replace("/\-+$/","",$prefix).".";
                return true;
            }
            return false;
        }
    }
}