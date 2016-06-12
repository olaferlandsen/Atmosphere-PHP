<?php
namespace Atmosphere\Database\Services
{
    /**
    *   Help Class
    */
    class Raw
    {
        private $raw;
        public function __construct($raw)
        {
            $this->raw = $raw;
        }
        public function get()
        {
            return $this->raw;
        }
    }
    
    final class Transaction
    {
        private $callback;
        private $queries;
        private $startTransaction = 'START TRANSACTION';
        private $commitTransaction = 'COMMIT';
        private $rollbackTransaction = 'ROLLBACK';
        
        public function __construct ($queries, $callback = null)
        {
            if (is_array($queries) AND count($queries) > 0) {
                foreach ($queries AS $index => &$query) {
                    $query = preg_replace('#(^\s+|\s+$|;+$)#i', '', $query);
                    if(preg_match('#(^commit($|\;|\s+)|^(begin(\s+work)?($|\;|\s+)|start\s+transaction($|\;|\s+)))#i', $query)) {
                        unset($queries[$index]);
                    } elseif (strlen($query) == 0) {
                        unset($queries[$index]);
                    }
                }
                $this->queries = $queries;
                if (count($queries) == 0) {
                    return false;
                }
    	    }
        }
        public function commit()
        {
            array_unshift   ($this->queries, $this->startTransaction);
            array_unshift   ($this->queries, 'SET autocommit = 0;');
            array_push      ($this->queries, $this->commitTransaction);
            return $this->showQuery();
        }
        public function rollback ()
        {
            array_unshift   ($this->queries, $this->startTransaction);
            array_unshift   ($this->queries, 'SET autocommit = 0;');
            array_push      ($this->queries, $this->rollbackTransaction);
            
            return $this->showQuery();
        }
        public function setBeforeCommit ($callback)
        {
            if (is_callable($callback)) {
                $this->callback = $callback;
            }
        }
        public function showQuery()
        {
            $this->queries = array_map(function($query){
                return $query. ';';
            }, $this->queries);
            
            return join("\n", $this->queries);
        }
    }
    
    
    /**
    * MySQL class
    *
    * this class has been created for Othal Framework
    *
    * @version 0.1
    * @author Olaf Erlandsen <olaftriskel@gmail.com>
    * @project MySQLClass
    */
    class MySQLi implements \Atmosphere\Database\DatabaseInterface
    {
    	/**
    	* Replacement limit regexp
    	*	
    	* @version	0.1
    	* @since  	0.1
    	*/
    	const LIMIT_CLAUSE = "/(LIMIT\s+([0-9]+)(\,\s*([0-9]+))?)/i";
    	const ORDER_CLAUSE = "/order\s+by\s+((\s*\,\s*)?(([a-z0-9_]+\.)?[a-z0-9_]+)\s+(asc|desc))/i";
    	const REPLACEMENT_COLUMNS = "/\@\@columns\-\>(?<table>[a-z0-9-_]+)\@\@/i";
    	/**
    	* Replacement opening clause
    	*	
    	* @version	0.1
    	* @since  	0.1
    	*/
    	const REPLACEMENT_OPENING_CLAUSE = ":";
    	/**
    	* Replacement closing clause
    	*	
    	* @version	0.1
    	* @since  	0.1
    	*/
    	const REPLACEMENT_CLOSING_CLAUSE    = ":";
    	private $ignoreErrorNumbers         = array();
    	private $asBoolean                  = false;
    	private $allowAffectFields          = array();
    	private $disallowAffectFields       = array();
    	private $enablePurge                = false;
    	private $onSuccess;
        private $onError;
        private $beforeExecute;
        private $afterExecute;
        private $order = array();
        private $freeResult = true;
        /***/
        /**
        *
        */
        private $cacheSecretKey     = "mysqli-apc-";
        private $isEnableCache        = false;
        private $cacheFromWhere        = null;
        private $cacheTTL           = 0;
    	/**
    	* Last query
    	*	
    	* @var	string
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $query = "";
    	/**
    	* Current database
    	*	
    	* @var	string
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $database = "";
    	/**
    	* Last replacement
    	*	
    	* @var	array
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $replacement = array();
    	/**
    	* Connection link
    	*	
    	* @var	source
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $connection;
    	/**
    	* Check if connected
    	*	
    	* @var		$isConnected
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $isConnected = false;
    	/**
    	* Prepare query
    	*	
    	* @var	boolean
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $isPrepare = false;
    	/**
    	* Query resource
    	*	
    	* @var	source
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $source;
    	/**
    	* Quote format
    	*	
    	* @var	string
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $quote = "'";
    	/**
    	* Limit is set
    	*	
    	* @var	boolean
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $limit;
    	/**
    	* Default limit
    	*	
    	* @var	integer
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $defaultLimit = 10;
    	private $defaultTimeLimit = 30;
    	/**
    	* Page is set
    	*	
    	* @var	mixed
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $offset;
    	/**
    	* Page is set
    	*	
    	* @var	mixed
    	* @access 	private
    	* @version	0.1
    	* @since  	0.1
    	*/
    	private $status = false;
    	
