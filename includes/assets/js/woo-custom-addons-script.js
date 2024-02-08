// console.log(custom_addons_params);

; (function ($) {
    // Function to update the original price based on selected extra options
    function updateOriginalPrice(response) {
        var extraOptionPrice = parseFloat(response);
        var originalPriceText = $('.product .price .amount bdi').text().trim();
        var originalPrice = parseFloat(originalPriceText.replace(/[^0-9.]/g, ''));
        var updatedPrice = originalPrice + extraOptionPrice;
        // Format the updated price and update the displayed price
        var formattedPrice = '$' + updatedPrice.toFixed(2);
        $('.product .price .amount bdi').html(formattedPrice);
    }


    // Trigger the updateOriginalPrice function when an extra option is selected
    $('input[name="wooaddon"]').on('change', function () {
        var selectedOption = $('input[name="wooaddon"]:checked').val();
        // console.log('Selected Option:', selectedOption);
        // console.log('customProductOptions:', custom_addons_params.customProductOptions);
        var extraOptionPrice = custom_addons_params.customProductOptions['customProductPrice'];
        // console.log('Extra Option Price:', extraOptionPrice);
        // Call the updateOriginalPrice function with the extra option price
        updateOriginalPrice(extraOptionPrice);
    });

})(jQuery);