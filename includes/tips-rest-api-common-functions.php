<?php
//define('SHORTINIT',1);
function replace_all_url($link,$header_url,$termname,$taxonomy,$slug)
{
    $site_url = get_site_url();
    $custom_url = "";
    $current_permalink = get_option( 'permalink_structure' );
    if($current_permalink == "")
    {
        $custom_url = str_replace($site_url,$header_url,$link);
    }else{
        //$replace_with = $header_url."/".$termname."/?term_name=".$taxonomy."&term_slug=".$slug;
        $replace_with = $header_url."/tip_source/?sourceId=".$slug;
        $custom_url = $replace_with;
    }
    return $custom_url;
}

function tips_show_back_translations( int $term_id ) : bool {

	if ( !get_term_meta( $term_id, 'show_graph', true  ) ) {
		return false;
	}

	$translations = get_term_meta( $term_id, 'translation', true );

	return is_array( $translations ) && count( $translations ) > 0;
}
// jigisha - cmetric
function trim_content($content, $word_limit) {
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

// jigisha - cmetric
?>