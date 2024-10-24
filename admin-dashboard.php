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
    // Check if user is allowed to make changes
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings when the form is submitted
    if (isset($_POST['m2m_quotes_settings_save'])) {
        check_admin_referer('m2m_quotes_settings_verify');

        // Save the rotating interval
        update_option('m2m_quotes_rotation_interval', intval($_POST['rotation_interval']));

        // Save custom buttons (text and URLs)
        update_option('m2m_custom_button_1', sanitize_text_field($_POST['custom_button_1']));
        update_option('m2m_custom_button_2', sanitize_text_field($_POST['custom_button_2']));
        update_option('m2m_custom_button_3', sanitize_text_field($_POST['custom_button_3']));
        update_option('m2m_custom_button_4', sanitize_text_field($_POST['custom_button_4']));

        update_option('m2m_custom_url_1', esc_url($_POST['custom_url_1']));
        update_option('m2m_custom_url_2', esc_url($_POST['custom_url_2']));
        update_option('m2m_custom_url_3', esc_url($_POST['custom_url_3']));
        update_option('m2m_custom_url_4', esc_url($_POST['custom_url_4']));

        // Save visibility options for likes/dislikes and share buttons
        update_option('m2m_show_likes_dislikes', isset($_POST['show_likes_dislikes']) ? 1 : 0);
        update_option('m2m_show_share_buttons', isset($_POST['show_share_buttons']) ? 1 : 0);

        // Display success message
        echo '<div class="updated"><p>Settings Saved!</p></div>';
    }

    // Retrieve saved settings
    $rotation_interval = get_option('m2m_quotes_rotation_interval', 24);
    $custom_button_1 = get_option('m2m_custom_button_1', 'Button 1');
    $custom_button_2 = get_option('m2m_custom_button_2', 'Button 2');
    $custom_button_3 = get_option('m2m_custom_button_3', 'Button 3');
    $custom_button_4 = get_option('m2m_custom_button_4', 'Button 4');

    $custom_url_1 = get_option('m2m_custom_url_1', '#');
    $custom_url_2 = get_option('m2m_custom_url_2', '#');
    $custom_url_3 = get_option('m2m_custom_url_3', '#');
    $custom_url_4 = get_option('m2m_custom_url_4', '#');

    $show_likes_dislikes = get_option('m2m_show_likes_dislikes', 1);
    $show_share_buttons = get_option('m2m_show_share_buttons', 1);

    // Output settings form
    ?>
    <div class="wrap">
        <h1>M2M Quotes Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('m2m_quotes_settings_verify'); ?>

            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th><label for="rotation_interval">Rotation Interval (hours)</label></th>
                    <td><input type="number" name="rotation_interval" id="rotation_interval" value="<?php echo esc_attr($rotation_interval); ?>" min="1" max="168">
                    <p class="description">Set the number of hours between each quote rotation (default is 24 hours).</p></td>
                </tr>
            </table>

            <h2>Custom Button Settings</h2>
            <table class="form-table">
                <tr>
                    <th><label for="custom_button_1">Custom Button 1 Text</label></th>
                    <td><input type="text" name="custom_button_1" id="custom_button_1" value="<?php echo esc_attr($custom_button_1); ?>">
                    <p class="description">The text for the first custom button.</p></td>
                </tr>
                <tr>
                    <th><label for="custom_url_1">Custom Button 1 URL</label></th>
                    <td><input type="url" name="custom_url_1" id="custom_url_1" value="<?php echo esc_attr($custom_url_1); ?>">
                    <p class="description">The URL for the first custom button.</p></td>
                </tr>
                <tr>
                    <th><label for="custom_button_2">Custom Button 2 Text</label></th>
                    <td><input type="text" name="custom_button_2" id="custom_button_2" value="<?php echo esc_attr($custom_button_2); ?>"></td>
                </tr>
                <tr>
                    <th><label for="custom_url_2">Custom Button 2 URL</label></th>
                    <td><input type="url" name="custom_url_2" id="custom_url_2" value="<?php echo esc_attr($custom_url_2); ?>"></td>
                </tr>
                <tr>
                    <th><label for="custom_button_3">Custom Button 3 Text</label></th>
                    <td><input type="text" name="custom_button_3" id="custom_button_3" value="<?php echo esc_attr($custom_button_3); ?>"></td>
                </tr>
                <tr>
                    <th><label for="custom_url_3">Custom Button 3 URL</label></th>
                    <td><input type="url" name="custom_url_3" id="custom_url_3" value="<?php echo esc_attr($custom_url_3); ?>"></td>
                </tr>
                <tr>
                    <th><label for="custom_button_4">Custom Button 4 Text</label></th>
                    <td><input type="text" name="custom_button_4" id="custom_button_4" value="<?php echo esc_attr($custom_button_4); ?>"></td>
                </tr>
                <tr>
                    <th><label for="custom_url_4">Custom Button 4 URL</label></th>
                    <td><input type="url" name="custom_url_4" id="custom_url_4" value="<?php echo esc_attr($custom_url_4); ?>"></td>
                </tr>
            </table>

            <h2>Display Options</h2>
            <table class="form-table">
                <tr>
                    <th><label for="show_likes_dislikes">Show Likes/Dislikes</label></th>
                    <td><input type="checkbox" name="show_likes_dislikes" id="show_likes_dislikes" <?php checked($show_likes_dislikes, 1); ?>>
                    <p class="description">Enable or disable the like/dislike buttons for quotes.</p></td>
                </tr>
                <tr>
                    <th><label for="show_share_buttons">Show Share Buttons</label></th>
                    <td><input type="checkbox" name="show_share_buttons" id="show_share_buttons" <?php checked($show_share_buttons, 1); ?>>
                    <p class="description">Enable or disable the social share buttons.</p></td>
                </tr>
            </table>

            <?php submit_button('Save Settings', 'primary', 'm2m_quotes_settings_save'); ?>
        </form>
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
