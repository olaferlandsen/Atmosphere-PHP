<?php
namespace Atmosphere\Database {

    interface DatabaseInterface
    {
    	public function __construct( array $cinfo = array() );
    	
    	public function asBoolean();
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
    	public function connect( $hostname = "localhost", $username = 'root', $password = '' , $charset = "utf8" , $database = null );
    	/**
    	* Connect with the server
    	*	
    	* @param  	string	$charset	Server charset
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function charset( $charset );
    	/**
    	* Select database to use.
    	*	
    	* @param  	string	$name	Database to use.
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function useDatabase( $dbname );
    	/**
    	* List databases
    	*	
    	* @return 	array	database list
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchDatabases();
    	/**
    	* List table from database
    	*	
    	* @param  	string	$database	Database to use.
    	* @return 	array	Tables
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchTables( $database = null );
    	/**
    	* List columns from table
    	*	
    	* @param  	string	$table	Table to use.
    	* @return 	array	Columns
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function fetchFields( $table );
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
    	public function prepare( $query , array $replacement = array() , $quotes = "'");
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
    	public function fetch( $limit = null , $page = null);
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
    	public function fetchAssoc( $limit = null , $page = null );
    	/**
    	* Fetch associative first row from query
    	*
    	* @return 	array			Array data from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function row();
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
    	public function result( $row = 0 , $field = 0 );
    	/**
    	* num rows query
    	*	
    	* @return 	integer	Numbers of rows from query
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function numRows( );
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
    	public function quote( $data , $quote = false , $quotes = "'" );
    	/**
    	* Set limit on query
    	*	
    	* @param  	integer	$limit
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function limit( $limit );
    	/**
    	* Set page of limit on query
    	*	
    	* @param  	integer	$page
    	* @return 	object	$this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function offset( $offset );
    	/**
    	* Retrive array with the errors
    	*	
    	* @param  	string	$retrive	Default null
    	* @return 	array	Errors
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function error( $retrive = null );
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
    	public function insert( $table , array $data = array() , $retriveId = true);
    	/**
    	* Close connection
    	*	
    	* @return 	boolean
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function close();
    	/**
    	* Execute query
    	*	
    	* @return 	object $this
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function exec();
    	/**
    	* 
    	*	
    	* @param 	string	$function	function name
    	* @return 	array	$args		array params
    	* @access 	public
    	* @version	0.1
    	* @since  	0.1
    	*/
    	public function call( $function , $args = array() );
    }
}