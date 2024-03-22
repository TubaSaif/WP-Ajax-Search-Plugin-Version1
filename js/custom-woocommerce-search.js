
// -#--------------------------- Getting categories from custom_woocommerce_search_params object and show them to dropdown-------------------
jQuery(document).ready(function($) {
    // Populate category dropdown with dynamic options
    var categoryDropdown = $('#category-dropdown');
    var categories = custom_woocommerce_search_params.categories;

    categories.forEach(function(category) {
        categoryDropdown.append('<option value="' + category.slug + '">' + category.name + '</option>');
    });  
    
// -#--------------------------- AJAX search functionality - Submit Button ------------------------------------------
jQuery(document).ready(function($) {
    $('#search-form').on('submit', function(e) {
        e.preventDefault();

        var category = $('#category-dropdown').val();
        var searchQuery = $('#search-input').val();

        $.ajax({
            type: 'POST',
            url: custom_woocommerce_search_params.ajax_url,
            data: {
                action: 'custom_woocommerce_ajax_search',
                category: category,
                search_query: searchQuery,
            },
            success: function(response) {
                var shopURL = shopData.shopURL;
                var shopURLWithSearchQuery = shopURL + '?s=' + encodeURIComponent(searchQuery);
                
                window.location.href = shopURLWithSearchQuery;
            }
        });
    });
}); 
});

// -#----------------------- JQUERY Autocomplete code --------------------------------------------------------

jQuery(document).ready(function($) {
        // Function to set autocomplete width
        function setAutocompleteWidth() {
            var containerWidth = $('#search-container').outerWidth();
            $('.ui-autocomplete').css('width', containerWidth-3);
        }

    // Autocomplete functionality
    $('#search-input').autocomplete({
        source: function(request, response) {
            var selectedCategory = $('#category-dropdown').val();

                $('#loading-gif').show();


            $.ajax({
                type: 'POST',
                dataType : 'json',
                url: custom_woocommerce_search_params.ajax_url,
                data: {
                    action: 'custom_woocommerce_auto_suggest',
                    search_query: request.term,
                    category: selectedCategory
                },
                success: function (data) {
                    if (data.length === 0) {
                        // No results found
                        response([{ value: 'No results found', label: 'No results found' }]);
                    } else {
                        // Results found
                        autocompleteData = data;
                        console.log('Autocomplete Data:', data);
                        // Show only the first three items
                        var slicedData = data.slice(0, 3);
                        response(slicedData);

                        // If there are more than three items, show a view more button
                        if (data.length > 3) {
                            $('.ui-autocomplete').append('<li class="view-more">View More</li>');
                        }
                    }
                },
                error: function(error) {
                    response('No Result Found');
                },
                complete: function() {
                    // When the AJAX request completes
                    $('#loading-gif').hide();
                }
            });
        },
        minLength: 2, 
        select: function(event, ui) {
            if (ui.item) {
                var identifier = ui.item.permalink;
                productPageLink = identifier;
                window.location.href = productPageLink;
            } 
        },
        open: function(event, ui) {
            setAutocompleteWidth(); 
            $('.ui-autocomplete').find('li').each(function(index) {
                var image = autocompleteData[index].image;
                if (image) {
                    $(this).prepend('<img src="' + image + '" alt="Product Image" class="product-image" />');
                }
            });
        }
    }).focus(function() {
        $(this).autocomplete('search', $(this).val());
    });
// Handle view more button click
$(document).on('click', '.view-more', function() {
                        var searchQuery = $('#search-input').val();
                        var shopURL = shopData.shopURL;
                        var shopURLWithSearchQuery = shopURL + '?s=' + encodeURIComponent(searchQuery);
                        window.location.href = shopURLWithSearchQuery;
});
});