    	public function __call ($method, $args)
    	{
    	    if ($method === 'raw') {
    	        return call_user_func_array($method, $args);
    	    }
    	}
    	/**
    	* Construct method
    	*	
    	* @param  	array	$cinfo	Server config.
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function __construct( array $cinfo = array() )
    	{
    	    
    		if (count( $cinfo ) > 0) {
    		    $this->connection = mysqli_init();
    		    
    			if (!array_key_exists("hostname", $cinfo)) {
    				$cinfo["hostname"] = "localhost";
    			}
    			if (!array_key_exists("username" , $cinfo)) {
    				$cinfo["username"] = "root";
    			}
    			if (!array_key_exists("password", $cinfo)) {
    				$cinfo["password"] = null;
    			}
    			if (!array_key_exists("charset", $cinfo)) {
    				$cinfo["charset"] = "utf8";
    			}
    			if (!array_key_exists("database", $cinfo)) {
    				$cinfo["database"] = null;
    			}
    			
    			if (array_key_exists("options", $cinfo)) {
    			    $this->setOptions($cinfo['options']);
    			}
    			
    			$connection = $this->connect(
    			    $cinfo['hostname'],
    			    $cinfo['username'],
    			    $cinfo['password'],
    			    $cinfo['charset'],
    			    $cinfo['database']
    			);
    			
    			
    			
    			return $connection;
    		}
    		return false;
    	}
    	/**
    	* destruct method
    	*
    	* @return 	null	
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function __destruct()
    	{
    	}
    	public function transaction($queries, $beforeCommit = null)
    	{
    	    return new Transaction($queries, $beforeCommit);
    	}
    	/**
    	* Connect with the server
    	*	
    	* @param  	string	$server		Server address.
    	* @param  	string	$user		Server user.
    	* @param  	string	$pass		Server password.
    	* @param  	boolean	$pconnect	Persistent connection.
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function connect ($hostname = "localhost", $username = 'root', $password = '', $charset = "utf8", $database = null )
    	{
    	    mysqli_real_connect($this->connection, $hostname, $username, $password);
    		if (mysqli_connect_errno() == 0) {
    			$this->isConnected = true;
    			$this->charset( $charset );
    			if (!empty($database)) {
    				return $this->useDatabase( $database );
    			}
    		} else {
    			trigger_error(
    			    sprintf(
    			        "Connect failed: %s [%d]",
    			        mysqli_connect_error(),
    			        mysqli_connect_errno()
    			    ),
    			    E_USER_ERROR
    		    );
    		}
    		return $this;
    	}
    	/**
    	*
    	*/
    	public function setOptions ($options)
    	{
		    if (is_array($options) AND !empty($options) ) {
		        $allowOptions = array(
		            MYSQLI_OPT_CONNECT_TIMEOUT,
		            MYSQLI_OPT_LOCAL_INFILE,
		            MYSQLI_OPT_LOCAL_INFILE,
		            MYSQLI_READ_DEFAULT_FILE,
		            MYSQLI_READ_DEFAULT_GROUP
		        );
		        if (defined('MYSQLI_OPT_NET_READ_BUFFER_SIZE')) {
		            $allowOptions[] = MYSQLI_OPT_NET_READ_BUFFER_SIZE;
		        }
		        if (defined('MYSQLI_SERVER_PUBLIC_KEY')) {
		            $allowOptions[] = MYSQLI_SERVER_PUBLIC_KEY;
		        }
		        if (defined('MYSQLI_OPT_NET_CMD_BUFFER_SIZE')) {
		            $allowOptions[] = MYSQLI_OPT_NET_CMD_BUFFER_SIZE;
		        }
		         
		        $index = 0;
		        foreach ($options AS $option => $value) {
		            if(is_string($option)){
		                if (defined($option)) {
		                    $option = constant($option);
		                }
		            }
		            if (!in_array($option, $allowOptions)) {
		                continue;
		            }
		            $index++;
		            mysqli_options($this->connection, $option, $value);
		        }
		        if ($index > 0) {
		            return true;
		        }
		    }
		    return false;
    	}
    	/**
    	*
    	*/
    	public function setOption ($option, $value)
    	{
		    return $this->setOptions(array(
		        $option => $value
		    ));
    	}
    	/**
    	*
    	*/
    	public function setTimeLimit($secs)
    	{
    	    return $this->setOption(MYSQLI_OPT_CONNECT_TIMEOUT, $secs);
    	}
    	/**
    	*
    	*/
    	public function asBoolean()
    	{
    	    $this->asBoolean = true;
    	    return $this;
    	}
    	/**
    	* Connect with the server
    	*	
    	* @param  	string	$charset	Server charset
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function charset( $charset )
    	{
    		if( $this->isConnected )
    		{
    			mysqli_set_charset($this->connection , $charset );
    		}
    		return $this;
    	}
    	/**
    	* Select database to use.
    	*	
    	* @param  	string	$name	Database to use.
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.2
    	* @since  	0.1
    	*/
    	public function useDatabase( $dbname )
    	{
    		if ($this->isConnected) {
    			if (!empty($dbname)) {
    				$this->database = $dbname;
    				if (!mysqli_select_db($this->connection , $dbname)) {
    					trigger_error("Could not select the database.", E_USER_ERROR);
    				}
    			} else {
    				trigger_error("Database name is empty", E_USER_ERROR);
    			}
    		}
    		return $this;
    	}
    	/**
    	* List databases
    	*	
    	* @return 	array	database list
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchDatabases()
    	{
    		$rows = $this->prepare("SHOW DATABASES")->fetch();
    		if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		return $rows;
    	}
    	/**
    	* Create new schema
    	*	
    	* @return 	bool
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function createDatabase( $database , $charset = "utf8" , $collate = "utf8_general_ci" )
    	{
    		$rows = $this->prepare("CREATE DATABASE `$database` DEFAULT CHARACTER SET $charset COLLATE $collate")->exec();
    		if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		return $rows;
    	}
    	/**
    	* List table from database
    	*	
    	* @param  	string	$database	Database to use.
    	* @return 	array	Tables
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchTables( $database = null )
    	{
    		$tables = array();
    		if (is_null($database)) {
    			$database = $this->database;
    		}
    		$this->prepare("SHOW TABLES FROM ".self::REPLACEMENT_OPENING_CLAUSE."database".self::REPLACEMENT_CLOSING_CLAUSE ,array(
    			'database'	=>	$database
    		),"`")->exec();
    		while ($table = mysqli_fetch_array($this->source, MYSQLI_NUM)) {
    			$tables[] = $table[0];
    		}
    		
    		if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $tables,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		return $tables;
    	}
    	/**
    	* List columns from table
    	*	
    	* @param  	string	$table	Table to use.
    	* @return 	array	Columns
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchFields( $table , $onlyName = false, $ttl = 0)
    	{
    	    if (apc_exists("colums-table-$table")) {
    	        return apc_fetch("colums-table-$table");
    	    }
    	    $limit = $this->limit;
    	    $offset = $this->offset;
    		$columns = $this->limit(null)->offset(null)->prepare(
    		    "SHOW COLUMNS FROM ".self::REPLACEMENT_OPENING_CLAUSE."table".self::REPLACEMENT_CLOSING_CLAUSE,
    		    array('table'=>$table),
    		    "`"
    		)->fetchAssoc();
    		$this->limit($limit)->offset($offset);
    		if ($onlyName === true) {
    		    return array_map(function($column) use($table){
    		        return $table.".".$column["Field"];
    		    }, $columns);
    		}
    		apc_store("colums-table-$table", $columns, intval($ttl));
    		return $columns;
    	}
    	/**
    	* Prepare query
    	*	
    	* @param  	string	$query			Query to use.
    	* @param  	array	$replacement	replacement.
    	* @param  	string	$quotes			Default = '.
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    
    	public function prepare( $query , array $replacement = array() , $quotes = "'", $columns = array())
    	{
    		if (!empty($query)) {
    			$this->replacement = $replacement;
    			$query = preg_replace(self::LIMIT_CLAUSE,"", $query);
    			
    			$query = preg_replace_callback(self::REPLACEMENT_COLUMNS, function ($match) {
    			    if (array_key_exists("table", $match)) {
    			        $columns = $this->fetchFields($match["table"], true);
    			        //$columns = $this->loadColumns($match["table"]);
    			        if (count($columns) == 0) {
    			            return "[count igual a cero]";
    			        }
    			        return join(',', $columns);
    			        
                    } else {
                        return "[no existe 'table' en el array]";
                    }
    			}, $query);
    			
    			foreach ($replacement AS $key => $value) {
    				$isQuoted = false;
    				if (is_object($value)) {
    					$value = serialize( $value );
    				} elseif (is_array( $value ) AND count( $value ) > 0) {
    					$value = join(",", $value );
    					$value = mysqli_real_escape_string($this->connection, $value);
    				} elseif (is_numeric($value)) {
    					$value = strval( $value );
    				}
    				$value = $this->quote( $value,true,$quotes);
    				$preg = preg_quote(self::REPLACEMENT_OPENING_CLAUSE.$key.self::REPLACEMENT_CLOSING_CLAUSE);
    				$query = preg_replace("/($preg)/i", $value, $query);
    			}
    			$this->isPrepare = true;
    			
    			// LIMIT CLAUSE
    			if (!empty($this->limit) AND $this->limit > 0) {
    			    $query = preg_replace(
        			    self::LIMIT_CLAUSE,
        			    "",
        			    $query
        			);
        			if ($this->limit !== null OR $this->limit !== false) {
    				    $query .= " LIMIT ".$this->limit;
    			    }
    				$this->limit = null;
    				
    		    }
    			
    			// OFFSET CLAUSE
    			if (!empty($this->offset) AND $this->offset > 0) {
    			    $query = preg_replace(
    			        self::LIMIT_CLAUSE,
    			        'LIMIT '.$this->offset.',$2',
    			        $query
    			    );
    			    $this->offset = null;
    		    }
    		    
    		    // ORDER CLAUSE
    			if (!empty($this->order) AND is_array($this->order) AND count($this->order) > 0) {
    			    $order = "ORDER BY";
    			    foreach($this->order AS $table => $mode){
    			        if (empty($mode)) {
    			            $mode = "asc";
    			        }
    			        $order .= " $table $mode";
    			    }
    			    
    			    $query = preg_replace(
    			        self::ORDER_CLAUSE,
    			        $order,
    			        $query
    			    );
    			    $this->order = array();
    		    }
    		    
    		    
    			
    			$this->query = $query;
    			
    			
    			return $this;
    		}else{
    			trigger_error( "Query is empty.", E_USER_ERROR);
    		}
    	}
    	/**
    	* Fetch data from query
    	*	
    	* @param  	integer	$limit	Default null
    	* @param  	integer	$page	Default null
    	* @return 	array			Array data from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetch( $limit = null , $page = null)
    	{
    	    
    	    if ($this->isInCache()) {
    		    return $this->getCache(true);
    		}
    		
    		$rows = array();
    		$this->exec();
    		if ($this->error("number") > 0) {
    			trigger_error("Unexpected error in the query.");
    			return false;
    		}
    		while ($row = mysqli_fetch_array($this->source, MYSQLI_NUM)) {
    			if (count($row) == 1) {
    				$row = array_values( $row );
    				$rows[] = $row[ 0 ];
    			} else {
    				$rows[] = $row;
    			}
    		}
    		if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		
    		
    		return $this->storeOnCache($rows);
    	}
    	/**
    	* Fetch associative data from query
    	*	
    	* @param  	integer	$limit	Default null
    	* @param  	integer	$page	Default null
    	* @return 	array			Array data from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchAssoc( $limit = null , $page = null )
    	{
    	    if ($this->isInCache()) {
    		    return $this->getCache(true);
    		}
    		
    		$rows = array();
    		$this->exec();
    		while ($row = mysqli_fetch_assoc( $this->source)) {
    			if (count($row) == 1) {
    				$row = array_values( $row );
    				$rows[] = $row[ 0 ];
    			} else {
    				$rows[] = $row;
    			}
    		}
    		$rows = array_map([$this, "convertType"], $rows);
    		if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		
    		
    		
    		return $this->storeOnCache($rows);
    	}
    	/**
    	* Fetch associative first row from query
    	*
    	* @return 	array			Array data from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function row ($allow = array())
    	{
    	    if ($this->isInCache()) {
    		    return $this->getCache(true);
    		}
    		
    		$this->limit(1);
    		$this->offset(null);
    		$this->exec();
    		if (mysqli_errno($this->connection) > 0) {
    			trigger_error(
    			    sprintf(
    			        '[%d] %s',
    			        mysqli_errno($this->connection),
    			        mysqli_error($this->connection)
    			    )
    			,E_USER_ERROR);
    		}
    		$rows = array();
    		$this->exec();
    		
    		
    		
    		$row = mysqli_fetch_assoc($this->source);
    		
    		return $row;
    	}
    	
    	/**
    	* Return row column combination 
    	*	
    	* @param  	integer	$row	Default 0
    	* @param  	integer	$field	Default 0
    	* @return 	mixed			Data from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function result( $row = 0 , $field = 0 )
    	{
    	    $this->exec();
    	    if (is_object($this->source) AND mysqli_num_rows($this->source) == 1) {
    	        mysqli_data_seek(
    	            $this->source,
    	            (mysqli_num_rows($this->source)-1)
    	        );
    	        $result = mysqli_fetch_array($this->source)[$row];
    	        if ($this->freeResult === true) {
        		    $this->freeResult();
        		}
        		return $result;
    	    }
    	    return false;
    	}
    	
    	
    	public function paginator ($query, array $replacement = array(), $limit = 1, $offset = 0)
    	{
    	    if ($this->isInCache()) {
    		    return $this->getCache(true);
    		}
    	    $results = (object)array(
    	        "itemsPerPage"      =>  intval($limit),
    	        "startFrom"         =>  0,
    	        "currentPage"       =>  max(intval($offset), 1),
    	        "nextPage"          =>  1,
    	        "prevPage"          =>  1,
    	        "lastPage"          =>  1,
    	        "firstPage"         =>  1,
    	        "numPages"          =>  1,
    	        "isFirstPage"       =>  false,
    	        "isLastPage"        =>  false,
    	        "numAllItems"       =>  0,
    	        "numCurrentItems"   =>  0,
    	        "items"             =>  array(),
    	    );
    	    
    	    $query = preg_replace(
    		    self::LIMIT_CLAUSE,
    		    "",
    		    $query
    		);
    	    
    	    
    	    $results->numAllItems       = $this->prepare($query, $replacement)->numRows();
    	    $results->numPages          = ceil($results->numAllItems/ $results->itemsPerPage);
    	    $results->lastPage          = $results->numPages;
    	    $results->startFrom         = max(($results->currentPage - 1) * $results->itemsPerPage, 0);
    	    $results->prevPage          = max(1, $results->currentPage-1);
    	    $results->nextPage          = max(min( ($results->currentPage+1) , $results->numPages) , 1);
    	    
    	    $this->limit($results->itemsPerPage);
    	    $this->offset($results->startFrom);
    	    
    	    $results->items             = $this->prepare($query, $replacement)->fetchAssoc();
    	    $results->numCurrentItems   = count($results->items);
    	    
    	    $results->isFirstPage       = ($results->firstPage === $results->currentPage);
    	    $results->isLastPage        = ($results->lastPage === $results->currentPage);
    	    
    	    
    	    
    	    
    	    return $this->storeOnCache($results);
    	}
    	/**
    	* num rows query
    	*	
    	* @return 	integer	Numbers of rows from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function numRows()
    	{
    	    if ($this->isInCache()) {
    		    return $this->getCache(true);
    		}
    		
    		$this->exec();
    		if ($this->error("number") > 0) {
    			trigger_error("Unexpected error in the query.", E_USER_ERROR);
    		}
    		
    		$row = mysqli_num_rows($this->source);
    		
    		
    		return $this->storeOnCache($row);
    	}
    	/**
    	* Escape data
    	*	
    	* @param  	mixed	$data
    	* @param  	boolean	$quote	Default = false
    	* @param  	string	$quotes	Default = '
    	* @return 	string	Result from escape
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function quote( $data , $quote = false , $quotes = "'" )
    	{
    		if (is_array($data) OR is_object($data)) {
    		    if (is_object($data) AND get_class($data) == __NAMESPACE__.'\Raw' ) {
    		        return $data->get();
    		    }
    			$data = serialize( $data );
    		} elseif (is_bool($data)) {
    			if ($data === true) {
    				return "1";
    			}
    			return "0";
    		} elseif (is_numeric($data) OR is_float($data) or is_integer($data)) {
    		    if (is_float($data)) {
    		        return strval(str_replace(",", ".",$data));
    		    } else {
    		        return (int)preg_replace("/((\.|\,)\d+)$/i", "", $data);
    		    }
    		} elseif (empty($data)) {
    		    if (is_string($data)) {
    		        return $quotes.$quotes;
    		    }
    			return "NULL";
    		}
    		
    		if ($quote === true AND !empty($data)) {
    			$data = mysqli_real_escape_string ($this->connection, $data);
    			$data = $quotes.$data.$quotes;
    		}
    		return $data;
    	}
    	
    	
    	public function escape ($data) {
    	    return mysqli_real_escape_string ($this->connection, $data);
    	}
    	/**
    	* Set limit on query
    	*	
    	* @param  	integer	$limit
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function limit ($limit)
    	{
    		if (is_integer( $limit ) AND $limit > 0 OR $limit === null) {
    			$this->limit = $limit;
    		}
    		return $this;
    	}
    	/**
    	* Set limit on query
    	*	
    	* @param  	integer	$limit
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function order ($order)
    	{
    	    if (!is_array($order) OR count($order) < 1) {
    	        trigger_error("__setOrder is empty.", E_USER_ERROR);
    	    }
    	    
    	    
    	    
    		return $this;
    	}
    	/**
    	* Set page of limit on query
    	*	
    	* @param  	integer	$page
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function offset ($offset)
    	{
    		if (is_integer( $offset ) AND $offset > 0) {
    			$this->offset = $offset;
    		}
    		return $this;
    	}
    	/**
    	* Retrive array with the errors
    	*	
    	* @param  	string	$retrive	Default null
    	* @return 	array	Errors
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function error( $retrive = null )
    	{
    		if( $this->isConnected )
    		{
    			$error = mysqli_error( $this->connection );
    			$errno = mysqli_errno( $this->connection );
    			
    			$error = array(
    				'query'			=>	$this->query,
    				'error'			=>	$error,
    				'number'		=>	$errno,
    				'replacement'	=>	$this->replacement,
    			);
    			if( array_key_exists("stat",$this->connection) )
    			{
    				$error["stat"] = preg_split("/(\s){2,}/",$this->connection->stat);
    			}
    			if( array_key_exists( strtolower( $retrive ) , $error ) )
    			{
    				return $error[ strtolower( $retrive ) ];
    			}
    			return $error;
    		}
    	}
    	/**
    	* Delete rows
    	*	
    	*/
    	public function delete($table, $keys)
    	{
    	    $rows = $this->prepare("DELETE FROM $table WHERE $keys")->exec();
    	    if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		return $rows;
    	}
    	/**
    	* Truncate table
    	*
    	*/
    	public function truncate($table)
    	{
    	    $rows = $this->prepare("TRUNCATE TABLE $table")->exec();
    	    if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $rows,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		return $rows;
    	}
    	/**
    	* Insert one or more rows
    	*	
    	* @param  	string	$table	Table name to use
    	* @param  	array	$data	Row or Rows
    	* @param  	boolean	$retriveId	Retrive last id
    	* @return 	mixed
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function insert( $table , array $data = array() , $retriveId = true)
    	{
    		$rows			= array();
    		$columns		= array();
    		
    		$data           = $this->purgeNotAllowedFields($data);
    		
    		$dataRows 		= array_values( $data );
    		$dataColumns	= array_keys( $data );
    		
    		
    		
    		if (count($data) > 0) {
    			/**
    			*	Columns
    			**/
    			foreach ($data AS $index => $value) {
    				if (is_array($value)) {
    					foreach ( array_keys($value) AS $token => $name) {
    						if (!empty($name)) {
    							$columns[ $name ] = "$table.$name";
    						} else {
    							trigger_error("Column [$index.$index] is empty", E_USER_ERROR);
    						}
    					}
    				} else {
    					if (!empty($index)) {
    						$columns[] = "$table.$index";
    					} else {
    						trigger_error("Column $index is empty.", E_USER_ERROR);
    					}
    				}
    			}
    			/**
    			*	Rows
    			**/
    			if (is_array($dataRows[0])) {
    				foreach ($data AS $index => $row) {
    					if (count($row) == count($columns)) {
    						$rows[ $index ] = $row;
    					} else {
    						trigger_error("The number of values is not the same as the number of columns.", E_USER_ERROR);
    					}
    				}
    			} else {
    				if (count($data) == count($columns)) {
    					$rows[] = $data;
    				} else {
    					trigger_error("The number of values is not the same as the number of columns(".count($data)."/".count($columns).").", E_USER_ERROR);
    				}
    			}
    			$SQL = "INSERT INTO `$table` (". join(",", $columns ).") VALUES ";
    			$loop = 0;
    			foreach ($rows AS $index => $row) {
    				$loop++;
    				foreach ($row AS $subindex => &$value) {
    					$value = $this->quote( $value ,true);
    				}
    				$SQL .= "(".join( "," , $row) . ")";
    				if ($loop < count( $rows )) {
    					$SQL .= ",";
    				}
    			}
    			$SQL .=";";
    			$result = $this->limit(null)->prepare( $SQL );
    			$result = mysqli_query($this->connection, $this->query);
    			if ($retriveId === true) {
    			    $id = mysqli_insert_id( $this->connection );
    			    if ($this->asBoolean === true) {
    			        if (mysqli_errno($this->connection) == 0) {
            		        $id = true;
            		    }
            		    $id = false;
            		}
    				if ($this->status === true AND !empty($this->onSuccess)) {
            		    call_user_func_array($this->onSuccess, array(
            		        $id,
            		        $this->query
            		    ));
            		    $this->onSuccess = null;
            		}
    				return $id;
    			} elseif ($this->asBoolean === true) {
    			    if (mysqli_errno($this->connection) == 0) {
    			        if ($this->status === true AND !empty($this->onSuccess)) {
                		    call_user_func_array($this->onSuccess, array(
                		        true,
                		        $this->query
                		    ));
                		    $this->onSuccess = null;
                		}
        		        return true;
        		    }
        		    return false;
        		}
        		
    		} else {
    		    trigger_error("Empty data", E_USER_ERROR);
    		}
    		return $this;
    	}
    	
    	public function allowAffectFields ($allow = array())
        {
            $this->enablePurge();
            if (!empty($allow)) {
                if (is_string($allow)) {
                    $allow = explode(",", $allow);
                    $allow = array_map("trim", $allow);
                }
                
                foreach ($allow AS $field) {
                    if(!empty($field)){
                        $this->allowAffectFields[] = $field;
                    }
                }
            }
            return $this;
        }
        public function disallowAffectFields ($disallow = array())
        {
            $this->enablePurge();
            if (!empty($disallow)) {
                
                if (is_string($allow)) {
                    $allow = explode(",", $allow);
                    $allow = array_map("trim", $allow);
                }
                
                foreach ($disallow AS $field) {
                    if(!empty($field)){
                        $this->disallowAffectFields[] = $field;
                    }
                }
            }
            return $this;
        }
        /***
        *   Si $strict es igual a TRUE, entonces si no se setio ningun campo, se truncara el objetivo.
        */
        private function purgeNotAllowedFields ($target , $strict = false, $sameNumberFields = false)
        {
            // default
            if (count($this->allowAffectFields) == 0 AND $this->enablePurge == false) {
                return $target;
            }
            
            $allow = [];
            // strict mode
            if ($strict === true AND count($this->allowAffectFields) == 0) {
                return $allow;
            }
            
            // disallow (remove)
            foreach (array_keys($target) AS $field) {
                if (in_array($field, $this->disallowAffectFields)) {
                    unset($target[$target]);;
                }
            }
            
            // allow
            foreach ($target AS $field => $value) {
                if (in_array($field, $this->allowAffectFields)) {
                    $allow[$field] = $value;
                }
            }
            
            // same numbers
            if ($sameNumberFields === true AND count($allow) != count($this->allowAffectFields) ) {
                return array();
            }
            //endpoint
            return $allow;
        }
        
        private function enablePurge ()
        {
            $this->enablePurge = true;
        }
        private function disablePurge ()
        {
            $this->allowAffectFields = array();
            $this->enablePurge= false;
        }
    	public function purgeErrorNumbers()
    	{
    	    $this->ignoreErrorNumbers= array();
    	}
    	public function ignoreErrorNumbers($numbers = array())
    	{
    	    if (is_string($numbers)) {
                $numbers = explode(",", $numbers);
                $numbers = array_map("trim", $numbers);
            }
    	    if (is_integer($numbers) AND $numbers > 0) {
    	        $this->ignoreErrorNumbers[] = $numbers;
    	    } elseif (is_array($numbers)) {
    	        foreach ($numbers AS $number) {
    	            $this->ignoreErrorNumbers[] = $number;
    	        }
    	    }
    	    return $this;
    	}
    	/**
    	* Update
    	*	
    	* @return 	boolean
    	* @access 	public
    	* @version	0.1
    	* @since  	0.2
    	*/
    	public function update( $table, $sets = array(), $primary = array(), $limit = 1)
    	{
    	    $fields = array();
    	    $primaries = array();
    	    // primary
    	    if (is_array($primary) and count($primary) > 0){
        	    foreach ($primary AS $field => $value) {
        	        if (is_integer($field) OR is_float($field) OR empty($field)) {
        	            trigger_error("Invalid field name",E_USER_ERROR);
        	            return false;
        	        }
        	        $primaries[] = "$table.$field = :primary$table$field:";
        	        $fields["primary".$table.$field] = $value;
        	    }
    	    }
    	    // sets
    	    if (count($sets) > 0) {
        	    foreach ($sets AS $field => &$value) {
        	        if (is_integer($field) OR is_float($field) OR empty($field)) {
        	            trigger_error ("Invalid field name", E_USER_ERROR);
        	            return false;
        	        }
        	        $fields["update".$table.$field] = $value;
        	        $value = "$table.$field = :update$table$field:";
        	    }
    	    } else {
    	        trigger_error ("Dont have fields to update", E_USER_ERROR);
    	        return false;
    	    }
    	    if (count($primaries) > 0) {
    	        $primaries = " WHERE ".join(" AND " , $primaries);
    	    } else {
    	        $primaries = NULL;
    	    }
    	    $sets = join("," , $sets);
    	    
    	    $this->limit($limit)->prepare("UPDATE $table SET $sets$primaries",$fields)->exec();
    	    
    	    if ($this->status === true AND !empty($this->onSuccess)) {
    		    call_user_func_array($this->onSuccess, array(
    		        $this->status,
    		        $this->query
    		    ));
    		    $this->onSuccess = null;
    		}
    		
    	    return $this->status;
    	}
    	/**
    	* Update
    	*	
    	* @return 	boolean
    	* @access 	public
    	* @since  	3.1
    	*/
    	public function onDuplicateKeyUpdate($enable = true)
    	{
    	    $this->onDuplicateKeyUpdate = true;
    	    return $this;
    	}
    	/**
    	* Close connection
    	*	
    	* @return 	boolean
    	* @access 	public
    	* @version	0.2
    	* @since  	0.1
    	*/
    	public function close()
    	{
    		return mysqli_close( $this->connection );
    	}
    	/**
    	* Execute query
    	*	
    	* @return 	object $this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function exec( $resultmode = MYSQLI_STORE_RESULT , $multiquery = false)
    	{
    	    if ($multiquery === true) {
    	        $this->source = @mysqli_multi_query ( $this->connection , $this->query );
    	    } else {
                $this->source = @mysqli_query (
                    $this->connection,
                    $this->query,
                    $resultmode
                );
    	    }
    	    
    	    
    	    
    		$this->status = $this->source;
    		$this->asBoolean = false;
    		
    		if (mysqli_errno($this->connection) > 0 OR $this->source === false ) {
    		    trigger_error(
    		        sprintf('[%d] %s ',
        		        mysqli_errno($this->connection),
        		        mysqli_error($this->connection)
    		        ),
    		        E_USER_ERROR
    		    );
    		}
    		
    		//$this->ignoreErrorNumbers = array();
    		return $this;
    	}
    	
    	public function enableCache($ttl, $fromWhere = null)
    	{
    	    trigger_error("MySQL Cache not enable because TTL is invalid", E_USER_ERROR);
    	    if (is_numeric($ttl) and $ttl > 0) {
    	        $this->isEnableCache = true;
    	        $this->cacheFromWhere = $fromWhere;
    	        $this->cacheTTL = intval($ttl);
    	    } else {
    	        trigger_error("MySQL Cache not enable because TTL is invalid", E_USER_ERROR);
    	    }
    	    
    	    return $this;
    	}
    	/**
    	*
    	*/
    	public function disableCache()
    	{
    	    $this->isEnableCache = false;
    	    $this->cacheTTL = null;
    	    return $this;
    	}
    	/**
    	*
    	*/
    	public function resetCache()
    	{
    	    $this->disableCache();
    	    $iter = new APCIterator('user');
            foreach ($iter as $item) {
                if (preg_match( "/^".preg_quote($this->cacheSecretKey)."/i" , $item['key'])) {
                    apc_delete($item['key']);
                }
            }
            return $this;
    	}
    	/**
    	*
    	*/
    	private function storeOnCache($source)
    	{
    	    if ($this->isEnableCache() and !empty($this->query)) {
        	    apc_store($this->cacheSecretKey.md5($this->query), array(
        	        'result'    =>  $source,
        	        'fromWhere' =>  $this->cacheFromWhere,
        	        'query'     =>  $this->query,
        	    ), $this->cacheTTL);
        	}
        	$this->disableCache();
        	return $source;
    	}
    	/**
    	*
    	*/
    	private function getCache($onlyResult = true)
    	{
    	    $cache = apc_fetch($this->cacheSecretKey.md5($this->query));
    	    if ($onlyResult === true) {
    	        return $cache["result"];
    	    }
    	    return $cache;
    	}
    	/**
    	*
    	*/
    	private function isInCache()
    	{
    	    if ($this->isEnableCache()) {
        	    return apc_exists(
        	        $this->cacheSecretKey.md5($this->query)
        	    );
    	    }
    	    return false;
    	}
    	public function isEnableCache()
    	{
    	    if ($this->isEnableCache === true) {
    	        return true;
    	    }
    	    return false;
    	}
    	/**
    	*
    	*/
    	public function multiExec()
    	{
    	    return $this->exec(null, true);
    	}
    	/**
    	* Get current status
    	*	
    	* @return 	boolean
    	* @access 	public
    	* @version	2
    	* @since  	2
    	*/
    	public function getStatus()
    	{
    	    if (mysqli_errno($this->connection) > 0) {
    	        return false;
    	    } elseif ($this->status === true ) {
    	        return true;
    	    } elseif (is_object($this->status) AND get_class($this->status) == "mysqli_result") {
    	        return true;
    	    }
    	    return false;
    	}
    	/**
    	*
    	*/
    	public function convertType ($var)
    	{
    	    if (is_numeric($var)) {
    	        if (is_string($var)) {
    	            if (ctype_digit($var)) {
    	                return intval($var);
    	            }
    	            return floatval($var);
    	        }
    	    }
    	    
    	    return $var;
    	}
    	/**
    	*
    	*/
    	public function onSuccess($callback)
    	{
    	    if (is_array($callback) OR is_array($callback)) {
    	        if (is_callable($callback)) {
    	            $this->onSuccess = $callback;
    	        }
    	    }
    	    return $this;
    	}
    	
    	/**
    	*
    	*/
    	public function onError($callback)
    	{
    	    if (is_array($callback) OR is_array($callback)) {
    	        if (is_callable($callback)) {
    	            $this->onError = $callback;
    	        }
    	    }
    	    return $this;
    	}
    	/**
    	*
    	*/
    	public function beforeExecute($callback)
    	{
    	    if (is_array($callback) OR is_array($callback)) {
    	        if (is_callable($callback)) {
    	            $this->beforeExecute = $callback;
    	        }
    	    }
    	    return $this;
    	}
    	/**
    	*
    	*/
    	public function afterExecute($callback)
    	{
    	    if (is_array($callback) OR is_array($callback)) {
    	        if (is_callable($callback)) {
    	            $this->afterExecute = $callback;
    	        }
    	    }
    	    return $this;
    	}
    	
    	public function freeResult()
        {
            mysqli_free_result($this->source);
        }
        
        
        
        /**
        *
        */
        public function call( $storeProcedure, $params = array() )
        {
            $rows = array();
            $this->limit(false)->prepare($storeProcedure, $params);
            $this->source = mysqli_multi_query ($this->connection, $this->query);
            if ($this->source) {
                do {
                    if ($result = mysqli_store_result($this->connection)) {
                        while ($row = mysqli_fetch_row($result)) {
                            $rows[] = $row;
                        }
                        //mysqli_close($this->connection);
                    }
                } while (mysqli_next_result($this->connection)) ;
            }
            return $rows;
        }
        
        
        
        public function raw($raw)
        {
            return new Raw ($raw);
        }
    }
    
    
    
    
}