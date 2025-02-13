<?php
/**
 * Defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://c-metric.com
 * @since      1.0.0
 *
 * @package    Tips_Rest_API
 * @subpackage Tips_Rest_API/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class TipsLanguagesNumbersRestAPI extends WP_REST_Controller {

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct() {
        $this->api_namespace = '/v';
        $this->base = 'bible/tips_languages_count';
        $this->api_version = '1';
        $this->required_capability = 'read'; // Minimum capability to use the endpoint
        $this->init();
    }

    public function register_routes_tips_languages_numbers() {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route( $namespace, '/' . $this->base, array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'tips_languages_numbers' ),
            ),
        ));
    }

    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes_tips_languages_numbers' ) );
    }

    public function tips_languages_numbers( WP_REST_Request $request ) {
        $headers = array_map( 'sanitize_text_field', getallheaders() );
        if ( ! isset( $headers['url'] ) || empty( $headers['url'] ) ) {
            $headers['url'] = home_url();
        }

        $total = array(
            'languages' => wp_count_terms( 'tip_language', array( 'hide_empty' => true ) ),
        );

        $post_object = (object) array(
            'languages'  => (object) array(
                'languages_count' => $total['languages'],
            )
        );
        
        $data[] = $post_object;
        return $data;
    }
}

$lps_rest_server = new TipsLanguagesNumbersRestAPI();
