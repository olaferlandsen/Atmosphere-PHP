<?php
namespace Cuinzy\Cache\Engines{
    class Apc extends \Cuinzy\Cache\CacheBase{
        
        /**
        *
        */
    	public function add( $key , $value = null , $ttl = 0 )
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    	    if ($ttl == 0 AND $this->setDefaultTimeToLive != 0) {
    	        $ttl = $this->setDefaultTimeToLive;
    	    }
    	    //$value = serialize($value);
    		return apc_add( $this->prefix.$key , $value , $ttl );
    	}
    	/**
        *
        */
    	public function set( $key , $value = null , $ttl = 0 )
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    	    if ($ttl == 0 AND $this->setDefaultTimeToLive != 0) {
    	        $ttl = $this->setDefaultTimeToLive;
    	    }
    		return apc_store( $this->prefix.$key , $value , $ttl );
    	}
    	/**
        *
        */
    	public function get( $key )
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    		return apc_fetch($this->prefix.$key);
    	}
    	/**
        *
        */
    	public function delete( $key )
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    	 	return apc_delete($this->prefix.$key);
    	}
    	/**
        *
        */
    	public function clear()
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    	 	return apc_clear_cache( 'user' );
    	}
    	/**
        *
        */
    	public function fetchAll()
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    		$cache = array();
    	 	$APC = apc_cache_info('user');
    	 	if (array_key_exists('cache_list', $APC)) {
    	 		foreach ($APC['cache_list'] AS $item) {
    	 			$cache[ $item['info'] ] = apc_fetch( $item['info'] );
    	 		}
    	 	}
    	 	return $cache;
    	}
    	/**
        *
        */
    	public function exists( $key )
    	{
    	    if (!$this->isEnable()) {
    	        return false;
    	    }
    	 	return apc_exists( $this->prefix.$key );
    	}
    }
}