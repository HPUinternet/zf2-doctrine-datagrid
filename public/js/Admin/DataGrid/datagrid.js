$(document).ready(function () {
    // Activate tab-bars foreach form fieldset
    $('ul.nav-tabs li a').each(function () {
        $(this).click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });

    // $('tr.simpleSearch input[type="checkbox"]').bootstrapSwitch();
});