(function($) {
    // Listen for autocomplete event on the search form
    $(document).on('input', '.search-form input', function() {
        // Assuming the autocomplete functionality is already handled by the third party
        // Listen for when autocomplete results are added to the DOM

        // Use a timeout to ensure the autocomplete list is populated
        setTimeout(function() {
            // Find all links with the class 'category-link'
            $('.category-link').each(function() {
                // Get the current href attribute
                var href = $(this).attr('href');
                
                // Check if the nonce is already in the URL
                if (!href.includes('nonce=')) {
                    // Append the nonce from searchData (passed via wp_localize_script)
                    var newHref = href + (href.indexOf('?') !== -1 ? '&' : '?') + 'nonce=' + convertopia_settings.search_nonce;
                    $(this).attr('href', newHref);
                }
            });
        }, 2000); // Adjust timeout as necessary to match when autocomplete populates
    });
})(jQuery);
