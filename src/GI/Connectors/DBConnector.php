<?php

/**
 *  Project: Generic Importer
 *  Contains the connector class for the DB. 
 *  @since 0.0.1
 */

namespace GI\Connectors;

/**
 * Handles connecting to and getting data from a database.
 *
 * @author Electric Studio
 */
class DBConnector implements Connector {
    
    private $user;
    private $password;
    private $db_name;
    private $dsn;
    
    /**
     * PDO Connection. 
     * @since 0.0.1
     * @var PDO 
     */
    private $connection = null;
    
    /**
     * The retry attempts.
     * @since 0.0.1
     * @var int 
     */
    private $retry_attempts = 0;
    
    /**
     * Maximum number of retries before stopping.
     * @since 0.0.1
     * @var int 
     */
    private $max_retry = 3;
    
    /**
     * Construct connector.
     *
     * @param array       $settings  Database connection settings.
     */
    public function __construct( array $settings )
    {

        $this->user = $settings['db_user'];
        $this->password = $settings['db_password'];
        $this->db_name = $settings['db_name'];

        $connection_type = empty( $settings['connection_type'] ) ? 'mysql:' : $settings['connection_type'] . ':';
        $driver = empty( $settings['driver'] ) ? '' : 'driver=' . $settings['driver'] . ';';
        $host = empty( $settings['host'] ) ? 'host=localhost;' : 'host=' . $settings['host'] . ';';
        $port = empty( $settings['port'] ) ? 'port=3306;' : 'port=' . $settings['port'] . ';';
        $dbname = ( 'odbc' == $settings['connection_type'] ) ? 'dsn=' . $settings['db_name'] . ';' : 'dbname=' . $settings['db_name'] . ';';
        $charset = empty( $settings['charset'] ) ? 'charset=utf8mb4;' : 'charset=' . $settings['charset'] ;

        $this->dsn = $connection_type . $driver . $host . $port . $dbname . $charset;
    }
    
    /**
     * Write data to this database.
     * 
     * @since 0.0.1
     * @param string $outputName    The name of table to write to.
     * @param array $data           Associative array of the data with the column names as the keys.
     * @param array $outputSettings Associative array - set ['orUpdate'] to true to make update on duplicate key.
     * @return bool 
     */
    public function writeData( string $outputName, array $data, array $outputSettings)
    {
        $columns = array_keys( $data[0] );
        $column_names = implode(', ', $columns );
        $query = 'INSERT INTO ' . $outputName . ' (' . $column_names . ') VALUES ';
        $values = array();
        $placeholders = array();

        foreach ( $data as $item ) {
          $values = array_merge( $values, array_values( $item ) );
          $value_placeholders = array_fill( 0, count($item), '?' );
          $placeholders[] = '(' . implode(', ', $value_placeholders ) .')';
        }
        $complete_query = $query . implode( ', ', $placeholders );
        
        if ( ! empty($outputSettings['orUpdate'] ) )
        {
          $update_columns = array_map( function($value) {
            return $value . ' = values(' . $value . ')';
          },
          $columns);
          $update = ' ON DUPLICATE KEY UPDATE ' . implode(', ', $update_columns);
          $complete_query = $complete_query . $update;
        }
//        try {
            $this->execute( $complete_query, $values );
//        } catch ( Exception $e ) {
//            $this->handle_error( $e->getMessage() );
//            return false;
//        }
        return true;
    }
    
    /**
     * Executes the query.
     *
     * If there is no custom data, this just runs a query. Otherwise the $query
     * is prepared before execution.
     *
     * @since 0.0.1
     * @throws Exception
     * @param  string $query The query to be executed.
     * @param  array  $data  The data that the query requires.
     * @return PDOStatement  A prepared statement and associated results.
     */
    private function execute( $query, $data = array() ) {
        $this->open_connection();

        if ( ! empty( $data ) ) {
          $stmt = $this->connection->prepare( $query );
          $stmt->execute( $data );
        }
        else {
          $stmt = $this->connection->query( $query );
        }
        if ( ! empty( $stmt ) ) {
            // There may be an error returned here.
            if ( ! empty( $stmt->errorCode() ) ) {
                if ( '2006' == $stmt->errorInfo()[1] && $this->retry_attempts < $this->max_retry ) {
                    $this->retry_attempts ++;
                    $this->open_connection();
                    $this->execute( $query, $data );
                }
                else {
                  $message = implode(': ', $stmt->errorInfo() );
                  throw new Exception('PDO SQL Error: ' . $message . ': Retries: ' . $this->retry_attempts);
                }
            }
        }

        $this->retry_attempts = 0;
        return $stmt;
    }
    
    /**
     * Opens the pdo connection to the database.
     *
     * @since 0.0.1
     * @return void
     */
    private function open_connection() {
        $this->connection = NULL;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
        );
        $this->connection = new PDO( $this->dsn, $this->user, $this->password, $options );
    }
}
