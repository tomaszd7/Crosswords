$(document).ready(function () {
    $('td').click(function () {
        if ($(this).hasClass('active')) {
            $(this).children('div').html(' ');
        } else {
            $(this).children('div').html('A');
        }
        $(this).toggleClass('active');

    })
})
