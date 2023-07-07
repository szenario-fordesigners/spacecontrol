let refreshIntervalId = undefined;

(function ($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.SpaceControlWidget = Garnish.Base.extend({
        init: function (widgetId) {
            let isInitalized = !!$('.sCC').data()?.initialized;
            console.log('isInitalized', isInitalized);

            if (!isInitalized) {
                refreshIntervalId = setInterval(() => {
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
        //console.log('Kreis Radius Gesamt: ' + circularRadius);
        //console.log('Kreis Fläche Gesamt: ' + circularArea);
        //console.log('Kreis Fläche Prozentuell: ' + circularAreaPercent);
        //console.log('Kreis Radius Prozentuell: ' + radiusPercentualCircle);

        if (radiusPercentualCircle > circularRadius) {
            radiusPercentualCircle = circularRadius;
        }

        $('.sCC-circleInner').css({
            'width': radiusPercentualCircle * 2 + 1 + 'px',
            'height': radiusPercentualCircle * 2 + 1 + 'px'
        });
        $('.sCC-percentage').addClass('animate');
        //console.log('Percent: ' +  usage);

    }, 500);
    $('.sCC-percentage').text(usage + '%');
    counter(usage, 2000);
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