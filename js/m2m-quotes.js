jQuery(document).ready(function($) {
    $('.m2m-like-btn, .m2m-dislike-btn').click(function() {
        var quoteId = $(this).data('quote-id');
        var voteType = $(this).hasClass('m2m-like-btn') ? 'like' : 'dislike';
        $.ajax({
            url: m2m_quotes_ajax.ajax_url,
            type: 'post',
            data: {
                action: 'm2m_like_dislike',
                quote_id: quoteId,
                vote_type: voteType
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // reload the page to show updated like/dislike counts
                }
            }
        });
    });

    $('.m2m-copy-link').click(function() {
        var copyText = $(this).data('link');
        navigator.clipboard.writeText(copyText);
        alert('Link copied to clipboard!');
    });
});
