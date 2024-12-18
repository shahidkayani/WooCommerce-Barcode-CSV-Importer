jQuery(document).ready(function($) {
    $('#barcode_input').on('change', function() {
        const barcode = $(this).val();
        
        $.post(ajaxurl, {
            action: 'wc_find_product_by_barcode',
            barcode: barcode
        }, function(response) {
            if(response.success) {
                $('#product_details').text(response.data.name + " - Stock: " + response.data.stock);
            } else {
                $('#product_details').text('Product not found');
            }
        });
    });
});
