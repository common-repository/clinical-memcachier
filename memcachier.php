<?php
defined( 'ABSPATH' ) OR exit;
/*
    Plugin Name: Clinical Memcachier
    Plugin URI: http://www.codeclinic.de/plugins
    Description: Plugin for implementing (NoASL) memcached as a service by Memcachier.
    Author: Code Clinic KreativAgentur
    Version: 0.4
    Author URI: http://www.codeclinic.de
    Forked from: https://github.com/hubertnguyen/memcachier
*/

if(!class_exists('Clinical_Memcachier_Plugin'))
{
    class Clinical_Memcachier_Plugin
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {   
            //performs the full activation after the activatio
            add_action('admin_init', array($this, 'do_ccmc_activation'));
            //adds the notice(s) to admin
            add_action('admin_notices', array($this, 'my_plugin_admin_notices'));
            /* hook updater to init */
            add_action( 'init', 'Clinical_Memcachier_Updater_init' );
            
        } // END public function __construct
    
        /**
         * Activate the plugin
         */
        public static function activate()
        {
            //if ( ! current_user_can( 'activate_plugins' ) )
                //return;
            update_option('clinical-memcachier-activate', '1', false);
        } // END public static function activate
    
        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            //if ( ! current_user_can( 'activate_plugins' ) )
                //return;
            //failsafe: switch off object-cache if fail to delete
            //update_option('clinical-memcachier-activate','0', 'false');

            //delete the object-cache
            $existing_file = WP_CONTENT_DIR.'/object-cache.php';
            unlink(realpath($existing_file));


            //flush the cache
            wp_cache_flush();
            //remove options   
            delete_option('clinical-memcachier-activate');
        } // END public static function deactivate
        
        /**
        * Full set of activation tasks
        */
        public function do_ccmc_activation(){
            //check if just activated
            if(get_option('clinical-memcachier-activate') == '1'){ 
                $source_file = plugin_dir_path( __FILE__ ) .'object-cache-memcachier.txt'; //our object-cache file
                $existing_file = WP_CONTENT_DIR.'/object-cache.php'; //Destination for our new object-cache.php file
                $newfile = $existing_file; //the existing object-cache.php file if it exists
                $backupfile = WP_CONTENT_DIR.'/object-cache-BACKUP.php'; //the backup we will create from any existing object-cache.php file

                if(file_exists($existing_file)){
                    //move the existing object-cache file into a backup
                    rename($existing_file,$backupfile);
                }
                //replace it with our new object-cache
                copy($source_file, $newfile);  
            }
            
        }// END function do_ccmc_activation
    
        
        /**
        * Admin notifications
        */
        public function my_plugin_admin_notices() {
          //if ($notices = get_option('my_plugin_deferred_admin_notices')) {
            //check the credentials
            //global $memcached_servers;
            if(!defined('MEMCACHIER_USER') && !defined('MEMCACHIER_PASSWORD')){
                //inform admin to now add Memcachier constants to wp-config
                
                //build the credentials messages
                $credentialMessage = "<strong>MEMCACHIER NOTICE:</strong><br>To complete the Clinical Memcachier installation, please add the following code to you <strong>wp-config.php</strong> file located in the root of your WordPress installation.<br>Be sure to <strong>place it on the first line</strong> after the opening php tag '<strong>&lt;?php</strong>' and replace the dummy credentials with your own.<br><br>";
                $credentialMessage .= "/**** MEMCACHIER CREDENTIALS ****/<br>"; 
                $credentialMessage .= "global \$memcached_servers;<br>";  
                $credentialMessage .= "\$memcached_servers = array('default' => array('YOUR_SERVER:PORT'));<br>"; 
                $credentialMessage .= "define('MEMCACHIER_USER', 'YOUR_USERNAME');<br>";  
                $credentialMessage .= "define('MEMCACHIER_PASSWORD', 'YOUR_PASSWORD');<br>"; 
                $credentialMessage .= "/********************************/"; 
                
                //display notice
                echo "<div class='update-nag'><p>".$credentialMessage."</p></div>";
            }
            //check if just activated
            if(get_option('clinical-memcachier-activate') == '1'){ 
                //build the activation message
                $activationMessage = "<strong>Clinical Memcachier:</strong> Memcachier has been activated. If you had a previous verion of 'object-cache.php', we've made a backup and saved it in the same folder.";
                
                //display notice
                echo "<div class='updated'><p>".$activationMessage."</p></div>";

                //enable the object-cache.php
                update_option('clinical-memcachier-activate','0');
            }
        }//END my_plugin_admin_notices
        
        
        
        /**
         * Load and Activate Plugin Updater Class.
         */
        function Clinical_Memcachier_Updater_init() {

            /* Load Plugin Updater */
            require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );

            /* Get values from titan options framework */
            $titan = TitanFramework::getInstance( 'clinical_cms' );
            $myValue = $titan->getOption( 'clinical_activation_code' );

            /* Updater Config */
            $config = array(
                'base'      => plugin_basename( __FILE__ ), //required
                'dashboard' => false,
                'username'  => false,
                'key'       => $myValue,
                'repo_uri'  => 'http://www.codeclinic.de/',
                'repo_slug' => 'clinical-memcachier',
            );

            /* Load Updater Class */
            new Clinical_Memcachier_Updater( $config );
        }
        
    } // END class WP_Plugin_Template
} // END if(!class_exists('WP_Plugin_Template'))

if(class_exists('Clinical_Memcachier_Plugin'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('Clinical_Memcachier_Plugin', 'activate'));
    register_deactivation_hook(__FILE__, array('Clinical_Memcachier_Plugin', 'deactivate'));

    // instantiate the plugin class
    $clinical_memcachier_plugin = new Clinical_Memcachier_Plugin();
}
               
?>