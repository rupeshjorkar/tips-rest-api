jQuery(document).ready(function(jQuery) {
            // jQuery accordion functionality
            jQuery("#Tips_Api .accordion-button").click(function() {
                var content = jQuery(this).next(".accordion-content");

                // Slide up all other accordion contents
                jQuery("#Tips_Api .accordion-content").not(content).slideUp().prev("#Tips_Api .accordion-button").removeClass("active");

                // Toggle the clicked section
                content.slideToggle();
                jQuery(this).toggleClass("active");
            });
        });