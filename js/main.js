$(document).ready(function () {

    $(window).keyup(function(event) {
        if (event.keyCode === 13) {
            console.log('Key is 13');
//            $('input').submit();
//            $.post('#', {'reset': 'Reset Grid'});
            location.reload();
        }
    })
})
