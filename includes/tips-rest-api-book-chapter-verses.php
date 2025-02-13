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

class TipsBookChapterVersesRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/chapterverses';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint     
        $this->init();
    }

    public function register_routes_Chapters_Listing()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_chapters_list'), 'args' => [
                'bookId'       => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ]),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Chapters_Listing'));
    }
    public function get_chapters_list(WP_REST_Request $request)
    {
        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }

        // Jigisha:c-metric:start code

        $book_code = sanitize_text_field($request->get_param('bookId'));
        $chapter_no = sanitize_text_field($request->get_param('chapterId'));
        $verses = get_terms( array(
		      		'taxonomy' => 'tip_verse',
		      		'hide_empty' => true,
					'meta_key' => '_term_verse_number',
					'orderby' => 'meta_value_num', //if the meta_key (population) is numeric use meta_value_num instead
                    'order' => 'ASC', //setting order direction
                	'meta_query' => array(
                        'relation' => 'AND',
                            array(
                                'key' => '_term_book_key',
                                'value' => $book_code,
                            ),
                            array(
                                'key' => '_term_chapter_number',
                                'value' => $chapter_no,
                            )
                        ),
					'number' => 0
        ) );
        $chapter_number = [];
        foreach ( $verses as $verse_number => $verse ) {
            $term_id = $verse->term_id;
            $verse_number  = intval( get_term_meta( $term_id, '_term_verse_number', true ) ); 
            $term_name = get_term( $term_id )->name;
            $term_url = get_term_link( $verse->term_id );
            $verse_url = sprintf("%s", get_term_link($term_id), sanitize_text_field($verse->name));
                $url = str_replace('\/', '/', $verse_url); 
                $exploded_url = explode('/', $url);
                $versename = end($exploded_url);
                if (empty($versename) && count($exploded_url) > 1) {
                    $versename = prev($exploded_url);
                }
                $verse_url = "/tip_verse/?verseId=".$versename."/";
                
            $chapter_number[$term_name] = esc_url($verse_url);
        }
        $post_object['chapters'] =  $chapter_number;

        $data = $post_object;
        return $data;
    }

}
$lps_rest_server = new TipsBookChapterVersesRestAPI();
