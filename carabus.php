<?php
/**
 * @package CarabusPlugin
 */
/*
Plugin Name: Carabus
Plugin URI: #
Description: Programming skills and coding standards
Version: 1.0
Author: Carabus Manuel Alexandru
Author URI: #
License: GPLv2 or later
Text Domain: carabus
*/

defined('ABSPATH') or die('Can\'t access this file!');

if( !class_exists('carabus') ) :
    
    class carabus {
        /** @var string The plugin version number */
        var $version = '1.0';
        
        /** @var array The plugin settings array */
        var $settings = array();
        
        /** @var array The plugin data array */
        var $data = array();
        
        /** @var array Storage for class instances */
        var $instances = array();

        function __construct() {
            // vars
            $version  = $this->version;
            $basename = plugin_basename( __FILE__ );
            $path     = plugin_dir_path( __FILE__ );
            $url      = plugin_dir_url( __FILE__ );
            $slug     = dirname($basename);

            $this->settings = array(
			
                // basic
                'name'				=> __('Carabus Plugin', 'carabus'),
                'version'			=> $version,
                            
                // urls
                'file'				=> __FILE__,
                'basename'			=> $basename,
                'path'				=> $path,
                'url'				=> $url,
                'slug'				=> $slug
            );

            // constants
            $this->define( 'CARABUS', 			true );
            $this->define( 'CARABUS_VERSION', 	$version );
            $this->define( 'CARABUS_PATH', 		$path );  
		
            // Include utility functions.
            include_once( CARABUS_PATH . 'includes/utility-functions.php');

            // Include activate and deactivate functions.
            include_once( CARABUS_PATH . 'includes/activate.php');
            include_once( CARABUS_PATH . 'includes/deactivate.php');

            load_plugin_textdomain('carabus', false, $slug . '/languages');

            $this->initialize();
        }

        function initialize() {
            
            register_activation_hook(__FILE__, array('Activate', 'activate'));
            register_deactivation_hook(__FILE__, array('Deactivate', 'deactivate'));
            add_action('init', array($this, 'init'), 10);
            add_action('init', array('Activate', 'check_flush_rewrite_rules_flag'), 11);
            add_action('init', array($this, 'register'), 12);
        }

        function init() {

            // create rest api
            add_action('rest_api_init', function(){

                // all users route
                register_rest_route('carabus/plugin', '/users', [
                    'methods' => 'GET',
                    'callback' => [$this,'get_users']
                ]);
                
                // specific user route
                register_rest_route('carabus/plugin', '/user(?:/(?P<id>\d+))?', [
                    'methods' => 'GET',
                    'callback' => [$this,'get_user'],
                    'args' => [
                        'id'
                    ]
                ]);
            });

            add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ){
                $wp_rewrite->rules = array_merge(
                    ['carabus/?$' => 'index.php?carabus_page=1'],
                    $wp_rewrite->rules
                );
            } );

            add_filter( 'query_vars', function( $query_vars ){
                $query_vars[] = 'carabus_page';
                return $query_vars;
            } );

            add_action( 'template_redirect', function(){
                $custom = intval( get_query_var( 'carabus_page' ) );
                if ( $custom ) {
                    if($this->has_setting('path')):

                        // Include the css and js only on the required page.
                        add_action ('wp_enqueue_scripts', array($this,'enqueue'));

                        include $this->get_setting('path') . 'templates/users-listing.php';

                        // stop execution
                        die;
                    endif;
                }
            } );
        }

        /**
        *  register
        *
        *  Registering the menu link and redirection link on plugin list.
        *
        *  @return	n/a
        */
        public function register() {
            add_action ('admin_menu', array( $this, 'add_admin_pages'));
            add_filter ('plugin_action_links_' . $this->get_setting('basename'), array( $this, 'settings_link'));
        }

        /**
        *  settings_link
        *
        *  Returns the link for the settings page.
        *
        *  @param	array $links
        *  @return	array
        */
        public function settings_link($links) {
            // add custom links
            $settings_link = '<a href="admin.php?page=' . $this->get_setting('slug') . '">' . __('Settings', 'carabus') . '</a>';
            array_push($links, $settings_link);

            return $links;
        }
        
        /**
        *  add_admin_pages
        *
        *  Registering the plugin settings page.
        *
        *  @return	n/a
        */
        public function add_admin_pages() {
            add_menu_page( 'Carabus Plugin', 'Carabus', 'manage_options', $this->get_setting('slug'), array( $this, 'admin_index'), 'dashicons-admin-settings', null);
            add_action('admin_init', array($this, 'carabus_general_page'));
        }

        /**
        *  carabus_general_page
        *
        *  Registering settings into the plugin settings page.
        *
        *  @return	n/a
        */
        function carabus_general_page() {  
            add_settings_section(  
                'api_links',
                'Api settings',
                '',
                $this->get_setting('slug')
            );

            add_settings_field(
                'api_link',
                'API Link',
                array($this, 'set_api_link'),
                $this->get_setting('slug'),
                'api_links',
                array(
                    'api_link'
                )  
            ); 

            register_setting($this->get_setting('slug'),'api_link', 'esc_attr');
        }

        /**
        *  set_api_link
        *
        *  If the API url is empty, we will set it automatically. If not, we will display it.
        *
        *  @return	n/a
        */
        function set_api_link($args) {  // Textbox Callback
            $option = get_option($args[0]);
            // delete_option($args[0]);
            if($option == false) {
                $option = 'https://jsonplaceholder.typicode.com/users';
                add_option($args[0], $option);
            }
            echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
        }


        public function admin_index() {
            // incluide admin template
            include_once( CARABUS_PATH . 'templates/admin.php');
        }

        /**
        *  get_users
        *
        *  Returns the whole list of the API response.
        *
        *  @return	json
        */
        public function get_users() {

            /** @var json Database saved json written by the plugin IF true, else, array from $apiResponse */
            if( false === ($users = get_transient('users_api'))) {

                /** @var array The API request response  */
                $apiResponse = wp_remote_request(get_option('api_link'), array(
                    'ssl_verify' => true
                ));
        
                if(is_wp_error( $apiResponse ) && WP_DEBUG == true){
                    printf(
                        'There was an ERROR in your request.<br />Code: %s<br />Message: %s',
                        $apiResponse->get_error_code(),
                        $apiResponse->get_error_message()
                    );
                }
                
                // Prepare the data
                $users = trim( wp_remote_retrieve_body( $apiResponse ) );
                
                // Convert output to JSON if is not
                if ( strstr( wp_remote_retrieve_header( $apiResponse, 'content-type' ), 'json' ) ){
                    $users = json_decode( $users );
                }

                set_transient('users_api', $users, DAY_IN_SECONDS);
            }

            return $users;
        }

        /**
        *  get_user
        *
        *  Return one user based on URL parameter 'id'.
        *
        *  @return	json
        */
        public function get_user() {

            /** @var int Get the user id from the request */
            $userId = $_GET['id'];

            /** @var json Database saved json written by the plugin IF true, else, array from $apiResponse */
            if( false === ($user = get_transient("user_api_$userId"))) {

                /** @var array The API request response  */
                $apiResponse = wp_remote_request(get_option('api_link') . '/' . $userId, array(
                    'ssl_verify' => true
                ));
        
                if(is_wp_error( $apiResponse ) && WP_DEBUG == true){
                    printf(
                        'There was an ERROR in your request.<br />Code: %s<br />Message: %s',
                        $apiResponse->get_error_code(),
                        $apiResponse->get_error_message()
                    );
                }
                
                // Prepare the data
                $user = trim( wp_remote_retrieve_body( $apiResponse ) );
                
                // Double check the Curl response.
                if(strlen($user) == 0) {
                    $apiResponse = wp_remote_request(get_option('api_link') . '/' . $userId);
                    $user = trim( wp_remote_retrieve_body( $apiResponse ) );
                }

                // Convert output to JSON if is not
                if ( strstr( wp_remote_retrieve_header( $apiResponse, 'content-type' ), 'json' ) ){
                    $user = json_decode( $user );
                }

                set_transient("user_api_$userId", $user, DAY_IN_SECONDS);
            }

            return $user;
        }

        /**
        *  define
        *
        *  Defines constants.
        *
        *  @param	string $name
        *  @return	boolean
        */
        protected function define( $name, $value = true ) {
            if( !defined($name) ) {
                define( $name, $value );
            }
        }


        /**
        *  has_setting
        *
        *  Returns true if has setting.
        *
        *  @param	string $name
        *  @return	boolean
        */

        protected function has_setting( $name ) {
            return isset($this->settings[ $name ]);
        }
        
        /**
        *  get_setting
        *
        *  Returns a setting.
        *
        *  @param	string $name
        *  @return	mixed
        */
        public function get_setting( $name ) {
            return isset($this->settings[ $name ]) ? $this->settings[ $name ] : null;
        }
        
        /**
        *  update_setting
        *
        *  Updates a setting.
        *
        *  @param	string $name
        *  @param	mixed $value
        *  @return	true
        */
        function update_setting( $name, $value ) {
            $this->settings[ $name ] = $value;
            return true;
        }
        
        /**
        *  enqueue
        *
        *  Providing the assets used in the plugin frontend page.
        *
        *  @return	n/a
        */
        function enqueue() {
            wp_enqueue_style('carabus-table-style', plugins_url('assets/data-tables/datatables.min.css', __FILE__), array(), '1.0');
            wp_enqueue_script('carabus-table-script', plugins_url('assets/data-tables/datatables.min.js', __FILE__), array('jquery'), '1.0');
            
            wp_register_script( "localize-rest-url", '', array('jquery'));
            wp_localize_script('localize-rest-url', 'carabusAjax', array(
                'restURL' => rest_url(),
                'nonce' => wp_create_nonce('wp_rest')
            ));

            wp_enqueue_script('localize-rest-url');
        }
    }
    
    function carabus() {

        // globals
        global $carabus;
                
        // initializez
        if( !isset($carabus) ) {
            $carabus = new carabus();
        }
    
        // return
        return $carabus;
    }

    // initialize
    carabus();

endif;