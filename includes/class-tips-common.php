<?php
class Tips_Common
{
    public static function replace_all_url($link,$header_url,$termname,$taxonomy,$slug)
    {
        if($termname == 'verse'){
            $replace_with = $header_url."/tip_verse/?verseId=".$slug;
            $custom_url = $replace_with;
        }
        elseif($termname == 'source'){
            $replace_with = $header_url."/tip_source/?sourceId=".$slug;
            $custom_url = $replace_with;
        }
        return $custom_url;
    }
    public static function get_hover_title($postID)
    {    
        $original_terms = []; $data = [];
        
        $terms = get_the_terms( $postID, 'tip_term' );
        if(!empty($terms)){
            foreach ( $terms as $term ) {
                foreach ( array( 'greek', 'hebrew', 'aramaic' ) as $original_language ) {
                    $original_term = get_term_meta( $term->term_id, '_term_' . $original_language, true );

                    if ( $original_term ) {
                        $original_terms[] = $original_term;
                    }
                }
                if( count( $original_terms ) <= 0 ) 
                {
                    $data['hover_title'] = null;
                    $data['main_title'][] = sprintf( '<span class="term">%s</span>', sanitize_text_field($term->name) );
                }else{
                    $data['hover_title'] = sprintf( '%s', implode( ', ', $original_terms ), sanitize_text_field($term->name));
                    $data['main_title'][] = sprintf( '<span class="term">%s</span>', sanitize_text_field($term->name));                
                }                             
            }
        }
        return $data;
    }
    
    public static function get_graphical_link($id)
    {
        $get_tree_link = [];
        $terms = get_the_terms( $id, 'tip_term' );    
        if(!empty($terms))
        {   
            foreach($terms as $term)
            {
                if ( tips_show_back_translations( $term->term_id ) ) {
                    $get_tree_link['title'] = "Click here to view graphically";
                    $tree_link = get_permalink( get_option( 'ubs_tip_tree_page_id' ) );          
                    $get_tree_link['link'] = "/tree-view?term_id=".$term->term_id;
                    $url = $get_tree_link;
                }
            }
        }
        return $url;
    }
    
    public static function get_tip_term_get_back_translations( $term_id )  {

    $translations = get_term_meta($term_id, 'translation', true);

    if (!$translations || $translations == '') return [];

    $term = get_term($term_id);
    $original_terms = [];

    foreach (array('greek', 'hebrew', 'aramaic', 'latin', 'geez') as $original_language) {
        $original_term = get_term_meta($term_id, '_term_' . $original_language, true);

        if ($original_term) {
            $original_terms[] = $original_term;
        }
    }

    $root = new stdClass();
    $root->name = $term->name;
    $root->original = implode(', ', $original_terms);

    $tree = [$root];

    foreach ($translations as $translation) {

        $node = new stdClass();
        $node->name = $translation['translation'];

        // Check if $translation['language'] is an array and not empty
        if (is_array($translation['language']) && !empty($translation['language'])) {
            // Use array_map safely, considering the possibility of $language_term being null
            $node->language = implode(', ', array_map(function ($term_id) {
                $language_term = get_term($term_id, 'tip_language');
                // Check if $language_term is not null before accessing its properties
                if ($language_term) {
                    return $language_term->name;
                } else {
                    return ''; // Return empty string if $language_term is null
                }
            }, $translation['language']));
        } else {
            $node->language = ''; // Set empty string if $translation['language'] is not an array or empty
        }

        $node->parent = 0;

        $tree[] = $node;
    }

    // term + greek or hebrew
    // usage ' '
    // Translation  'Language list'

    return $tree;
}
    
    
    public static function get_book_abbrevations($name){
        global $wpdb;
        $table_name = $wpdb->prefix . 'book_alternatives';

        $sql = $wpdb->prepare( "SELECT abbreviation FROM $table_name WHERE name='%s'", strtolower( $name ) );
        error_log( $sql );
        $results = $wpdb->get_results( $sql );

        error_log( print_r( $results, true ) );

        if ( $results !== NULL && is_array( $results ) && count( $results ) > 0 ) {
            return $results[0]->abbreviation;
        } else {
            return '';
        }
    }
    public static function trim_content($content, $word_limit) {
        // Strip HTML tags and shortcodes
        $content = strip_tags(strip_shortcodes($content));
        
        // Split the content into an array of words
        $words = explode(' ', $content);
        
        // If the number of words is less than or equal to the word limit, return the content as is
        if (count($words) <= $word_limit) {
            return $content;
        }
        
        // Otherwise, trim the content to the specified number of words
        $trimmed_content = array_slice($words, 0, $word_limit);
        
        // Join the trimmed content back into a string and append "..."
        return implode(' ', $trimmed_content) . '........';
    }
    public static function replace_specific_url_in_content($content) {
        $url_replacements = array(
            'http://tips.translation.bible/story/' => 'detail/?',
            'https://tips.translation.bible/story/' => 'detail/?',
            'https://demo-tips.translation.bible/story/' => 'detail/?',
            'https://tips.translation.bible/tip_verse/' => '?verseId=', 
            'https://demo-tips.translation.bible/tip_verse/' => '?verseId=' 
        );

        foreach ($url_replacements as $url => $replacement_base_url) {
            $pattern = '/<a\s+href="' . preg_quote($url, '/') . '([^"]*)">/';
            $replacement = '<a href="' . $replacement_base_url . '$1">';
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }
}
?>