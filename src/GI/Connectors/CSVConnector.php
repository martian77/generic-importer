<?php

/**
 *  Project: Generic Importer
 *  File purpose here.
 *  @since version number.
 */

namespace GI\Connectors;

/**
 * Description of CSVConnector
 *
 * @author Electric Studio
 */
class CSVConnector implements Connector {
    /**
     *
     * @var 
     */
    private $fileHandle = null;
    
    /**
     * 
     * @param string $outputName    Filename to write to.
     * @param array $data           Data to write to.
     * @param array $outputSettings Additional settings. E.g. to append, add headings, etc.
     */
    public function writeData( string $outputName, array $data, array $outputSettings)
    { 
        // Check we've been sent data. 
        if ( empty( $data ) ) {
            throw new \Exception('No data passed to write');
        }
        // Work out whether we're writing or appending. 
        if ( ! empty( $outputSettings['append'] ) ) {
            $operation = 'append';
        }
        else {
            $operation = 'write';
        }
        // Sort out delimiter etc. 
        $delimiter = empty($outputSettings['delimiter']) ? ',' : $outputSettings['delimiter'];
        $this->openFile($outputName, $operation);
        // Are we writing headers? No reason to link this to append or write. 
        if ( ! empty($outputSettings['write_headers'] ) ) {
            $headers = array_keys($data[0]);
            fputcsv($this->fileHandle, $headers, $delimiter);
        }
        foreach ( $data as $row ) {
            fputcsv($this->fileHandle, $row, $delimiter);
        }
        $this->closeFile();
    }
    
    /**
     * Read data from a CSV file.
     * 
     * @param string $inputName
     * @param array $inputSettings
     * @return array
     */
    public function readData( string $inputName, array $inputSettings)
    {
        $delimiter = empty($inputSettings['delimiter']) ? ',' : $inputSettings['delimiter'];
        $readheaders = ! empty( $inputSettings['read_headers']);
        
        // Get the line endings set for macs. 
        ini_set('auto_detect_line_endings', '1');
        $this->openFile($inputName, 'read');
        if ( $readheaders ) {
            $headers = fgetcsv( $this->fileHandle, 0, $delimiter );
        }
        
        $data = [];
        while ( FALSE !== $row = fgetcsv( $this->fileHandle, 1000, $delimiter ) ) {
            if ( $readheaders ) {
                $data[] = $this->mergeHeaders($headers, $row);
            }
            else {
                $data[] = $row;
            }
        }
        
        $this->closeFile();
        
        return $data;
    }
    
    /**
     * Opens a CSV file.
     * 
     * @param string $filename
     * @param string $operation
     * @throws \Exception
     */
    private function openFile( string $filename, string $operation)
    {
        // First stop any open connection. 
        if ( ! is_null( $this->fileHandle)) {
            $this->close_file();
        }
        // Check the requested file is not a directory.
        if ( is_dir($filename) ) {
            throw new \Exception( 'Requested file ' . $filename . ' is a directory.');
        }
        
        // I think I want to specify three operations: 
        // read, write, append. 
        $operations = [
            'read' => 'r', // Opens a file for reading. 
            'write' => 'x', // Creates a file for writing, warns if exists. 
            'append' => 'a', // Opens/creates a file for writing and appends.
        ];
        
        $fileHandle = fopen( $filename, $operations[$operation]);
        if ( false === $fileHandle ) {
            throw new \Exception( 'Failed to open file ' . $filename . ' for ' . $operation);
        }
        $this->fileHandle = $fileHandle;
    }
    
    /**
     * Closes the CSV file.
     */
    private function closeFile( )
    {
        if ( ! empty( $this->fileHandle) ) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }
    
    /**
     * Merge the header values with the row. 
     * 
     * @param array $headers    Header values.
     * @param array $row        Row data values.
     * @return associative array.
     */
    private function mergeHeaders( $headers, $row )
    {
        $output_row = [];
        foreach( $headers as $key => $header ) {
            $output_row[$header] = $row[$key];
        }
        return $output_row;
    }
}
