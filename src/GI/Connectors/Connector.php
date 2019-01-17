<?php

/*
 *  Project: Generic Importer
 *  Generic functions for connectors of any kind.
 *  @since version number.
 */

namespace GI\Connectors;

/**
 * Specifies the interface for Connectors. 
 * 
 * @author Electric Studio
 */
interface Connector {
    public function writeData( string $outputName, array $data, array $outputSettings);
}
