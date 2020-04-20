<?php
/**
 * @package CarabusPlugin
 */

class Activate {

    function __construct() {
		
        /* Do nothing here */
        
    }
    
    public static function activate() {
        if ( ! get_option( 'carabus_flush_rewrite_rules_flag' ) ) {
            add_option( 'carabus_flush_rewrite_rules_flag', true );
        } 
    }

    public static function check_flush_rewrite_rules_flag() {
        if ( get_option( 'carabus_flush_rewrite_rules_flag' ) ) {
            flush_rewrite_rules();
            delete_option( 'carabus_flush_rewrite_rules_flag' );
        }
    }
}