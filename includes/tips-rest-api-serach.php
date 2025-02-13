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

if (!defined("ABSPATH")) {
    exit(); // Exit if accessed directly.
}

class TipsRestAPISearch extends WP_REST_Controller
{
    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = "/v";
        $this->base = "bible/search";
        $this->api_version = "1";
        $this->required_capability = "read"; // Minimum capability to use the endpoint
        $this->init();
    }

    public function register_routes_tips_search()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, "/" . $this->base, [
            [
                "methods" => WP_REST_Server::READABLE,
                "callback" => [$this, "tips_search"],
                "args" => [
                    "Id" => [
                        "type" => "string",
                        "required" => true,
                    ],
                ],
            ],
        ]);
    }

    public function init()
    {
        add_action("rest_api_init", [$this, "register_routes_tips_search"]);
    }

    public function tips_search(WP_REST_Request $request)
    {
        $headers = array_map("sanitize_text_field", getallheaders());
        if (!isset($headers["url"]) || empty($headers["url"])) {
            $headers["url"] = home_url();
        }
        global $wpdb;

        $search_term = sanitize_text_field($request->get_param("Id"));
        $per_page = sanitize_text_field($request->get_param('per_page'));
        $paged = sanitize_text_field($request->get_param('paged'));
        $args = [
            "post_type" => ["tip_story"], // Add your custom post types here
            "s" => $search_term,
            "post_status" => "publish",
            'posts_per_page' => 8,
            'paged' => ($paged ? $paged : 1),
        ];

        // Define the prefixes to exclude
        $excluded_prefixes = ["complete verse", "Translation commentary on"];

        // Generate SQL conditions to exclude titles with specified prefixes
        $conditions = [];
        foreach ($excluded_prefixes as $prefix) {
            $conditions[] =
                $wpdb->posts .
                ".post_title NOT LIKE '" .
                esc_sql($wpdb->esc_like($prefix)) .
                "%'";
        }

        // Filter to modify the WHERE clause of the query
        add_filter(
            "posts_where",
            function ($where, $wp_query) use ($conditions) {
                if (!empty($conditions)) {
                    $where .= " AND (" . implode(" AND ", $conditions) . ")";
                }
                return $where;
            },
            10,
            2
        );

        $query = new WP_Query($args);
        $posts = [];

        if ($query->have_posts()) {
            $data = array();
            while ($query->have_posts()) {
                $query->the_post();
                $id = sanitize_text_field(get_the_ID());
               
                    $post = get_post($id);
                    $link = get_permalink($id);
                    $content = $post->post_content;
                    $content = apply_filters('the_content', $content);
                    $content = str_replace(']]>', ']]&gt;', $content);
                    $site_url = esc_url($headers["url"]);
                    $content1 = preg_replace('/\/tip_language\//','#nonclick/',$content);
                    $content2 = Tips_Common::replace_specific_url_in_content($content1);
                    $content2 = self::limit_content_by_words_with_links($content2, 40); 
                
                    $taxonomies = is_single() || ($query->post_count == 1) ? ['Language' => 'tip_language', 'Verse' => 'tip_verse'] :  ['Language' => 'tip_language'];
                    $taxonomies_list = array();
                    foreach ($taxonomies as $key => $taxonomy) {
                        if (is_tax($taxonomy)) {
                            continue;
                        }
                        $terms = get_the_terms(sanitize_text_field($id), sanitize_text_field($taxonomy));
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                $term_link = get_term_link(sanitize_text_field($term), sanitize_text_field($taxonomy));
                                $both_titles = Tips_Common::get_hover_title(sanitize_text_field($id)); //main title and hover title
                                $maintitle = implode(", ", $both_titles['main_title']);
                                $title_link = $headers['url']."/detail/?".$post->post_name;
                            }
                        }
                    }
                    
                $post_object = (object) [
                        'id' => sanitize_text_field($id),
                        'title' => (object) ['rendered' => sanitize_text_field($post->post_title), 'hover_title' => $both_titles['hover_title'],'title_link' => $title_link],
                        'content' => (object) ['rendered' => $content2],
                    ];
                $data['storyData'][] = $post_object;
            }
        }
        wp_reset_postdata();
        remove_filter("posts_where", "modify_posts_where");
        
        $base = 'search';
        $image_url = plugins_url('images/prev_icon.png', dirname(__FILE__));
        $data['pagination'][] = (object) ['total_pages' => $query->max_num_pages, 'image_url' => $image_url,'paged' => $paged,'base' => $base];
        return $data;
    }
   // Function to limit content by words while preserving links
    public static function limit_content_by_words_with_links($content, $limit) {
        // Strip all tags except <a>
        $content = strip_tags($content, '<a>');
    
        // Convert content to array of words
        $words = preg_split("/[\s,]+/", $content);
    
        // Check if words count is less than limit
        if (count($words) <= $limit) {
            return implode(' ', $words);
        }
    
        // Otherwise, limit the array to the specified number of words
        $limited_words = array_slice($words, 0, $limit);
    
        // Implode limited words back into a string
        $limited_content = implode(' ', $limited_words);
    
        // Restore closing tags for links
        $limited_content = preg_replace('/(<a [^>]+>)/i', '$1', $limited_content);
        if (count($words) > $limit) {
            $limited_content .= ' ...';
        }

        return $limited_content;
    }
}

$lps_rest_server = new TipsRestAPISearch();
