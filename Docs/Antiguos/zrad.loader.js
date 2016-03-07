/**
 * Zrad loader plugin
 * Este plugin se usa para agregar un loader en el backend de las aplicaciones
 * esta adaptado de jQuery UI Dialog
 * @name zrad.loader.js
 * @author Juan Victor Minaya Leon - http://www.zend-rad.com
 * @version 0.1
 * @date April 11, 2011
 * @category Zrad plugin
 * @copyright (c) 2011 Zrad Company (info@zend-rad.com)
 * @license CCAttribution-ShareAlike 2.5 Brazil - http://creativecommons.org/licenses/by-sa/2.5/br/deed.en_US
 * @example Visit http://leandrovieira.com/projects/jquery/lightbox/ for more informations about this jQuery plugin
 */

(function($){

    var settings = {
        autoOpen : true,
        message : 'Cargando...',
        width : 77,
        height : 50,
        opacity : 0.3,
        cWidth : 0,
        cHeight : 10
    };

    var methods = {
        init : function (options){
            $.extend(settings, options);
            var width = $.trim(settings.message).length * 7;
            if(width >= settings.width)
                settings.width = width;
            var shadow = settings.width + 20;

            $('#zrad-loader-ui .ui-overlay .ui-widget-overlay').css('opacity', settings.opacity);
            $('#zrad-loader-ui .ui-overlay .ui-widget-overlay').css('height', $(window).height());
            $('#zrad-loader-ui .ui-widget-content:first').css('width', settings.width + settings.cWidth);
            $('#zrad-loader-ui .ui-widget-content:first').css('height', settings.height + settings.cHeight);
            $('#zrad-loader-ui .ui-widget-content:first').css('margin-left', -settings.width/2-3-12);
            $('#zrad-loader-ui .ui-overlay .ui-widget-shadow').css('width', shadow);
            $('#zrad-loader-ui .ui-overlay .ui-widget-shadow').css('margin-left', -shadow/2-12);
            $('#zrad-loader-ui .ui-dialog-content').html(settings.message);
            if (settings.autoOpen) {
                methods.show();
            }
        },
        show : function () {
            $('#zrad-loader-ui').show();
        },
        hide : function () {
            $('#zrad-loader-ui').hide();
        },
        destroy : function (){
            $('#zrad-loader-ui').hide();
        }
    };

    $.zradUILoader = function(method){                
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        return methods.init.apply(this, arguments);
    };
})(jQuery);


