jQuery(document).ready(function($) {
    var synchActive = false;
    var currentProductSynchValue = 0;
    var step = parseFloat($('#progress_step').val());
    var productSynchPage = 1;
    var hitCount = parseInt($('.hitCount').html());
    function updateSynchProductTotal()
    {
        var data = {
            'action': 'warehousespace_get_product_sync_total'
        };
        jQuery.post(ajaxurl, data, function(response) {
            $('.hitCount').html(response.total);
        });
    }
    function synchProductBatch(currentProductSynchValue, step, productSynchPage)
    {
        synchActive = true;

        currentProductSynchValue = Math.ceil(currentProductSynchValue+step);
        if (currentProductSynchValue > 100) {
            currentProductSynchValue = 100;
        }

        var data = {
            'action': 'warehousespace_product_sync',
            'page': productSynchPage
        };
        jQuery.post(ajaxurl, data, function(response) {
            $('.warehousespace-progress').attr('value', currentProductSynchValue);
            productSynchPage++;


            if (response.errors.length > 0) {
                $('.showWhenInProgress strong').html('Error!');
            } else {
                if (currentProductSynchValue < 100) {
                    synchProductBatch(currentProductSynchValue, step, productSynchPage);
                } else {
                    hitCount += 0;
                    $('.showWhenInProgress strong').html('Synchonization complete');
                    synchActive = false;
                    productSynchPage = 1;
                    updateSynchProductTotal();
                }
            }
            console.log('Got this from the server: ' + response);
        });
    }
    $('#synchronize-products').click(function() {
        $('.showWhenInProgress').css({'display': 'block'});
        $('.hitCount').html('?');
        var currentProductSynchValue = parseInt($('.warehousespace-progress').attr('value'));

        if (synchActive === false) {
            synchProductBatch(currentProductSynchValue, step, productSynchPage);
        }
    });
});