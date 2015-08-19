<?php
/*
Plugin Name: Serializer
Plugin URI:
Description: The best way to replace serialized values stored in the database.
Version: 1.0.0
Author: Timothy Wood (@codearachnid)
Author URI: http://www.codearachnid.com
Text Domain: serializer
Domain Path: /languages
Credits:
	* Background patterns from subtlepatterns.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;

global $the_serializer;
add_action( 'plugins_loaded', 'the_serializer_loaded' );

function the_serializer_loaded() {
    global $the_serializer;
    $the_serializer = new The_Serializer();
}

if ( !class_exists('The_Serializer', false) ) {
    class The_Serializer{
        public function __construct() {
			// load_plugin_textdomain( 'fgfss', null, basename(dirname( __FILE__ )) . '/languages' );
			add_action( 'admin_menu', array($this, 'menue') );
            add_action( 'wp_ajax_the_serializer_table_list', array($this, 'ajax_table_list') );
            add_action( 'wp_ajax_the_serializer_columm_list', array($this, 'ajax_column_list') );
            add_action( 'wp_ajax_the_serializer_run', array($this, 'ajax_run') );

		}

        public function menue(){
            add_management_page( 'Serializer', 'Serializer', 'manage_options', 'the-serializer', array($this, 'admin_page') );
        }

        public function admin_page(){
            include 'template.php';
        }

        public function ajax_table_list(){
            if ( defined( 'DOING_AJAX' ) ){
                echo json_encode( $this->get_table_list() );
            }
            wp_die();
        }

        public function ajax_column_list(){
            if ( defined( 'DOING_AJAX' ) ){
                echo json_encode( $this->get_column_list( !empty( $_POST['table'] ) ? $_POST['table'] : null ) );
            }
            wp_die();
        }

        public function ajax_run(){
            $args = wp_parse_args( $_POST, array('table'=>'','column'=>'','find'=>'','replace'=>'') );
            if ( defined( 'DOING_AJAX' ) && !empty( $args['table'] ) && !empty( $args['column'] )){
                echo json_encode( $this->run_serializer(  $args['table'], $args['column'], $args['find'], $args['replace'] ) );
            }
            wp_die();
        }

        public function run_serializer( $table = '', $column = '', $find = '', $replace = '' ){
            global $wpdb;
            $wpdb->show_errors();
            $status = true;
            $i = 0;
            $total = 0;
			$matches = $this->find_items_to_replace($table,$column,$find);
            foreach( $matches as $match ){
                $fixed_string = $this->fix_serialized($match->{$column}, $find, $replace);
                if( $fixed_string != $match->{$column} ){
                    $where = $match;
                    $fixed_data[$column] = $fixed_string;
                    unset($where->{$column});
                    if( $wpdb->update( $table, $fixed_data, $where ) !== FALSE ){
                        $i++;
                    }
                    // echo $fixed_string . "\r\n";
                    // print_r($where);
                    // echo "\r\n";
                }
            }
            print_r($wpdb->queries);
            echo "\r\n";

            $total=sizeof($matches);
            return (object) array('status'=>$status,'count'=>$i,'total'=>$total);
        }

        public function find_items_to_replace( $table = '', $column = '', $find = '' ){
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM `{$table}` WHERE `{$column}` LIKE '%{$find}%'");
        }

        public function get_table_list(){
            global $wpdb;
            return $wpdb->get_col( 'SHOW TABLES;' );
        }

        public function get_column_list( $table = '' ){
            global $wpdb;
            $response = !empty($table) ? $wpdb->get_col( 'SHOW COLUMNS FROM ' . $table . ';' ) : array();
            return $response;
        }

        function fix_serialized( $haystack, $needle, $replace){
            $fixed = str_replace( $needle, $replace, $haystack );
            return $this->recalculate_length( $fixed );
        }

        function recalculate_length( $string ) {
            $string = preg_replace_callback('/\bs:(\d+):"(.*?)"/', array($this, 'fix_string_length'), $string);
           return $string;
        }

        function fix_string_length( $matches ){
			return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '"';
        }

    }
}
