$(document).ready(function() {
    // Handle star rating hover and click
    $('.stars i').hover(
        function() {
            // On hover in
            var rating = $(this).data('rating');
            var starsContainer = $(this).parent();
            var stars = starsContainer.find('i');
            
            // Store the current state before hovering if not already stored
            if (!starsContainer.data('hover-active')) {
                var currentState = [];
                stars.each(function() {
                    currentState.push($(this).hasClass('fa-star'));
                });
                starsContainer.data('original-state', currentState);
                starsContainer.data('hover-active', true);
            }
            
            // Reset all stars
            stars.removeClass('fa-star').addClass('far fa-star');
            
            // Fill stars up to the hovered one
            for (var i = 0; i < rating; i++) {
                $(stars[i]).removeClass('far fa-star').addClass('fa fa-star');
            }
        },
        function() {
            // On hover out
            var starsContainer = $(this).parent();
            var stars = starsContainer.find('i');
            
            // Reset hover active flag
            starsContainer.data('hover-active', false);
            
            // Get the stored rating (if any)
            var storedRating = parseInt(starsContainer.attr('data-stored-rating') || 0);
            
            // Reset all stars first
            stars.removeClass('fa-star').addClass('far fa-star');
            
            // If there's a stored rating (user clicked), use that
            if (storedRating > 0) {
                for (var i = 0; i < storedRating; i++) {
                    $(stars[i]).removeClass('far fa-star').addClass('fa fa-star');
                }
            } 
            // Otherwise restore the original state before hovering
            else {
                var originalState = starsContainer.data('original-state');
                if (originalState) {
                    stars.each(function(index) {
                        if (originalState[index]) {
                            $(this).removeClass('far fa-star').addClass('fa fa-star');
                        }
                    });
                }
            }
        }
    );
    
    // Handle star click
    $('.stars i').click(function() {
        var rating = $(this).data('rating');
        var container = $(this).parent();
        var stars = container.find('i');
        
        // Reset all stars
        stars.removeClass('fa-star').addClass('far fa-star');
        
        // Fill stars up to the clicked one
        for (var i = 0; i < rating; i++) {
            $(stars[i]).removeClass('far fa-star').addClass('fa fa-star');
        }
        
        // Store the rating
        container.attr('data-stored-rating', rating);
        
        // Update the rating status
        var statusText = 'You rated this ' + rating + ' star' + (rating > 1 ? 's' : '');
        container.siblings('.rating-status').html('<span class="rated">' + statusText + '</span>');
    });
    
    // Handle submit button click
    $('.submit-rating').click(function() {
        var date = $(this).data('date');
        var option = $(this).data('option');
        var starsContainer = $('.stars[data-date="' + date + '"][data-option="' + option + '"]');
        var rating = parseInt(starsContainer.attr('data-stored-rating') || 0);
        var comment = $('.comment[data-date="' + date + '"][data-option="' + option + '"]').val();
        
        // Validate rating
        if (rating < 1 || rating > 5) {
            alert('Please select a rating between 1 and 5 stars.');
            return;
        }
        
        // Submit the rating
        $.ajax({
            url: 'submit_rating.php',
            type: 'POST',
            data: {
                rating: rating,
                date: date,
                option: option,
                comment: comment
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Thank you for your rating!');
                    
                    // Disable the submit button temporarily
                    var submitButton = $('.submit-rating[data-date="' + date + '"][data-option="' + option + '"]');
                    submitButton.prop('disabled', true).text('Submitted!');
                    setTimeout(function() {
                        submitButton.prop('disabled', false).text('Submit Rating');
                    }, 3000);
                } else {
                    // Show error message
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while submitting your rating. Please try again.');
            }
        });
    });
    
    // Initialize stored ratings
    $('.stars').each(function() {
        var container = $(this);
        var stars = container.find('i');
        var storedRating = 0;
        
        // Find if any star is already filled
        stars.each(function(index) {
            if ($(this).hasClass('fa-star')) {
                storedRating = index + 1;
            }
        });
        
        // Store the rating
        if (storedRating > 0) {
            container.attr('data-stored-rating', storedRating);
        }
    });
});