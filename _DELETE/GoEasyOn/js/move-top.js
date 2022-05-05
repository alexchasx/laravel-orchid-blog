$(document).ready(function() {

    $(window).scroll(function() {
        // Высота проявления кнопки
        if ($(this).scrollTop() > 200) {
            $('#toTop').fadeIn();
        } else {
            $('#toTop').fadeOut();
        }
    });
    
    $('#toTop').click(function() {
        $('body,html').animate({
            scrollTop: 0
        // Скорость подъема
        }, 1000);
        return false;
    });

});