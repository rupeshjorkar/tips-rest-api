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

class TipsStoryDetailRestAPI extends WP_REST_Controller
{

    private $api_namespace;
    private $base;
    private $api_version;
    private $required_capability;

    public function __construct()
    {
        $this->api_namespace = '/v';
        $this->base = 'bible/story';
        $this->api_version = '1';
        $this->required_capability = 'read';  // Minimum capability to use the endpoint
        $this->init();
    }

    public function register_routes_Story_Detail()
    {
        $namespace = $this->api_namespace . $this->api_version;
        register_rest_route($namespace, '/' . $this->base, array(
            array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_story_details'), 'args' => [
                'storyId'       => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ]),
        ));
    }

    // Register our REST Server
    public function init()
    {
        add_action('rest_api_init', array($this, 'register_routes_Story_Detail'));
    }

    public function get_story_details(WP_REST_Request $request)
    {

        $headers = array_map('sanitize_text_field', getallheaders());
        if (!isset($headers['url']) || $headers['url'] === "") {
            $headers['url'] = home_url();
        }
        
      
            // TODO: Run real code here.

            $name = sanitize_text_field($request->get_param('storyId'));
            $args = array(
                'name'        => $name,
                'post_type' => 'tip_story',
                'post_status' => 'publish',
                'posts_per_page' => -1
            );

            $meta_query = new WP_Query($args);
            if ($meta_query->have_posts()) {

                $data = array();

                // Store each post's data in the array

                while ($meta_query->have_posts()) {

                    $meta_query->the_post();
                    $id = sanitize_text_field(get_the_ID());
                    $post = get_post($id);
                    $link = get_permalink($id);
                    $content = $post->post_content;
                    $content = apply_filters('the_content', $content);
                    $content = str_replace(']]>', ']]&gt;', $content);
                    $site_url = esc_url($headers["url"]);
                    //$content1 = preg_replace('/\/tip_language\//', esc_url($headers["url"]) . '/tips?term_name=tip_language&term_slug=', $content);
                    $content1 = preg_replace('/\/tip_language\//', '#nonclick/', $content);
                    $content2 = Tips_Common::replace_specific_url_in_content($content1);
                    $taxonomies = is_single() || ($meta_query->post_count == 1) ? ['Language' => 'tip_language', 'Verse' => 'tip_verse', 'Source' => 'tip_source'] : ['Language' => 'tip_language'];
                    $taxonomies_list = array();

                    foreach ($taxonomies as $key => $taxonomy) {
                        if (is_tax($taxonomy)) {
                            continue;
                        }

                        $terms = get_the_terms($post->ID, $taxonomy);
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                $term_link = get_term_link($term, $taxonomy);
                                $taxonomies_list[$key][sanitize_text_field($term->name)] = Tips_Common::replace_all_url($term_link, esc_url($headers["url"]), strtolower($key), $taxonomy, sanitize_text_field($term->slug));
                                $both_titles = Tips_Common::get_hover_title($post->ID); //main title and hover title
                                $maintitle = implode(", ", $both_titles['main_title']);
                                $get_tree_link =  Tips_Common::get_graphical_link($id);
                            }
                        }
                    }
                    $prev_post = get_previous_post();
                    $prev_story = array();
                    $next_story = array();

                    if ($prev_post) {
                        $image_url = plugins_url('images/back.png', dirname(__FILE__));
                        $prev_story['title'] = strip_tags(str_replace('"', '', $prev_post->post_title));
                        $prev_story['link'] = $headers['url']."/detail/?".$prev_post->post_name;
                        $prev_story['image'] = $image_url;
                    }

                    $next_post = get_next_post();
                    if ($next_post) {
                        $image_url = plugins_url('images/next.png', dirname(__FILE__));
                        $next_story['title'] = strip_tags(str_replace('"', '', $next_post->post_title));
                        $next_story['link'] =  $headers['url']."/detail/?".$next_post->post_name;
                        $next_story['image'] =  $image_url;
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
                        'id' => $post->ID,
                        'title' => (object) ['rendered' => strip_tags($maintitle), 'hover_title' => $both_titles['hover_title']],
                        'geographical_link' => $get_tree_link,
                        'date' => sanitize_text_field($post->post_date),
                        'slug' => sanitize_text_field($post->post_name),
                        'link' => esc_url($link),
                        'content' => (object) ['rendered' => $content2],
                        'taxonomies_list' => $taxonomies_list,
                        'translation_details' => $translation_details,
                        'prev_story' => $prev_story,
                        'next_story' => $next_story
                    ];

                    $data[] = $post_object;
                }

                // Return the data

                return $data;
            } else {
                // If there is no post
                return 'No post to show';
            }
    }
}
$lps_rest_server = new TipsStoryDetailRestAPI();
