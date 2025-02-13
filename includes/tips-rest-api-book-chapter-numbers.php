<?php

/**
 * this defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       c-metric.com
 * @since      1.0.0
 *
 * @package    tips-rest-api
 * @subpackage tips-rest-api/includes
 */

class TipsBookChapterNumbersRestAPI extends WP_REST_Controller {
    
    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;
    

    public function __construct() {
        $this->api_namespace = '/v';
        $this->base = 'bible/chapternumbers';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint     
        $this->init();
    }

    public function register_routes_Chapters_Numbers_Listing() {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route( $namespace, '/' . $this->base, array(
            array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'get_chapters_number_list' ), 'args' => [
                'bookId'       => [
                    'type'     => 'string',
                    'required' => true,
                ],]),
        )  );
    }
    // Register our REST Server
    public function init(){
        add_action( 'rest_api_init', array( $this, 'register_routes_Chapters_Numbers_Listing' ) );
    }
    public function get_chapters_number_list( WP_REST_Request $request ){
        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }
            
            // TODO: Run real code here.

            $book_code = sanitize_text_field($request->get_param( 'bookId' ));
             $verses = get_terms( array(
		      		'taxonomy' => 'tip_verse',
		      		'hide_empty' => true,
					'meta_key' => '_term_book_key',
                	'meta_query' => array(
                		array(
                			'key' => '_term_book_key',
                			'value' => $book_code,
                		)
                	),
					'number' => 0
		    ) );
            
                     
            $chapter_number = [];
                if ( is_array( $verses ) && !empty( $verses ) ) {
					foreach ( $verses as $verse ) {
						$term_id = $verse->term_id;
						$chapter_number[] = intval( get_term_meta( $term_id, '_term_chapter_number', true ) );
					}
                }
                if(!empty($chapter_number)){
                    sort($chapter_number);
                }
                
            
                $post_object['chapter_numbers'] =  array_unique($chapter_number);

                $data = $post_object;
                return $data;         
    }
}
$lps_rest_server = new TipsBookChapterNumbersRestAPI();

