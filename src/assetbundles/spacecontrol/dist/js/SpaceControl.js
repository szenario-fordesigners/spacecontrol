let refreshIntervalId = undefined;

(function ($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.SpaceControlWidget = Garnish.Base.extend({
        init: function (widgetId) {
            let isConfigured = !!$('.sCC').data()?.configured;
            console.log('isConfigured', isConfigured);
            if (!isConfigured) return;

            let isInitalized = !!$('.sCC').data()?.initialized;
            if (!isInitalized) {
                refreshIntervalId = setInterval(() => {
                    console.log('refresh');
                    htmx.trigger('.sCC', 'refresh');
                    isInitalized = !!$('.sCC').data()?.initialized;

                    if (isInitalized) {
                        clearInterval(refreshIntervalId);
                        drawCircle();
                    }
                }, 500);
            } else {
                drawCircle();
            }

        }
    });
})(jQuery);


function drawCircle() {
    let usage = $('.sCC-circleContainer').data().usage;
    setTimeout(() => {
        let circularRadius = $('.sCC-circle').width() / 2;
        let circularArea = circularRadius * circularRadius * Math.PI;
        let circularAreaPercent = circularArea / 100 * usage;
        let radiusPercentualCircle = Math.sqrt(circularAreaPercent / Math.PI);

        if (radiusPercentualCircle > circularRadius) {
            radiusPercentualCircle = circularRadius;
        }

        $('.sCC-circleInner').css({
            'width': radiusPercentualCircle * 2 + 'px',
            'height': radiusPercentualCircle * 2 + 'px'
        });
        $('.sCC-percentage').addClass('animate');

    }, 500);

    if (usage >= 99.5) {
        $('.sCC-percentage').text('full');
    } else {
        $('.sCC-percentage').text(usage + '%');
        counter(usage, 2000);
    }


}

/** countUp Function */
function counter(targetValue, duration) {
    var initialValue = 0;
    var startTime = performance.now();

    function updateCounter(timestamp) {
        var elapsedTime = timestamp - startTime;
        if (elapsedTime >= duration) {
            $('.sCC-percentage').text(Math.round(targetValue) + '%');
        } else {
            var currentValue = easeInOutQuad(elapsedTime / duration) * targetValue;
            $('.sCC-percentage').text(Math.round(currentValue) + '%');
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