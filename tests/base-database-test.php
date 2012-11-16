<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';

define( 'ABS_PATH', realpath( $_SERVER['DOCUMENT_ROOT'] . '../' ) . '/' );
define( 'LIB_PATH',  ABS_PATH . 'lib/' );

// Need registry for Database
require LIB_PATH . 'helpers/registry.php';

// DB class for helping
require LIB_PATH . 'test/db.php';

/**
 * Base classe for all tests that needs to connect to Database
 */
abstract class BaseDatabaseTest extends PHPUnit_Extensions_Database_TestCase {
    /**
     * Hold the database variable
     * @var DB
     */
    protected $db;

    private static $pdo = null;

    /**
     * Initialize DB
     */
    public function __construct() {
        $this->db = new DB();
    }

    /**
     * Retrieve a valid database connection
     * @override
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    final public function getConnection() {
        if ( self::$pdo == null ) {
            self::$pdo = new PDO(
                  'mysql:host=' . ActiveRecordBase::DB_HOST
                , ActiveRecordBase::DB_USER, ActiveRecordBase::DB_PASSWORD
            );
        }

        return $this->createDefaultDBConnection( self::$pdo, ActiveRecordBase::DB_NAME );
    }

    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_QueryDataSet( $this->getConnection() );
    }
    
}

/**
 * Load a model
 *
 * @var string $model
 */
function load_model( $model ) {
    // Form the model name, i.e., AccountListing to account-listing.php
    $model_file = substr( strtolower( preg_replace( '/(?<!-)[A-Z]/', '-$0', $model ) ) . '.php', 1 );

    // Get all the model paths we can
    $model_paths = scandir( ABS_PATH . 'model' );
    unset( $model_paths[0], $model_paths[1] );

    foreach( $model_paths as &$mp ) {
        $mp = ABS_PATH . 'model/' . $mp . '/';
    }

    // Define the paths to search
    $paths = array_merge( $model_paths, array( LIB_PATH . 'models/' ) );

    // Loop through each path and see if it exists
    foreach ( $paths as $path ) {
        $full_path = $path . $model_file;

        if ( is_file( $full_path ) ) {
            require_once $full_path;
            break;
        }
    }
}
spl_autoload_register( 'load_model' );

/**
 * Load a model
 *
 * @var string $model
 */
function load_response( $response ) {
    if ( !stristr( $response, 'Response' ) )
        return;

    // Form the model name, i.e., AccountListing to account-listing.php
    $response_file = substr( strtolower( preg_replace( '/(?<!-)[A-Z]/', '-$0', $response ) ) . '.php', 1 );

    $full_path = LIB_PATH . 'responses/' . $response_file;

    if ( is_file( $full_path ) )
        require_once $full_path;
}

spl_autoload_register( 'load_response' );

/**
 * Load an exception
 *
 * @var string $exception
 */
function load_exception( $exception ) {
    if ( !stristr( $exception, 'Exception' ) )
        return;

    // Form the model name, i.e., AccountListing to account-listing.php
    $exception_file = substr( strtolower( preg_replace( '/(?<!-)[A-Z]/', '-$0', $exception ) ) . '.php', 1 );

    $full_path = LIB_PATH . 'exceptions/' . $exception_file;

    if ( is_file( $full_path ) )
        require_once $full_path;
}

spl_autoload_register( 'load_exception' );