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

class TipsVerseRelatedStoriesRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/tip_verse';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint     
        $this->init();
    }

    public function register_routes_Verse_Related_Story()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_verse_story_list'), 'args' => [
                'verseId'       => [
                    'type'     => 'string',
                    'required' => true,
                ]
            ]),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Verse_Related_Story'));
    }
    public function get_verse_story_list(WP_REST_Request $request)
    {

        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }

        $slug = sanitize_text_field($request->get_param('verseId'));
        $taxonomy = "tip_verse";
        $tip_order = sanitize_text_field($request->get_param('order'));
        $paged = sanitize_text_field($request->get_param('paged'));

        $args = array(
            'post_type' => 'tip_story',
            'post_status' => 'publish',
            'posts_per_page' => 8,
            'paged' => ($paged ? $paged : 1),
            'order' =>  $tip_order,
            'meta_key' => '_priority',
            'orderby' => 'meta_value_num',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $slug
                )
            )
        );

        $meta_query = new WP_Query($args);
        $total_records = $meta_query->found_posts;

        if ($meta_query->have_posts()) {

            $data = array();
            // Store each post's data in the array

            while ($meta_query->have_posts()) {

                $meta_query->the_post();
                $id = get_the_ID();
                $post = get_post($id);
                $link = get_permalink($id);


                $content = $post->post_content;
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);
                $site_url = esc_url($headers["url"]);
                $content1 = preg_replace('/\/tip_language\//','#nonclick/',$content);
                //$content2 = str_replace("https://tips.translation.bible/story/", esc_url($headers["url"]) . "/story?story_slug=", $content1);
                $content2 = Tips_Common::replace_specific_url_in_content($content1);
                 // jigisha - cmetric
                $word_limit = "80";
                //$content2 = trim_content($content2, $word_limit);
                 
                 // jigisha - cmetric
                 
                $taxonomies = is_single() || ($meta_query->post_count == 1) ? ['Language' => 'tip_language',  'Source' => 'tip_source'] : ['Language' => 'tip_language'];
                $taxonomies = ['Language' => 'tip_language'];
                $taxonomies_list = array();

                foreach ($taxonomies as $key => $taxonomy) {
                    if (is_tax($taxonomy)) {
                        continue;
                    }
                    $terms = get_the_terms($id, $taxonomy);
                    if (!empty($terms)) {
                        foreach ($terms as $term) {
                            $term_link = get_term_link($term, $taxonomy);
                            $taxonomies_list[$key][sanitize_text_field($term->name)] = Tips_Common::replace_all_url($term_link, esc_url($headers["url"]), strtolower($key), $taxonomy, sanitize_text_field($term->slug));
                            $both_titles = Tips_Common::get_hover_title(sanitize_text_field($id)); //main title and hover title
                            $maintitle = implode(", ", $both_titles['main_title']);
                            $get_tree_link = Tips_Common::get_graphical_link($id);
                           
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
                    
                $get_translation_data = $this->get_translation_section();
                $title_link = $headers['url']."/detail/?".$post->post_name;
                $post_object = (object) [
                    'id' => sanitize_text_field($id),
                    'title' => (object) ['rendered' => sanitize_text_field($post->post_title),'hover_title' => $both_titles['hover_title'],'title_link' => $title_link],
                    'slug' => sanitize_text_field($post->post_name),
                    'geographical_link' => $get_tree_link,
                    'link' => esc_url($link),
                    'content' => (object) ['rendered' => $content2],
                    'translation_data' => (!empty($get_translation_data)) ? $get_translation_data['title'] : null,
                    'translation_details' => $translation_details,
                    // 'taxonomies_list' => $taxonomies_list
                ];
                $data[] = $post_object;
            }
           
            $base = 'tip_verse';
            $image_url = plugins_url('images/prev_icon.png', dirname(__FILE__));
            $data['pagination'][] = (object) ['total_pages' => $meta_query->max_num_pages, 'image_url' => $image_url,'paged' => $paged,'base' => $base];
            

            return $data;
        } else {
            // If there is no post
            return 'No post to show';
        }
    }
    public function get_translation_section()
    {
        $detail = array();
        $translations = get_post_meta(get_the_ID(), '_translations', true);

        if ($translations && is_array($translations)) {

            foreach ($translations as $key => $translation) {

                if (array_key_exists('translation', $translation)) {
                    $language = get_term($translation['language']);
                    $detail['title'] = sprintf('<details id="%s"><summary>Translation: %s</summary><div class="translation">%s</div></details>', sanitize_text_field($language->slug), sanitize_text_field($language->name), wpautop($translation['translation']));
                }
            }
        }
        return $detail;
    }
}
$lps_rest_server = new TipsVerseRelatedStoriesRestAPI();
