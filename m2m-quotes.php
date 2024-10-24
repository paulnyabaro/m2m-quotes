<?php
/**
 * Plugin Name: m2m Quotes
 * Description: A plugin to display quotes with likes, dislikes, and sharing options.
 * Version: 1.0.1
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
    // Check if form is submitted for settings
    if (isset($_POST['m2m_quotes_save_settings'])) {
        check_admin_referer('m2m_quotes_save_settings_verify');

        // Save options (you can add more settings as required)
        $auto_switch_interval = intval($_POST['m2m_auto_switch_interval']);
        update_option('m2m_auto_switch_interval', $auto_switch_interval);

        echo '<div class="updated"><p>' . __('Settings saved successfully!', 'm2m-quotes') . '</p></div>';
    }

    // Get the current setting for the automatic switch interval
    $auto_switch_interval = get_option('m2m_auto_switch_interval', 24); // Default is 24 hours

    // Query all quotes for performance
    $args = array(
        'post_type' => 'm2m_quotes',
        'posts_per_page' => -1
    );
    $quotes = new WP_Query($args);
    
    ?>
    <div class="wrap">
        <h1><?php _e('m2m Quotes Settings', 'm2m-quotes'); ?></h1>
        <h2><?php _e('Quotes Performance', 'm2m-quotes'); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Quote', 'm2m-quotes'); ?></th>
                    <th><?php _e('Author', 'm2m-quotes'); ?></th>
                    <th><?php _e('Likes', 'm2m-quotes'); ?></th>
                    <th><?php _e('Dislikes', 'm2m-quotes'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($quotes->have_posts()) {
                while ($quotes->have_posts()) {
                    $quotes->the_post();
                    $likes = get_post_meta(get_the_ID(), 'likes', true) ?: 0;
                    $dislikes = get_post_meta(get_the_ID(), 'dislikes', true) ?: 0;
                    $author = get_post_meta(get_the_ID(), 'author', true) ?: __('Unknown', 'm2m-quotes');
                    ?>
                    <tr>
                        <td><?php the_content(); ?></td>
                        <td><?php echo esc_html($author); ?></td>
                        <td><?php echo esc_html($likes); ?></td>
                        <td><?php echo esc_html($dislikes); ?></td>
                    </tr>
                    <?php
                }
                wp_reset_postdata();
            } else {
                ?>
                <tr>
                    <td colspan="4"><?php _e('No quotes found.', 'm2m-quotes'); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <hr>

        <h2><?php _e('Admin Options', 'm2m-quotes'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('m2m_quotes_save_settings_verify'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Automatic Quote Switch Interval (in hours)', 'm2m-quotes'); ?></th>
                    <td>
                        <input type="number" name="m2m_auto_switch_interval" value="<?php echo esc_attr($auto_switch_interval); ?>" min="1" class="small-text" />
                        <p class="description"><?php _e('Set the interval (in hours) for how often quotes should switch automatically.', 'm2m-quotes'); ?></p>
                    </td>
                </tr>

                <!-- Add more admin options here if needed -->

            </table>

            <p class="submit">
                <input type="submit" name="m2m_quotes_save_settings" class="button-primary" value="<?php _e('Save Changes', 'm2m-quotes'); ?>" />
            </p>
        </form>
    </div>
    <?php
}
