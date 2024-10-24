<?php
/*
Plugin Name: M2M Quotes
Description: Display quotes that rotate every 24 hours with voting and sharing capabilities.
Version: 1.1
Author: Paul Nyabaro
*/

if (!defined('ABSPATH')) exit;

// Include admin dashboard functionality
include(plugin_dir_path(__FILE__) . 'admin-dashboard.php');
include(plugin_dir_path(__FILE__) . 'shortcode-display.php');

// Create custom database table for quotes
register_activation_hook(__FILE__, 'm2m_quotes_create_table');
function m2m_quotes_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'm2m_quotes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        quote_text text NOT NULL,
        author varchar(255) NOT NULL,
        role varchar(255) DEFAULT '',
        likes int(11) DEFAULT 0,
        dislikes int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Enqueue assets (styles, scripts)
add_action('wp_enqueue_scripts', 'm2m_quotes_enqueue_assets');
function m2m_quotes_enqueue_assets() {
    wp_enqueue_style('m2m-quotes-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('m2m-quotes-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
    wp_localize_script('m2m-quotes-script', 'm2m_quotes_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('m2m_quotes_nonce')
    ));
}

// AJAX handlers for like and dislike buttons
add_action('wp_ajax_m2m_quote_vote', 'm2m_quote_vote');
add_action('wp_ajax_nopriv_m2m_quote_vote', 'm2m_quote_vote');
function m2m_quote_vote() {
    check_ajax_referer('m2m_quotes_nonce', 'nonce');
    global $wpdb;

    $quote_id = intval($_POST['quote_id']);
    $action = sanitize_text_field($_POST['action_type']);

    $table_name = $wpdb->prefix . 'm2m_quotes';

    if ($action == 'like') {
        $wpdb->query("UPDATE $table_name SET likes = likes + 1 WHERE id = $quote_id");
    } else if ($action == 'dislike') {
        $wpdb->query("UPDATE $table_name SET dislikes = dislikes + 1 WHERE id = $quote_id");
    }

    $result = $wpdb->get_row("SELECT likes, dislikes FROM $table_name WHERE id = $quote_id");
    wp_send_json_success($result);
}

// Cron job to display new quote every 24 hours
register_activation_hook(__FILE__, 'm2m_quotes_schedule_cron');
function m2m_quotes_schedule_cron() {
    if (!wp_next_scheduled('m2m_quotes_daily_event')) {
        wp_schedule_event(time(), 'daily', 'm2m_quotes_daily_event');
    }
}

add_action('m2m_quotes_daily_event', 'm2m_quotes_rotate_quote');
function m2m_quotes_rotate_quote() {
    // Logic for rotating the quote daily
    // You can use get_option and update_option to track the current displayed quote
}

register_deactivation_hook(__FILE__, 'm2m_quotes_remove_cron');
function m2m_quotes_remove_cron() {
    wp_clear_scheduled_hook('m2m_quotes_daily_event');
}

