<?php
/**
 * Plugin Name: m2m Quotes
 * Description: A plugin to display quotes with likes, dislikes, and sharing options.
 * Version: 1.0
 * Author: Paul Nyabaro
 * Text Domain: m2m-quotes
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue CSS and JS files.
function m2m_enqueue_assets() {
    wp_enqueue_style('m2m-quotes-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('m2m-quotes-js', plugin_dir_url(__FILE__) . 'js/m2m-quotes.js', array('jquery'), null, true);
    wp_localize_script('m2m-quotes-js', 'm2mQuotes', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'm2m_enqueue_assets');

// Register custom post type for Quotes.
function m2m_create_quotes_post_type() {
    register_post_type('m2m_quotes', array(
        'labels' => array(
            'name' => __('Quotes', 'm2m-quotes'),
            'singular_name' => __('Quote', 'm2m-quotes'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'm2m_create_quotes_post_type');

// Add meta box for author and role.
function m2m_add_quote_meta_box() {
    add_meta_box('m2m_quote_meta_box', __('Quote Details', 'm2m-quotes'), 'm2m_quote_meta_box_html', 'm2m_quotes', 'normal', 'high');
}
add_action('add_meta_boxes', 'm2m_add_quote_meta_box');

function m2m_quote_meta_box_html($post) {
    $author = get_post_meta($post->ID, 'author', true);
    $role = get_post_meta($post->ID, 'role', true);
    ?>
    <label for="m2m_author"><?php _e('Author:', 'm2m-quotes'); ?></label>
    <input type="text" name="m2m_author" value="<?php echo esc_attr($author); ?>" class="widefat"><br>
    <label for="m2m_role"><?php _e('Role:', 'm2m-quotes'); ?></label>
    <input type="text" name="m2m_role" value="<?php echo esc_attr($role); ?>" class="widefat">
    <?php
}

function m2m_save_quote_meta_box($post_id) {
    if (array_key_exists('m2m_author', $_POST)) {
        update_post_meta($post_id, 'author', sanitize_text_field($_POST['m2m_author']));
    }
    if (array_key_exists('m2m_role', $_POST)) {
        update_post_meta($post_id, 'role', sanitize_text_field($_POST['m2m_role']));
    }
}
add_action('save_post', 'm2m_save_quote_meta_box');

// Create shortcode to display quotes.
function m2m_quotes_shortcode() {
    $args = array(
        'post_type' => 'm2m_quotes',
        'posts_per_page' => 1,
        'orderby' => 'rand', // Randomly display a quote
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
                    <a href="#" class="m2m-share" data-platform="twitter">Share on Twitter</a>
                    <a href="#" class="m2m-share" data-platform="facebook">Share on Facebook</a>
                    <a href="#" class="m2m-share" data-platform="linkedin">Share on LinkedIn</a>
                    <a href="#" class="m2m-share" data-platform="copy">Copy Link</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
    } else {
        return '<p>No quotes available.</p>';
    }
}
add_shortcode('m2m_quotes', 'm2m_quotes_shortcode');

// Handle AJAX request for like and dislike.
function m2m_handle_vote() {
    $quote_id = intval($_POST['quote_id']);
    $vote_type = sanitize_text_field($_POST['vote_type']);

    if ($vote_type === 'like') {
        $likes = get_post_meta($quote_id, 'likes', true) ?: 0;
        update_post_meta($quote_id, 'likes', $likes + 1);
    } elseif ($vote_type === 'dislike') {
        $dislikes = get_post_meta($quote_id, 'dislikes', true) ?: 0;
        update_post_meta($quote_id, 'dislikes', $dislikes + 1);
    }

    wp_send_json_success();
}
add_action('wp_ajax_m2m_vote', 'm2m_handle_vote');
add_action('wp_ajax_nopriv_m2m_vote', 'm2m_handle_vote');

// Admin Menu and Settings Page
function m2m_admin_menu() {
    add_menu_page('m2m Quotes Settings', 'm2m Quotes', 'manage_options', 'm2m-quotes', 'm2m_quotes_settings_page');
}
add_action('admin_menu', 'm2m_admin_menu');

function m2m_quotes_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('m2m Quotes Settings', 'm2m-quotes'); ?></h1>
        <p><?php _e('Manage the settings for your quotes plugin here.', 'm2m-quotes'); ?></p>
    </div>
    <?php
}
