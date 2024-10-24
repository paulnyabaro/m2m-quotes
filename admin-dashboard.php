<?php

add_action('admin_menu', 'm2m_quotes_add_admin_menu');
function m2m_quotes_add_admin_menu() {
    add_menu_page('M2M Quotes', 'M2M Quotes', 'manage_options', 'm2m-quotes', 'm2m_quotes_dashboard', 'dashicons-format-quote');
    add_submenu_page('m2m-quotes', 'Settings', 'Settings', 'manage_options', 'm2m-quotes-settings', 'm2m_quotes_settings_page');
}

function m2m_quotes_dashboard() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'm2m_quotes';
    $quotes = $wpdb->get_results("SELECT * FROM $table_name");

    // Output for Admin dashboard (Add Quote Form, List of Quotes)
    ?>
    <div class="wrap">
        <h1>M2M Quotes Dashboard</h1>
        <form method="post" action="">
            <input type="hidden" name="action" value="add_quote">
            <input type="text" name="quote_text" placeholder="Quote" required>
            <input type="text" name="author" placeholder="Author" required>
            <input type="text" name="role" placeholder="Role">
            <input type="submit" value="Add Quote">
        </form>

        <h2>All Quotes</h2>
        <table>
            <thead>
                <tr>
                    <th>Quote</th>
                    <th>Author</th>
                    <th>Role</th>
                    <th>Likes</th>
                    <th>Dislikes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($quotes as $quote) { ?>
                <tr>
                    <td><?php echo $quote->quote_text; ?></td>
                    <td><?php echo $quote->author; ?></td>
                    <td><?php echo $quote->role; ?></td>
                    <td><?php echo $quote->likes; ?></td>
                    <td><?php echo $quote->dislikes; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

function m2m_quotes_settings_page() {
    // Output for plugin settings page
    ?>
    <div class="wrap">
        <h1>M2M Quotes Settings</h1>
        <!-- Settings content goes here -->
    </div>
    <?php
}

// Handle adding quotes
add_action('admin_post_add_quote', 'm2m_add_quote');
function m2m_add_quote() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'm2m_quotes';

    $quote_text = sanitize_text_field($_POST['quote_text']);
    $author = sanitize_text_field($_POST['author']);
    $role = sanitize_text_field($_POST['role']);

    $wpdb->insert($table_name, array(
        'quote_text' => $quote_text,
        'author' => $author,
        'role' => $role,
    ));

    wp_redirect(admin_url('admin.php?page=m2m-quotes'));
    exit();
}
