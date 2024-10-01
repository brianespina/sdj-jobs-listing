<?php
// AJAX handler for logged-in users
add_action( 'wp_ajax_get_jobs', 'get_jobs' );

// AJAX handler for non-logged-in users
add_action( 'wp_ajax_nopriv_get_jobs', 'get_jobs' );


function get_jobs() {

    // Get the filter from the request
    $filters = isset($_POST['filters']) ? json_decode(stripslashes($_POST['filters']), true) : [];
    $paged = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
    $hide_expired = isset( $_POST['hide_expired'] ) ? intval( $_POST['hide_expired'] ) : 0;
    $posts_per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 10;

    // Query posts
    $args = array(
        'post_type' => 'job_listing',
        'posts_per_page' => $posts_per_page, // Number of posts you want to return
        'paged' => $paged,
    );

    $tax_query = array('relation' => 'AND');
    
     // Process each filterrs
    if (!empty($filters)) {
        foreach ($filters as $taxonomy => $terms) {
            if (!empty($terms)) {
                $tax_query[] = array(
                    'taxonomy' => sanitize_text_field($taxonomy),
                    'field' => 'name',
                    'terms' => $terms, // Sanitize terms
                );
            }
        }
    }

    if($hide_expired == 1){
        $meta_query = array('relation' => 'OR');
        $meta_query[] = array(
            'key' => '_job_expires',  // The meta key for the expiration date
            'compare' => 'NOT EXISTS',   // Include jobs that don't have an expiry date
        );
        $meta_query[] =array(
            'key' => '_job_expires',
            'value' => date('Y-m-d'),    // Current date in Y-m-d format
            'compare' => '>=',           // Exclude jobs where the expiry date is before today
            'type' => 'DATE',            // Treat the value as a date
        );
        $args['meta_query'] = $meta_query;
    }
    if (count($tax_query) > 1) { 
        $args['tax_query'] = $tax_query;
    }

    $posts = new WP_Query( $args );
    // Prepare post data
    $post_data = array();
    if ( $posts->have_posts() ) {
        while ( $posts->have_posts() ) {
            $posts->the_post();

            $post_data[] = array(
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'company_name' => get_post_meta(get_the_ID(), '_company_name', true),
                'job_location' => get_post_meta(get_the_ID(), '_job_location', true),
                'type' => get_the_terms( get_the_ID(), 'job_listing_type' ),
                'country' => get_the_terms( get_the_ID(), 'country' ),
                'job_industry' => get_the_terms( get_the_ID(), 'job_industry' ),
                'job_experience_level' => get_the_terms( get_the_ID(), 'job_experience_level' ),
                'featured_image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                'time' => human_time_diff(get_the_time('U'), current_time ('timestamp')) . ' ago',
            );
        }
    }

    wp_reset_postdata();

    // Now calculate term counts for all taxonomies excluding their own filter
    $taxonomy_counts = array();
    $taxonomies = array('country', 'job_listing_type', 'job_industry', 'job_experience_level'); // Add any taxonomies you need

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ));

        // For each term in this taxonomy, get the post count excluding the filter for this taxonomy
        foreach ($terms as $term) {
            $term_query = $args;

            // Modify the tax query to exclude the current taxonomy filter
            $term_query['tax_query'] = array_filter($tax_query, function ($query) use ($taxonomy) {
                return is_array($query) && isset($query['taxonomy']) && $query['taxonomy'] !== $taxonomy;
            });

            // Add the current term as a filter
            $term_query['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term->term_id,
            );

            // Get the count of posts for this term
            $term_posts = new WP_Query($term_query);
            $taxonomy_counts[$taxonomy][$term->name] = $term_posts->found_posts;
        }
    }

    // Send the response with both post data and taxonomy counts
    wp_send_json_success(array(
        'posts' => $post_data,
        'taxonomy_counts' => $taxonomy_counts,
    ));
    wp_die();
}


// AJAX handler for logged-in users
add_action( 'wp_ajax_get_tax', 'get_tax' );

// AJAX handler for non-logged-in users
add_action( 'wp_ajax_nopriv_get_tax', 'get_tax' );

function get_tax() {

    $tax = isset($_POST['tax']) ? sanitize_text_field($_POST['tax']) : '';

    $terms = get_terms(array(
        'taxonomy' => $tax, // Replace 'country' with your actual taxonomy name
        'hide_empty' => true,    // Only get terms that have posts
    ));

    $countries = array();

    foreach ($terms as $term) {
        $countries[] = array(
            'name' => $term->name,
            'count' => $term->count
        );// Store the term name and post count
    }

    // Send response in JSON format
    wp_send_json_success($countries);
    wp_die();
}

