<?php
/**
 * @param string $bson
 * @return array
 */
function bson_decode (  $bson ){}

/**
 * @param mixed $anything
 * @return string
 */
function bson_encode ( $anything ){}

class Mongo {
	/* Constants */
	/**
	 * @var string
	 */
	const VERSION = "1" ;
	/**
	 * @var string
	 */
	const DEFAULT_HOST = "localhost" ;
	/**
	 * @var int
	 */
	const DEFAULT_PORT = 27017 ;
	/* Fields */
	/**
	 * @var boolean
	 */
	public $connected = FALSE ;
	/**
	 * @var string 
	 */
	protected $server = "localhost:27017";
	/**
	 * @var boolean 
	 */
	protected $persistent = FALSE ;
	
	/* Methods */
	/**
	 * @return boolean 
	 */
	public function close ( );
	/**
	 * @return boolean
	 */
	public function connect ( );
	
	/**
	 * @return boolean 
	 */
	protected function connectUtil ( );
	
	/**
	 * @param string $server
	 * @param array $options
	 */
	function __construct ($server = "mongodb://localhost:27017", array $options = array("connect" => TRUE));
	
	/**
	 * @param mixed $db
	 * @return array
	 */
	public function dropDB ( mixed $db );
	
	/**
	 * @return bool
	 */
	public function forceError (  );
	
	/**
	 * @param string $dbname
	 * @return MongoDB
	 */
	public function __get ( $dbname );
	/**
	 * @return array
	 */
	public function lastError ( );
	/**
	 * @return array
	 */
	public function listDBs ( );
	/**
	 * @return boolean
	 */
	public function pairConnect ( );
	/**
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function pairPersistConnect ($username = "" , $password = "" );
	/**
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function persistConnect ($username = "" , $password = "" );
	/**
	 * @return array
	 */
	public function prevError ( );
	/**
	 * @return array
	 */
	public function resetError ( );
	/**
	 * @param string|MongoDB $db
	 * @param string $collection
	 * @return MongoCollection
	 */
	public function selectCollection ( $db , $collection );
	/**
	 * @param string $dbname
	 * @return MongoDB
	 */
	public function selectDB ( $dbname );
	/**
	 * @return string
	 */
	public function __toString ( );
}

class MongoDB {
	/* Constants */
	/**
	 * @var int
	 */
	const PROFILING_OFF = 0 ;
	/**
	 * @var int
	 */
	const PROFILING_SLOW = 1 ;
	/**
	 * @var int
	 */
	const PROFILING_ON = 2 ;
	/* Methods */
	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	public function authenticate ( $username , $password );
	/**
	 * @param array $data
	 * @return array
	 */
	public function command ( array $data );
	/**
	 * @param Mongo $conn
	 * @param string $name
	 */
	function __construct ( Mongo $conn , string $name );
	/**
	 * @param string $name
	 * @param bool $capped
	 * @param int $size
	 * @param int $max
	 * @return MongoCollection 
	 */
	public function createCollection ( string $name, bool $capped = FALSE, int $size = 0, int $max = 0);
	/**
	 * @param string $collection
	 * @param mixed $a
	 * @return array
	 */
	public function createDBRef ( string $collection , mixed $a );
	/**
	 * @return array
	 */
	public function drop ( );
	/**
	 * @param mixed $coll
	 * @return array
	 */
	public function dropCollection ( mixed $coll );
	/**
	 * @param mixed $code
	 * @param array $args
	 * @return array
	 */
	public function execute ( mixed $code, array $args = array() );
	/**
	 * @return bool
	 */
	public function forceError ( );
	/**
	 * @return MongoCollection
	 */
	public function __get ( string $name );
	/**
	 * @return array
	 */
	public function getDBRef ( array $ref );
	/**
	 * @return MongoGridFS 
	 */
	public function getGridFS (string $prefix = "fs" );
	/**
	 * @return int
	 */
	public function getProfilingLevel ( );
	/**
	 * @return array
	 */
	public function lastError ( );
	/**
	 * @return array
	 */
	public function listCollections ( );
	/**
	 * @return array
	 */
	public function prevError ( );
	/**
	 * @return array
	 */
	public function repair (bool $preserve_cloned_files = FALSE, bool $backup_original_files = FALSE  );
	/**
	 * @return array
	 */
	public function resetError ( );
	/**
	 * @return MongoCollection
	 */
	public function selectCollection ( string $name );
	/**
	 * @return int
	 */
	public function setProfilingLevel ( int $level );
	/**
	 * @return string
	 */
	public function __toString ( );
}

