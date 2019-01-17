<?php

/**
 *  Project: Generic Importer
 *  File purpose here.
 *  @since version number.
 */

namespace GI;

use GI\Helpers\Settings;
use GI\Connectors\DBConnector;

/**
 * Description of GI
 *
 * @author Electric Studio
 */
class GI {
    private static $instance = NULL;
    
    public function fetchConnection( string $type, array $settings = [])
    {
        switch ($type)
        {
            case 'db':
                $connection = $this->fetchDBConnection( $settings );
                break;
            case 'csv': 
                $connection = new Connectors\CSVConnector();
                break;
            default:
                throw new Exception('Output connection not set correctly: ' . $type);
        }
        return $connection;
    }
    
    private function fetchDBConnection( array $settings)
    {
        $connection = new DBConnector($settings);
        return $connection;
    }
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new GI();
            
        }
        return self::$instance;
    }
}
