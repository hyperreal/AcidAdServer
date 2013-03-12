(function($) {
    $(document).ready(function() {
        $("select").change(getPrices);
        getPrices();
    });

})(jQuery);

function getPrices() {
    var from = getForm('from', 'year') + '-' + getForm('from', 'month') + '-' + getForm('from', 'day');
    var to = getForm('to', 'year') + '-' + getForm('to', 'month') + '-' + getForm('to', 'day');

    $.post('./calculate', {"from": from, "to": to}, function(data) {
        $("#days-info").html(Mustache.render($("#price-tpl").html(), data)).show();
    }, 'json');
}

function getForm(dir, per) {
    return $('#hyper_payment_form_pay_' + dir + '_' + per).val();
}