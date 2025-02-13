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

class TipsBooksRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/books';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint     
        $this->init();
    }

    public function register_routes_Books_Listing()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_books_list'),),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Books_Listing'));
    }

    public function get_books_list(WP_REST_Request $request)
    {

        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }

        $name = sanitize_text_field($request->get_param('bookId'));
        $bookcode = ($name) ? $name : "all";
        global $wpdb;
        $books_new = $wpdb->get_results("SELECT * FROM wp_tip_bible_book_store");
        $books_new = json_decode(json_encode($books_new), true);
        $testaments = [];
        foreach ( $books_new as $book ) {
            $testaments[$book['testament']][] = $book;
        }
        foreach ( $testaments as $testament => $books ) {
            $book_list = $testaments;
            foreach ( $books_new as $book ) {
                 if($book['testament'] == $testament && $book['name'] != 'Odes' && $book['name'] != '4 Maccabees'){
                    // $book_list[$book['abbr']] = $book['name'];
                     $post_object = (object) [
                        'books' => $book_list
                    ];
                 }
            }	    
        }
        $data[] = $post_object;
        return $data;
    }
}
$lps_rest_server = new TipsBooksRestAPI();
