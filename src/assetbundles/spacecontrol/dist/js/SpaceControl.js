let refreshIntervalId = undefined;

(function ($) {
    /** global: Craft */
    /** global: Garnish */
    Craft.SpaceControlWidget = Garnish.Base.extend({
        init: function (widgetId) {
            let isConfigured = !!$('.sCC').data()?.configured;
            if (!isConfigured) return;

            let isInitalized = !!$('.sCC').data()?.initialized;
            if (!isInitalized) {
                refreshIntervalId = setInterval(() => {
                    htmx.trigger('.sCC', 'refresh');
                    isInitalized = !!$('.sCC').data()?.initialized;

                    if (isInitalized) {
                        clearInterval(refreshIntervalId);
                        this.drawCircle();
                    }
                }, 500);
            } else {
                this.drawCircle();
            }
        },

        drawCircle: function() {
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
                this.counter(usage, 2000);
            }
        },

        counter: function(targetValue, duration) {
            var startTime = performance.now();
            var self = this;

            function updateCounter(timestamp) {
                var elapsedTime = timestamp - startTime;
                if (elapsedTime >= duration) {
                    $('.sCC-percentage').text(Math.round(targetValue) + '%');
                } else {
                    var currentValue = self.easeInOutQuad(elapsedTime / duration) * targetValue;
                    $('.sCC-percentage').text(Math.round(currentValue) + '%');
                    requestAnimationFrame(updateCounter);
                }
            }

            requestAnimationFrame(updateCounter);
        },

        easeInOutQuad: function(t) {
            t /= 0.5;
            if (t < 1) return 0.5 * t * t;
            t--;
            return -0.5 * (t * (t - 2) - 1);
        }
    });
})(jQuery);