class MongoCollection {
	/* Fields */
	/**
	 * @var MongoDB 
	 */
	public $db = NULL ;
	/* Methods */
	/**
	 * @return mixed
	 */
	public function batchInsert ( array $a, array $options = array() );
	public function __construct ( MongoDB $db , string $name );
	/**
	 * @return int
	 */
	public function count (mixed $query = array());
	/**
	 * @return array
	 */
	public function createDBRef ( array $a );
	/**
	 * @param string|array $keys
	 * @return array
	 */
	public function deleteIndex ( $keys );
	/**
	 * @return array
	 */
	public function deleteIndexes ( );
	/**
	 * @return array
	 */
	public function drop (  );
	/**
	 * @return boolean
	 */
	public function ensureIndex ( array $keys , array $options );
	/**
	 * @return MongoCursor
	 */
	public function find ( array $query = array() , array $fields = array() );
	/**
	 * @return array
	 */
	public function findOne ( array $query = array() , array $fields = array()  );
	/**
	 * @return MongoCollection
	 */
	public function __get ( string $name );
	/**
	 * @return array
	 */
	public function getDBRef ( array $ref );
	/**
	 * @return array
	 */
	public function getIndexInfo ( );
	/**
	 * @return string
	 */
	public function getName ( );
	/**
	 * @return array
	 */
	public function group ( mixed $keys , array $initial , MongoCode $reduce, array $condition = array()  );
	/**
	 * @return mixed
	 */
	public function insert ( array $a , array $options = array()  );
	/**
	 * @return mixed
	 */
	public function remove ( array $criteria , array $options = array() );
	/**
	 * @return mixed
	 */
	public function save ( array $a , array $options = array()  );
	/**
	 * @return string
	 */
	public function __toString (  );
	/**
	 * @return boolean
	 */
	public function update ( array $criteria , array $newobj , array $options = array()  );
	/**
	 * @return array
	 */
	public function validate ( bool $scan_data = FALSE  );
}

class MongoCursor
implements Iterator {
	/* Static Fields */
	/**
	 * @var boolean
	 */
	static $slaveOkay = FALSE ;
	/* Methods */
	/**
	 * @return MongoCursor
	 */
	public function addOption ( string $key , mixed $value );
	function __construct ( resource $connection , string $ns , array $query = array(), array $fields = array()  );
	/**
	 * 
	 * @param boolean $all 是否使用limit和skip信息
	 * @return int
	 */
	public function count ( boolean $all = FALSE  );
	/**
	 * @return array
	 */
	public function current ( );
	/**
	 * @return boolean
	 */
	public function dead ( );
	protected function doQuery ( );
	/**
	 * @return array
	 */
	public function explain ( );
	/**
	 * @return MongoCursor
	 */
	public function  fields ( array $f );
	/**
	 * @return array
	 */
	public function  getNext (  );
	/**
	 * @return array
	 */
	public function info (  );
	/**
	 * @return boolean
	 */
	public function hasNext ( );
	/**
	 * @return MongoCursor
	 */
	public function  hint ( array $key_pattern );
	/**
	 * @return MongoCursor
	 */
	public function  immortal ( boolean $liveForever = true  );
	/**
	 * @return string
	 */
	public function key ( );
	/**
	 * @return MongoCursor
	 */
	public function  limit ( int $num );
	public function next (  );
	public function reset (  );
	public function  rewind (  );
	/**
	 * @return MongoCursor
	 */
	public function skip ( int $num );
	/**
	 * @return MongoCursor
	 */
	public function slaveOkay ( boolean $okay = true  );
	/**
	 * @return MongoCursor
	 */
	public function snapshot ( );
	/**
	 * @return MongoCursor
	 */
	public function sort ( array $fields );
	/**
	 * @return MongoCursor
	 */
	public function tailable ( boolean $tail = true  );
	/**
	 * @return MongoCursor
	 */
	public function timeout ( int $ms );
	/**
	 * @return boolean
	 */
	public function valid ( );
}

class MongoId {
	/* Methods */
	function __construct ( string $id = NULL  );
	/**
	 * @return int
	 */
	public function getTimestamp ( );
	/**
	 * @return string
	 */
	public function __toString ( );
}

class MongoRegex {
	/* Fields */
	/**
	 * @var string
	 */
	public $regex ;
	/**
	 * @var string
	 */
	public $flags ;
	/* Methods */
	function __construct ( string $regex );
	/**
	 * @return string
	 */
	public function __toString ( );
}

class MongoCode {
	/* Methods */
	function __construct ( string $code , array $scope = array()  );
	/**
	 * @return string
	 *
	 */
	public function  __toString ( );
}

class MongoGridFS
extends MongoCollection {
	/* Fields */
	/**
	 * @var MongoCollection
	 */
	public  $chunks = NULL ;
	/**
	 * @return string
	 */
	protected $filesName = NULL ;
	/**
	 * @return string
	 */
	protected $chunksName = NULL ;
	/* Methods */
	function __construct ( MongoDB $db, string $prefix = "fs" );
	/**
	 * @return array
	 */
	public function drop (  );
	/**
	 * @return MongoGridFSCursor
	 */
	public function  find ( array $query = array() , array $fields = array()  );
	/**
	 * @return MongoGridFSFile
	 */
	public function  findOne ( mixed $query = array()  );
	/**
	 * @return boolean
	 */
	public function remove ( array $criteria = array() , boolean $just_one = FALSE  );
	/**
	 * @return mixed
	 */
	public function storeBytes ( string $bytes , array $extra = array() , array $options = array()  );
	/**
	 * @return mixed
	 */
	public function storeFile ( string $filename, array $extra = array(), array $options = array()  );
	/**
	 * @return mixed
	 */
	public function storeUpload ( string $name , string $filename = NULL  );
}


class MongoException
extends Exception {
}

class MongoCursorException
extends MongoException {
}

class MongoCursorTimeoutException
extends MongoCursorException {
}

class MongoConnectionException
extends MongoException {
}

class MongoGridFSException
extends MongoException {
}

?>