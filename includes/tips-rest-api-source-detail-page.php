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
class TipsSourceDetailRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/tip_source';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint
        $this->init();
    }

    public function register_routes_Source_Detail()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_source_details'), 'args' => [
                'sourceId'       => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ]),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Source_Detail'));
    }
    
    public function get_source_details(WP_REST_Request $request)
    {
            $headers = array_map('sanitize_text_field', getallheaders());
            if (!isset($headers['url']) || $headers['url'] === "") {
                $headers['url'] = home_url();
            }

            $slug = sanitize_text_field($request->get_param('sourceId'));
            $tip_order = sanitize_text_field($request->get_param('order'));
            $per_page = sanitize_text_field($request->get_param('per_page'));
            $paged = sanitize_text_field($request->get_param('paged'));
            $args = array(
                'post_type' => 'tip_story',
                'post_status' => 'publish',
                'posts_per_page' => 8,
                'paged' => ($paged ? $paged : 1),
                'meta_key' => '_priority',
                'orderby' => 'meta_value_num',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'tip_source',
                        'field' => 'slug',
                        'terms' => $slug
                    )
                )
            );
            $taxonomies = get_taxonomies();
            if (!empty($taxonomies)) {
                foreach ($taxonomies as $tax_type_key => $taxonomy) {
                    if ($term_object = get_term_by('slug', $slug, $taxonomy)) {
                        break;
                    }
                }
            }

            $source_data = [];
            if ($term_object) {
                if ($term_object->name != '') {
                    $source_data['id'] = sanitize_text_field($term_object->term_id);
                    $source_data['title'] = sanitize_text_field($term_object->name);
                    $source_data['slug'] = sanitize_text_field($term_object->slug);
                }
            }

            $meta_query = new WP_Query($args);
            $total_records = $meta_query->found_posts;
            if ($meta_query->have_posts()) {
                $data = array();
                while ($meta_query->have_posts()) {
                    $meta_query->the_post();
                    $id = sanitize_text_field(get_the_ID());
                    $post = get_post($id);
                    $link = get_permalink($id);
                    $content = $post->post_content;
                    $content = apply_filters('the_content', $content);
                    $content = str_replace(']]>', ']]&gt;', $content);
                    $site_url = esc_url($headers["url"]);
                    $content1 = preg_replace('/\/tip_language\//','#nonclick/',$content);
                    //$content2 = str_replace("https://demo-tips.translation.bible/story/", esc_url($headers["url"]) . "/source?term_slug=", $content1);
                    $content2 = Tips_Common::replace_specific_url_in_content($content1);

                    $word_limit = "80";
                    //$content2 = Tips_Common::trim_content($content2, $word_limit);
                    
                    $taxonomies = is_single() || ($meta_query->post_count == 1) ? ['Language' => 'tip_language', 'Verse' => 'tip_verse'] :  ['Language' => 'tip_language'];
                    $taxonomies_list = array();
                    foreach ($taxonomies as $key => $taxonomy) {
                        if (is_tax($taxonomy)) {
                            continue;
                        }
                        $terms = get_the_terms(sanitize_text_field($post->ID), sanitize_text_field($taxonomy));
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                $term_link = get_term_link(sanitize_text_field($term), sanitize_text_field($taxonomy));
                                $taxonomies_list[$key][sanitize_text_field($term->name)] = Tips_Common::replace_all_url($term_link, esc_url($headers["url"]), strtolower($key), sanitize_text_field($taxonomy), sanitize_text_field($term->slug));
                                $both_titles = Tips_Common::get_hover_title(sanitize_text_field($post->ID)); //main title and hover title
                                $maintitle = implode(", ", $both_titles['main_title']);
                                $get_tree_link =  Tips_Common::get_graphical_link($id);
                                $title_link = $headers['url']."/detail/?".$post->post_name;
                            }
                        }
                    }
                    $translations = get_post_meta( $id, '_translations', true );
                    if ( $translations && is_array( $translations ) ) {
                        foreach ( $translations as $key => $translation ) {
                            if ( array_key_exists( 'translation', $translation ) ) {
                                $language = get_term( $translation['language'] );
                                $translation_details = sprintf('<details id="%s"><summary>Translation: %s</summary><div class="translation">%s</div></details>',$language->slug,$language->name,wpautop($translation['translation']));
                            }
                        }
                    }
                    $post_object = (object) [
                        'id' => sanitize_text_field($post->ID),
                        'geographical_link' => $get_tree_link,
                        'title' => (object) ['rendered' => strip_tags($maintitle), 'hover_title' => $both_titles['hover_title'],'title_link' => $title_link],
                        'link' => esc_url($link),
                        'content' => (object) ['rendered' => $content2],
                        'translation_details' => $translation_details,
                        //'taxonomies_list' => $taxonomies_list,
                    ];

                    $data['sourceData'] = $source_data;
                    $data['storyData'][] = $post_object;
                    
                    
                }
                $base = 'tip_source';
                $image_url = plugins_url('images/prev_icon.png', dirname(__FILE__));
                $data['pagination'][] = (object) ['total_pages' => $meta_query->max_num_pages, 'image_url' => $image_url,'paged' => $paged,'base' => $base];
                return $data;
            } else {
                return 'No post to show';
            }
    }
}
$lps_rest_server = new TipsSourceDetailRestAPI();
