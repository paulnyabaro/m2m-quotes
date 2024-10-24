<?php
// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete all custom post types and associated metadata.
$quotes = get_posts(array(
    'post_type' => 'm2m_quotes',
    'numberposts' => -1,
));

foreach ($quotes as $quote) {
    wp_delete_post($quote->ID, true);
}
