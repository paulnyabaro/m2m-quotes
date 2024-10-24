<?php

add_shortcode('m2m_display_quote', 'm2m_display_quote_shortcode');
function m2m_display_quote_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'm2m_quotes';

    // Fetch quote for the day
    $quote = $wpdb->get_row("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 1");

    if ($quote) {
        ob_start();
        ?>
        <div class="m2m-quote-container">
            <blockquote>
                "<?php echo $quote->quote_text; ?>"
                <footer>- <?php echo $quote->author; ?>, <?php echo $quote->role; ?></footer>
            </blockquote>

            <div class="m2m-vote-buttons">
                <?php if (get_option('m2m_show_likes_dislikes', 1)) : ?>
                    <button data-quote-id="<?php echo $quote->id; ?>" class="m2m-like-btn">👍 Like (<?php echo $quote->likes; ?>)</button>
                    <button data-quote-id="<?php echo $quote->id; ?>" class="m2m-dislike-btn">👎 Dislike (<?php echo $quote->dislikes; ?>)</button>
                <?php endif; ?>
            </div>

            <div class="m2m-custom-buttons">
                <a href="<?php echo esc_url(get_option('m2m_custom_url_1', '#')); ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/icon1.png'; ?>" alt="Button 1"></a>
                <a href="<?php echo esc_url(get_option('m2m_custom_url_2', '#')); ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/icon2.png'; ?>" alt="Button 2"></a>
                <a href="<?php echo esc_url(get_option('m2m_custom_url_3', '#')); ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/icon3.png'; ?>" alt="Button 3"></a>
                <a href="<?php echo esc_url(get_option('m2m_custom_url_4', '#')); ?>"><img src="<?php echo plugin_dir_url(__FILE__) . 'assets/icon4.png'; ?>" alt="Button 4"></a>
            </div>

            <?php if (get_option('m2m_show_share_buttons', 1)) : ?>
                <div class="m2m-share-buttons">
                <!-- Social share buttons -->
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($quote->quote_text); ?>">Share on Twitter</a>
                    <a href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()); ?>">Share on Facebook</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>">Share on LinkedIn</a>
                </div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }
}

