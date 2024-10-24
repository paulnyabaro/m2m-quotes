jQuery(document).ready(function($) {
    // Like Button Click
    $('.m2m-like-btn').on('click', function() {
        var quote_id = $(this).data('quote-id');
        var $button = $(this);

        if (!localStorage.getItem('m2m-voted-' + quote_id)) {
            $.ajax({
                type: 'POST',
                url: m2m_quotes_ajax.ajax_url,
                data: {
                    action: 'm2m_quote_vote',
                    quote_id: quote_id,
                    action_type: 'like',
                    nonce: m2m_quotes_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.html('👍 Like (' + response.data.likes + ')');
                        localStorage.setItem('m2m-voted-' + quote_id, 'liked');
                    }
                }
            });
        } else {
            alert("You have already voted!");
        }
    });

    // Dislike Button Click
    $('.m2m-dislike-btn').on('click', function() {
        var quote_id = $(this).data('quote-id');
        var $button = $(this);

        if (!localStorage.getItem('m2m-voted-' + quote_id)) {
            $.ajax({
                type: 'POST',
                url: m2m_quotes_ajax.ajax_url,
                data: {
                    action: 'm2m_quote_vote',
                    quote_id: quote_id,
                    action_type: 'dislike',
                    nonce: m2m_quotes_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.html('👎 Dislike (' + response.data.dislikes + ')');
                        localStorage.setItem('m2m-voted-' + quote_id, 'disliked');
                    }
                }
            });
        } else {
            alert("You have already voted!");
        }
    });
});
