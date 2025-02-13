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

class TipsTreeViewRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/tree-view';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint
        $this->init();
    }

    public function register_routes_Tree_View()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_tree_view'), 'args' => [
                'termId'       => [
                    'type'     => 'numeric',
                    'required' => true,
                ],
            ]),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Tree_View'));
    }
    public function get_tree_view(WP_REST_Request $request)
    {
        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }

        $term_id = sanitize_text_field($request->get_param('termId'));
        $all_nodes = Tips_Common::get_tip_term_get_back_translations($term_id);
        
        if ($all_nodes) {
            return $all_nodes;
        } else {
            return 'No tree view to Show';
        }
    }
}
$lps_rest_server = new TipsTreeViewRestAPI();
