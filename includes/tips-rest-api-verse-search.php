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

class List_Tips_Verse_Search_Rest_API extends WP_REST_Controller {

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct() {
        $this->api_namespace = '/v';
        $this->base = 'bible/find-verse';
        $this->api_version = '1';
        $this->required_capability = 'read';  
        $this->init();
    }

    public function register_routes_Verse_Detail() {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route( $namespace, '/' . $this->base, array(
            array( 'methods' => WP_REST_Server::READABLE, 'callback' => array( $this, 'find_verse_data' ), 'args' => [
                'verse'       => [
                    'type'     => 'string',
                    'required' => true,
                ],] ),
        )  );
    }

    public function init(){
        add_action( 'rest_api_init', array( $this, 'register_routes_Verse_Detail' ) );
    }

    public function find_verse_data(WP_REST_Request $request) {
        $headers = array_map('sanitize_text_field', getallheaders());
        $headers['url'] = !empty($headers['url']) ? esc_url($headers['url']) : esc_url(home_url());
        $search_string = sanitize_text_field($request->get_param('verse'));
        if (empty($search_string)) {
            return [
                (object) ['error' => 'No verse provided.']
            ];
        }

        $pattern = "/\b\d+:\d+\b/";
        $chapter_verse = [];
    
        if (preg_match($pattern, $search_string, $matches)) {
            $chapter_verse_string = $matches[0];
            $chapter_verse = preg_split("/[.:-]/", $chapter_verse_string);
            $book = strtolower(preg_replace("/\s?\b\d+:\d+\b/", '', $search_string));
        } else if (preg_match('/\s(\d+)$/', $search_string, $matches)) {
            $chapter_verse_string = $matches[0];
            $chapter_verse = preg_split("/[.:-]/", $chapter_verse_string);
            $book = strtolower(preg_replace('/\s(\d+)$/', '', $search_string));
        } else {
            $book = strtolower($search_string);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'books_abbrevation';
        $query = "SELECT book_code, book_abbrevation FROM $table_name";
        $results = $wpdb->get_results($query, OBJECT);

        if (!empty($results) && is_array($results)) {
            foreach ($results as $result) {
                $book_abbrevation = $result->book_abbrevation;
                $book_abbrevation_json = json_decode($book_abbrevation, true);
                if (in_array($book, $book_abbrevation_json)) {
                    $book = $result->book_code;
                    break; 
                }
            }
        }

        $args = [
            'taxonomy' => 'tip_verse',
            'hide_empty' => true,
            'meta_query' => [
                [
                    'key' => '_term_book_key',
                    'value' => $book
                ],
                [
                    'key' => '_term_chapter_number',
                    'value' => isset($chapter_verse[0]) ? $chapter_verse[0] : ''
                ],
            ],
        ];

        if (!empty($chapter_verse[1])) {
            $args['meta_query'][] = [
                'key' => '_term_verse_number',
                'value' => isset($chapter_verse[1]) ? $chapter_verse[1] : ''
            ];
        }

        $terms = get_terms($args);

        $post_object = [];
        if (empty($chapter_verse[1])) {
            $verse_new = [];
            foreach ($terms as $verse) {
                $term_id = $verse->term_id;
                $chapter_number = intval(get_term_meta($term_id, '_term_chapter_number', true));
                $verse_number = intval(get_term_meta($term_id, '_term_verse_number', true));
                $verse_new[$verse_number] = $term_id;
            }
            if (!empty($verse_new)) {
                $min = min(array_keys($verse_new));
                $term_id = $verse_new[$min];
                $term = get_term( $term_id );
                if ( ! is_wp_error( $term ) && $term ) {
                    $post_object[] = (object) [
                        'term_slug' => $term->slug
                    ];
                    return $post_object;
                }
            } else {
                return $this->handle_no_verses_found($book);
            }
        } 
        else {
            if (is_array($terms) && !empty($terms)) {
                $term_id = $verse_new[$min];
                $term = get_term( $terms[0]->term_id );
                if ( ! is_wp_error( $term ) && $term ) {
                    $post_object[] = (object) [
                        'term_slug' => $term->slug
                    ];
                    return $post_object;
                }
            }
        }
        return [(object) ['error' => "No verses found for the specified input."]];
    }
    private function handle_no_verses_found($book) {
        $books = tip_get_books();
        $books_a = array_column($books, null, 'abbr');
        $books_a = array_change_key_case($books_a);

        if (array_key_exists($book, $books_a)) {
            $abbr = ($book == 'epjer') ? 'lje' : $books_a[$book]['abbr'];
            $verses = get_terms([
                'taxonomy' => 'tip_verse',
                'hide_empty' => true,
                'meta_key' => '_term_book_key',
                'meta_value' => $abbr,
                'number' => 0
            ]);

            if (is_array($verses) && !empty($verses)) {
                return $this->build_chapter_response($verses, $books_a, $book);
            }
        }
        return [(object) ['error' => "The book you requested could not be found."]];
    }

    private function build_chapter_response($verses, $books_a, $book) {
        $chapters = [];
        foreach ($verses as $verse) {
            $term_id = $verse->term_id;
            $chapter_number = intval(get_term_meta($term_id, '_term_chapter_number', true));
            $verse_number = intval(get_term_meta($term_id, '_term_verse_number', true));
            $chapters[$chapter_number][$verse_number] = $verse;
        }

        ksort($chapters, SORT_NATURAL);
        $post_object = [];

        foreach ($chapters as $chapter_number => $by_verse) {
            $chapter_reference = $books_a[$book]['abbr'] . ' ' . strval($chapter_number);
            ksort($by_verse, SORT_NATURAL);
            $verse_slug = [];
            foreach ($by_verse as $verse_number => $verse) {
                $verse_slug[$verse->name] = $verse->slug; 
            }
            $post_object[] = (object) [
                'chapter_reference' => $chapter_reference,
                'verse_slug' => $verse_slug
            ];
        }
        return $post_object;
    }
}
$lps_rest_server = new List_Tips_Verse_Search_Rest_API();
