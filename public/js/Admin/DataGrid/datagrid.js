$(document).ready(function(){
    $('ul.nav-tabs li a').each(function(){
        $(this).click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
});