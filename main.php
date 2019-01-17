<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// Pull in the composer autoloader. 
require_once 'vendor/autoload.php';

use GI\Helpers\Settings;

function run() 
{
    $gi = GI\GI::getInstance();
    $output_connection = $gi->fetchConnection( Settings::getSetting('output_location.type'));
    $data = [
        [
            'test1' => 'output1',
            'test2' => 'output2', 
            'test3' => 'output3',
        ],
        [
            'test1' => 'row1',
            'test2' => 'row2', 
            'test3' => 'row3',
        ],
    ];
    // These settings can vary depending on the type of connector. 
    $write_settings = [
        'write_headers' => false,
        'append' => true,
    ];
    $output_connection->writeData( __DIR__ . '/test.csv', $data, $write_settings);
}

run();