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


        }
    });
})(jQuery);
