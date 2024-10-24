<?php
/**
 * Plugin Name: m2m Quotes
 * Description: A plugin to display quotes with likes, dislikes, and sharing options.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create custom post type for quotes
function m2m_quotes_post_type() {
    register_post_type('m2m_quotes', array(
        'labels' => array(
            'name' => __('Quotes'),
            'singular_name' => __('Quote')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
    ));
}
add_action('init', 'm2m_quotes_post_type');

// Add a shortcode to display quotes
function m2m_quotes_shortcode($atts) {
    $args = array(
        'post_type' => 'm2m_quotes',
        'posts_per_page' => 1,
        'orderby' => 'rand', // Random quote for demo
    );
    
    $quotes = new WP_Query($args);
    if ($quotes->have_posts()) {
        while ($quotes->have_posts()) {
            $quotes->the_post();
            $quote = get_the_content();
            $author = get_post_meta(get_the_ID(), 'author', true);
            $role = get_post_meta(get_the_ID(), 'role', true);
            $likes = get_post_meta(get_the_ID(), 'likes', true) ?: 0;
            $dislikes = get_post_meta(get_the_ID(), 'dislikes', true) ?: 0;

            ob_start();
            ?>
            <div class="m2m-quote">
                <blockquote><?php echo esc_html($quote); ?></blockquote>
                <p>— <?php echo esc_html($author); ?>, <em><?php echo esc_html($role); ?></em></p>
                <div class="m2m-voting">
                    <button class="m2m-like-button" data-quote-id="<?php echo get_the_ID(); ?>">👍 <?php echo $likes; ?></button>
                    <button class="m2m-dislike-button" data-quote-id="<?php echo get_the_ID(); ?>">👎 <?php echo $dislikes; ?></button>
                </div>
                <div class="m2m-share-buttons">
                    <a href="#">Share on Twitter</a>
                    <a href="#">Share on Facebook</a>
                    <a href="#">Share on LinkedIn</a>
                    <a href="#">Copy Link</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    }
    return 'No quotes available.';
}
add_shortcode('m2m_quotes', 'm2m_quotes_shortcode');

// Enqueue scripts
function m2m_quotes_enqueue_scripts() {
    wp_enqueue_script('m2m-quotes-js', plugin_dir_url(__FILE__) . 'm2m-quotes.js', array('jquery'), null, true);
    wp_localize_script('m2m-quotes-js', 'm2mQuotes', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'm2m_quotes_enqueue_scripts');

// Handle like/dislike functionality
function m2m_handle_vote() {
    $quote_id = intval($_POST['quote_id']);
    $action = sanitize_text_field($_POST['action']);

    if ($action === 'like') {
        $likes = get_post_meta($quote_id, 'likes', true) ?: 0;
        update_post_meta($quote_id, 'likes', $likes + 1);
    } elseif ($action === 'dislike') {
        $dislikes = get_post_meta($quote_id, 'dislikes', true) ?: 0;
        update_post_meta($quote_id, 'dislikes', $dislikes + 1);
    }

    wp_send_json_success();
}
add_action('wp_ajax_m2m_vote', 'm2m_handle_vote');
add_action('wp_ajax_nopriv_m2m_vote', 'm2m_handle_vote');

// Admin menu for settings
function m2m_quotes_admin_menu() {
    add_menu_page('m2m Quotes Settings', 'm2m Quotes', 'manage_options', 'm2m-quotes', 'm2m_quotes_settings_page');
}
add_action('admin_menu', 'm2m_quotes_admin_menu');

function m2m_quotes_settings_page() {
    ?>
    <div class="wrap">
        <h1>m2m Quotes Settings</h1>
        <!-- Add your settings form here -->
    </div>
    <?php
}
