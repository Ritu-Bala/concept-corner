<?php

class FacetWP_Vendor
{
    public $search_terms;


    function __construct() {
        add_filter( 'facetwp_query_args', array( $this, 'search_args' ), 10, 2 );
        add_filter( 'facetwp_pre_filtered_post_ids', array( $this, 'searchwp_search' ), 10, 2 );
    }


    /**
     * Prevent the default WP search from running when SearchWP is enabled
     * @since 1.3.2
     */
    function search_args( $args, $class ) {

        if ( ! empty( $args['s'] ) ) {
            $class->is_search = true;

            if ( is_plugin_active( 'searchwp/searchwp.php' ) ) {
                $this->search_terms = $args['s'];
                unset( $args['s'] );
            }
        }

        return $args;
    }


    /**
     * Use the SearchWP API to retrieve matching post IDs
     * @since 1.3.2
     */
    function searchwp_search( $post_ids, $class ) {

        if ( ! empty( $this->search_terms ) && class_exists( 'SearchWP' ) ) {

            // Return only post IDs and set pagination to 200
            add_filter( 'searchwp_load_posts', '__return_false' );
            add_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            // Perform the search
            $searchwp = SearchWP::instance();
	        $results = $searchwp->search( 'default', $this->search_terms, 1 );

            // Revert filters
            remove_filter( 'searchwp_load_posts', '__return_false' );
            remove_filter( 'searchwp_posts_per_page', array( $this, 'searchwp_posts_per_page' ) );

            // Preserve post ID order
            $intersected_ids = array();
            foreach ( $results as $post_id ) {
                if ( in_array( $post_id, $post_ids ) ) {
                    $intersected_ids[] = $post_id;
                }
            }
            $post_ids = $intersected_ids;
        }

        return $post_ids;
    }


    /**
     * SearchWP pagination callback
     * @since 1.7.1
     */
    function searchwp_posts_per_page() {
        return 200;
    }
}
