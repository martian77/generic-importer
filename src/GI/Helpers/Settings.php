<?php

/**
 *  Project: Generic Importer
 *  Contains a class to handle settings.
 *  @since version number.
 */

namespace GI\Helpers;

/**
 * Parses and deals with fetching the settings.
 *
 * @author Electric Studio
 */
class Settings {
    private static $settings = null;
    
    /**
     * Returns a specified setting. 
     * 
     * Specify levels separated by . e.g. 'output_location.type'
     * Leave setting name blank for all settings.
     * 
     * @since 0.0.1
     * @param string $setting_name Name of the setting to return. 
     * @return array | string
     * @throws \Exception if setting not found
     */
    public static function getSetting( $setting_name = '' )
    {
        if ( is_null( self::$settings ) ) {
            $settings_dir = dirname( __DIR__, 3);
            self::$settings = parse_ini_file( $settings_dir . '/settings.ini', true );
        }
        $settings = self::$settings;
        if ( ! empty( $setting_name ) ) {
            $setting_detail = explode('.', $setting_name);

            foreach ( $setting_detail as $detail ) {
                if ( isset( $settings[$detail])) {
                    $settings = $settings[$detail];
                }
                else {
                    throw new \Exception('Unknown setting selection: ' . $detail);
                }
            }
        }
        return $settings;
    }
}
