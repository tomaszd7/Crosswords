$(document).ready(function () {
    $(window).keyup(function(event) {
        if (event.keyCode === 13) {
            console.log('Enter pressed');
            location.reload();
        }
    });
});
