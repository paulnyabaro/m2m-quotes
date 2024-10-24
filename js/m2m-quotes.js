jQuery(document).ready(function($) {
    $('.m2m-like-button, .m2m-dislike-button').on('click', function() {
        var quoteId = $(this).data('quote-id');
        var voteType = $(this).hasClass('m2m-like-button') ? 'like' : 'dislike';

        $.ajax({
            url: m2mQuotes.ajax_url,
            method: 'POST',
            data: {
                action: 'm2m_vote',
                quote_id: quoteId,
                vote_type: voteType
            },
            success: function(response) {
                if (response.success) {
                    alert('Your vote has been recorded.');
                }
            }
        });
    });

    // Share button functionality
    $('.m2m-share').on('click', function(e) {
        e.preventDefault();
        var platform = $(this).data('platform');
        var shareUrl = window.location.href;

        if (platform === 'twitter') {
            window.open('https://twitter.com/share?url=' + shareUrl, '_blank');
        } else if (platform === 'facebook') {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + shareUrl, '_blank');
        } else if (platform === 'linkedin') {
            window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + shareUrl, '_blank');
        } else if (platform === 'copy') {
            navigator.clipboard.writeText(shareUrl);
            alert('Link copied to clipboard.');
        }
    });
});
