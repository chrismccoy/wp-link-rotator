<?php
/**
 * Plugin Name: WP Link Rotator
 * Description: A Simple Link Rotator with Weights
 * Version:     1.0
 * Author:      Chris McCoy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPLinkRotator {
    public function __construct() {
        add_action( 'after_setup_theme', [$this, 'init_carbon_fields'] );
        add_action( 'init', [$this, 'register_post_type'] );
        add_filter( 'manage_link_rotator_posts_columns', [$this, 'columns'] );
        add_action( 'manage_link_rotator_posts_custom_column', [$this, 'columns_content'], 10, 2 );
        add_action( 'template_redirect', [$this, 'do_redirect'] );
    }

    public function init_carbon_fields(): void {
        \Carbon_Fields\Carbon_Fields::boot();
        add_action('carbon_fields_register_fields', [$this, 'meta_box']);
    }

    public function register_post_type(): void {
        $labels = $this->get_post_type_labels();

        $args = [
            'labels'    => $labels,
            'public'    => true,
            'menu_icon' => 'dashicons-admin-links',
            'supports'  => [ 'title' ],
            'rewrite'   => [ 'slug' => 'go', 'with_front' => false ],
        ];

        register_post_type( 'link_rotator', $args );
    }

    private function get_post_type_labels(): array {
        return [
            'name'               => esc_html__( 'Rotators', 'wp-link-rotator' ),
            'singular_name'      => esc_html__( 'Rotator', 'wp-link-rotator' ),
            'menu_name'          => esc_html__( 'Rotators', 'wp-link-rotator' ),
            'name_admin_bar'     => esc_html__( 'Rotator', 'wp-link-rotator' ),
            'add_new'            => esc_html__( 'Add New', 'wp-link-rotator' ),
            'add_new_item'       => esc_html__( 'Add New Rotator', 'wp-link-rotator' ),
            'new_item'           => esc_html__( 'New Rotator', 'wp-link-rotator' ),
            'edit_item'          => esc_html__( 'Edit Rotator', 'wp-link-rotator' ),
            'view_item'          => esc_html__( 'View Rotator', 'wp-link-rotator' ),
            'all_items'          => esc_html__( 'All Rotators', 'wp-link-rotator' ),
            'search_items'       => esc_html__( 'Search Rotators', 'wp-link-rotator' ),
            'parent_item_colon'  => esc_html__( 'Parent Rotators:', 'wp-link-rotator' ),
            'not_found'          => esc_html__( 'No rotators found.', 'wp-link-rotator' ),
            'not_found_in_trash' => esc_html__( 'No rotators found in Trash.', 'wp-link-rotator' ),
        ];
    }

    public function meta_box(): void {
        Container::make( 'post_meta', 'WP Link Rotation' )
            ->where( 'post_type', '=', 'link_rotator' )
            ->add_fields( [
                Field::make('complex', 'wplinkrotator_url_list', 'URLs to Rotate')
                    ->add_fields([
                        Field::make('text', 'wplinkrotator_url', 'URL'),
                        Field::make('text', 'wplinkrotator_url_weight', 'Weight')
                    ]),
            ]);
    }

    public function columns(): array {
        return [
            'cb'        => '<input type="checkbox" />',
            'title'     => esc_html__( 'Title', 'wp-link-rotator' ),
            'wplinkrotator_url'    => esc_html__( 'URL', 'wp-link-rotator' ),
            'wplinkrotator_target' => esc_html__( 'Target', 'wp-link-rotator' ),
            'wplinkrotator_weight' => esc_html__( 'Weight', 'wp-link-rotator' ),
        ];
    }

    public function columns_content( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'wplinkrotator_url' :
                $this->render_url_column($post_id);
                break;
            case 'wplinkrotator_target' :
                $this->render_target_column($post_id);
                break;
            case 'wplinkrotator_weight' :
                $this->render_weight_column($post_id);
                break;
        }
    }

    private function render_url_column(int $post_id): void {
        $url = esc_url( get_the_permalink( $post_id ) );
        echo sprintf('<a href="%s" target="_blank">%s</a>', $url, $url);
    }

    private function render_target_column(int $post_id): void {
        $urls  = carbon_get_post_meta($post_id, 'wplinkrotator_url_list');

        if (!is_array($urls)) {
            return;
        }

        $targets = array_map(function($url) {
            return sprintf('<a href="%s" target="_blank">%s</a>', esc_url($url['wplinkrotator_url']), esc_html($url['wplinkrotator_url']));
        }, $urls);

        echo implode("<br>", $targets);
    }

    private function render_weight_column(int $post_id): void {
        $urls  = carbon_get_post_meta($post_id, 'wplinkrotator_url_list');

        if (!is_array($urls)) {
            return;
        }

        $weights = array_map(function($url) {
            return sprintf("<b>%s</b>", esc_html($url['wplinkrotator_url_weight'] . '%'));
        }, $urls);

        echo implode("<br>", $weights);
    }

    public function get_random_url(int $post_id): ?string {
        $urls = carbon_get_post_meta($post_id, 'wplinkrotator_url_list');
        $randomValue = random_int(1, 100);

        if (!is_array($urls)) {
            return null;
        }

        foreach ($urls as $url) {
            if (($randomValue -= (int)$url['wplinkrotator_url_weight']) <= 0) {
                return esc_url($url['wplinkrotator_url']);
            }
        }
        return null;
    }

    public function do_redirect(): void {
        if ( ! is_singular( 'link_rotator' ) ) {
            return;
        }

        $random_url = $this->get_random_url(get_the_ID());

        if (!empty($random_url)) {
            wp_redirect($random_url, 302);
            exit;
        }
    }
}

new WPLinkRotator();
