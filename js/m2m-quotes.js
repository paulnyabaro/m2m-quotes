jQuery(document).ready(function($) {
    $('.m2m-like-button, .m2m-dislike-button').click(function() {
        var quoteId = $(this).data('quote-id');
        var action = $(this).hasClass('m2m-like-button') ? 'like' : 'dislike';

        $.post(m2mQuotes.ajax_url, {
            action: 'm2m_vote',
            quote_id: quoteId,
            action: action,
        }, function(response) {
            if (response.success) {
                // Optionally, refresh the quote display here
            }
        });
    });
});
