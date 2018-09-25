<?php

final class FacetWP_Helper
{

    public $settings;
    public $facet_types;


    /**
     * Backwards-compatibility
     */
    public static function instance() {
        return FWP()->helper;
    }


    function __construct() {
        $this->settings = $this->load_settings();

        // custom facet types
        include( FACETWP_DIR . '/includes/facets/autocomplete.php' );
        include( FACETWP_DIR . '/includes/facets/checkboxes.php' );
        include( FACETWP_DIR . '/includes/facets/date_range.php' );
        include( FACETWP_DIR . '/includes/facets/dropdown.php' );
        include( FACETWP_DIR . '/includes/facets/hierarchy.php' );
        include( FACETWP_DIR . '/includes/facets/number_range.php' );
        include( FACETWP_DIR . '/includes/facets/search.php' );
        include( FACETWP_DIR . '/includes/facets/slider.php' );

        $this->facet_types = apply_filters( 'facetwp_facet_types', array(
            'checkboxes'        => new FacetWP_Facet_Checkboxes(),
            'date_range'        => new FacetWP_Facet_Date_Range(),
            'dropdown'          => new FacetWP_Facet_Dropdown(),
            'hierarchy'         => new FacetWP_Facet_Hierarchy(),
            'number_range'      => new FacetWP_Facet_Number_Range(),
            'search'            => new FacetWP_Facet_Search(),
            'slider'            => new FacetWP_Facet_Slider(),
            'autocomplete'      => new FacetWP_Facet_Autocomplete()
        ) );
    }


    /**
     * Parse the URL hostname
     */
    function get_http_host() {
        return parse_url( get_option( 'home' ), PHP_URL_HOST );
    }


    /**
     * Get settings and allow for developer hooks
     */
    function load_settings() {
        $settings = json_decode( get_option( 'facetwp_settings' ), true );
        $settings['facets'] = apply_filters( 'facetwp_facets', $settings['facets'] );
        $settings['templates'] = apply_filters( 'facetwp_templates', $settings['templates'] );

        if ( empty( $settings['facets'] ) ) {
            $settings['facets'] = array();
        }
        if ( empty( $settings['templates'] ) ) {
            $settings['templates'] = array();
        }

        return $settings;
    }


    /**
     * Get an array of all facets
     * @return array
     */
    function get_facets() {
        return $this->settings['facets'];
    }


    /**
     * Get an array of all templates
     * @return array
     */
    function get_templates() {
        return $this->settings['templates'];
    }


    /**
     * Get all properties for a single facet
     * @param string $facet_name
     * @return mixed An array of facet info, or false
     */
    function get_facet_by_name( $facet_name ) {
        foreach ( $this->get_facets() as $facet ) {
            if ( $facet_name == $facet['name'] ) {
                return $facet;
            }
        }
        return false;
    }


    /**
     * Get all properties for a single template
     * @param string $template_name
     * @return mixed An array of template info, or false
     */
    function get_template_by_name( $template_name ) {
        foreach ( $this->get_templates() as $template ) {
            if ( $template_name == $template['name'] ) {
                return $template;
            }
        }
        return false;
    }


    /**
     * Get a structured array of terms from a given taxonomy
     * @param string $taxonomy_name
     * @return array
     */
    function get_taxonomy_terms( $taxonomy_name ) {
        $output = array();
        $terms = get_terms( $taxonomy_name, array(
            'hide_empty' => false,
            'fields' => 'all',
        ) );

        foreach ( $terms as $term ) {
            $output[$term->term_id] = $term->name;
        }

        return $output;
    }


    /**
     * Get an array of term information, including depth
     * @return array Term information
     * @since 0.9.0
     */
    function get_term_depths( $taxonomy ) {
        $output = array();

        // Get all taxonomy terms
        $terms = get_terms( $taxonomy, array(
            'hide_empty'    => false,
            'fields'        => 'id=>parent',
        ) );

        foreach ( $terms as $term => $parent ) {
            $output[$term] = array(
                'parent_id'     => $parent,
                'depth'         => 0
            );

            $current_parent = $parent;
            while ( 0 < (int) $current_parent) {
                $current_parent = $terms[$current_parent];
                $output[$term]['depth']++;

                // Prevent an infinite loop
                if ( 50 < $output[$term]['depth'] ) {
                    break;
                }
            }
        }

        return $output;
    }


    /**
     * Finish sorting the facet values
     * The results are already sorted by depth and (name OR count), we just need
     * to move the children directly below their parents
     */
    function sort_taxonomy_values( $values = array(), $orderby = 'count' ) {

        // Create an "order" sort value based on the top-level items
        $cache = array();
        foreach ( $values as $key => $val ) {
            if ( 0 == $val['depth'] ) {
                $cache[ $val['facet_value'] ] = $key;
                $values[ $key ]['order'] = $key;
            }
            else {
                $new_order = $cache[ $val['parent_id'] ] . ".$key"; // dot-separated hierarchy string
                $cache[ $val['facet_value'] ] = $new_order;
                $values[ $key ]['order'] = $new_order;
            }
        }

        // Sort the array based on the new "order" element
        // Since this is a dot-separated hierarchy string, treat it like version_compare
        usort( $values, array( $this, 'compare_order' ) );

        return $values;
    }


    /**
     * Sort the "order" string using version_compare
     * @since 1.6.1
     */
    function compare_order( $a, $b ) {
        return version_compare( $a['order'], $b['order'] );
    }


    /**
     * Sanitize SQL data
     * @return mixed The sanitized value(s)
     * @since 0.9.1
     */
    function sanitize( $input ) {

        if ( is_array( $input ) ) {
            $output = array();

            foreach ( $input as $key => $val ) {
                $output[$key] = $this->sanitize( $val );
            }
        }
        else {
            $output = addslashes( $input );
        }

        return $output;
    }


    /**
     * Does a facet with the specified setting exist?
     * @return boolean
     * @since 1.4.0
     */
    function facet_setting_exists( $setting_name, $setting_value, $facets = array() ) {
        foreach ( $facets as $facet ) {
            if ( isset( $facet[ $setting_name ] ) && $facet[ $setting_name ] == $setting_value ) {
                return true;
            }
        }
        return false;
    }
}
