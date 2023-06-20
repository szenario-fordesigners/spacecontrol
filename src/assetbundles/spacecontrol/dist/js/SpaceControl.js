(function ($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.SpaceControlWidget = Garnish.Base.extend({
        init: function (widgetId) {
            let size = $('.sCC-circleContainer').data().size;
            setTimeout(() => {
                $('.sCC-circleInner').css({'width': size + '%', 'height': size + '%'});
                $('.sCC-percentage').addClass('animate');

            }, 500);
            $('.sCC-percentage').text(size + '%');
            counter(size, 2000);
            
        }
    });
})(jQuery);

/** countUp Function */
function counter(targetValue, duration) {
    var initialValue = 0;
    var startTime = performance.now();

    function updateCounter(timestamp) {
        var elapsedTime = timestamp - startTime;
        if (elapsedTime >= duration) {
            console.log(targetValue); // Counter reached the target value
            $('.sCC-percentage').text(Math.round(targetValue)+'%');
        } else {
            var currentValue = easeInOutQuad(elapsedTime / duration) * targetValue;
            $('.sCC-percentage').text(Math.round(currentValue)+'%');
            requestAnimationFrame(updateCounter);
        }
    }

    requestAnimationFrame(updateCounter);
}

/** easing Function */
function easeInOutQuad(t) {
    t /= 0.5;
    if (t < 1) return 0.5 * t * t;
    t--;
    return -0.5 * (t * (t - 2) - 1);
}
