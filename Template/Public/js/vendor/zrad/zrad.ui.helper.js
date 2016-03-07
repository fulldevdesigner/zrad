(function($){        

    var settings = {
        baseUrl : '',
        entity : null,
        controller : 'multimedia',
        id : null,
        column : null,
        container : null,
        width : 0,
        height : 0,
        title : '',
        message : ''
    };

    var methods = {
        init : function (options){
            $.extend(settings, options);

        },
        openImage : function () {
            $.zradUILoader({
                message: 'Cargando imagen...', 
                cWidth: -8, 
                cHeight: -6
            });
            $.zradUILoader('show');
            $.post(settings.baseUrl + '/admin/' + settings.controller + '/get-dimension', {
                idEntity: settings.id,
                nameEntity: settings.entity,
                nameImage: settings.column
            }, function(response){
                
                if (response.state) {
                    response = eval(response);
                    var dlgWidth = response['width'];
                    var dlgHeight = response['height'];

                    // Creamos el contendor
                    var dlgImage = $('<div id="dlgImage" class="dlgImage"></div>').appendTo(this);
                    var panImage = $('<div id="panImage" class="panImage"></div>').appendTo(dlgImage);
                    var panSrcImage = $('<img id="panSrcImage" src="' + settings.baseUrl + '/img/zrad/imagen.gif" alt="imagen" />').appendTo(panImage);

                    //redimensionamos el panel de la imagen
                    panImage.css('width', dlgWidth);
                    panImage.css('height', dlgHeight);
                    //creamos el dialogo
                    dlgImage.dialog({
                        title: 'IMAGEN',
                        modal: true,
                        autoOpen: true,
                        resizable: false,
                        width: dlgWidth + 25,
                        minHeight: dlgHeight + 40,
                        open: function(){
                            panSrcImage.hide();
                            panSrcImage.attr('src', response['path'])
                            panSrcImage.show();
                            $.zradUILoader('hide');
                        },
                        buttons: {
                            Cerrar: function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                } else {
                    var dlgModal = $('<div id="zrad-dialog"></div>')
                    .html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;">&nbsp;</span>')
                    .appendTo(this);
                    var messageModal = $('<span id="message-dialog">&nbsp;</span>').appendTo(dlgModal);

                    dlgModal.dialog({
                        title: 'IMAGEN',
                        modal: true,
                        autoOpen: true,
                        resizable:false,
                        width:280,
                        show: "fade",
                        minHeight:180,
                        overlay: {
                            backgroundColor: '#000',
                            opacity: 0.1
                        },
                        open: function() {
                            messageModal.html("No existe imagen para mostrar");
                            $.zradUILoader('hide');
                        },
                        buttons: {
                            Aceptar: function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                }
                                
            },'json');
        },        
        showImage : function () {
            settings.container.dialog({
                title:'IMAGEN',
                modal: true,
                autoOpen: true,
                resizable: false,
                width: settings.width + 25,
                minHeight: settings.height + 40,
                overlay: {
                    backgroundColor: '#000',
                    opacity: 0.1
                },
                buttons: {
                    Cerrar: function() {
                        $(this).dialog('close');
                    }
                }
            });
        },
        redirect : function (url) {
            location.href = url;
        },
        dialog : function () {
            
            var dlgModal = $('<div id="zrad-dialog"></div>')
            .html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;">&nbsp;</span>')
            .appendTo(this);
            var messageModal = $('<span id="message-dialog">&nbsp;</span>').appendTo(dlgModal);

            dlgModal.dialog({
                title: settings.title,
                modal: true,
                autoOpen: true,
                resizable:false,
                width:280,
                show: "fade",
                minHeight:180,
                overlay: {
                    backgroundColor: '#000',
                    opacity: 0.1
                },
                open: function() {
                    messageModal.html(settings.message);
                },
                buttons: {
                    Aceptar: function() {
                        $(this).dialog('close');
                    }
                }
            });
        },
        dialogExecute : function () {
            var dlgModal = $('<div id="zrad-dialog"></div>')
            .html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;">&nbsp;</span>')
            .appendTo(this);
            var messageModal = $('<span id="message-dialog">&nbsp;</span>').appendTo(dlgModal);

            dlgModal.dialog({
                title: settings.title,
                modal: true,
                autoOpen: true,
                resizable: false,
                width:280,
                show: "fade",
                minHeight:180,
                overlay: {
                    backgroundColor: '#000',
                    opacity: 0.1
                },
                open:function(){
                    messageModal.html(settings.message)
                },
                buttons: {
                    Cancelar: function() {
                        $(this).dialog('close');
                    },
                    Aceptar: function() {
                        callback();
                        $(this).dialog('close');
                    }
                }
            });
        },
        dialogExit : function () {
            
            var dlgModal = $('<div id="zrad-dialog"></div>')
            .html('<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;">&nbsp;</span>')
            .appendTo(this);
            var messageModal = $('<span id="message-dialog">&nbsp;</span>').appendTo(dlgModal);

            dlgModal.dialog({
                title: settings.title,
                modal: true,
                autoOpen: true,
                resizable:false,
                show: "fade",                
                overlay: {
                    backgroundColor: '#000',
                    opacity: 0.1
                },
                open:function(){
                    messageModal.html(settings.message)
                },
                buttons: {
                    Cancelar: function() {
                        $(this).dialog('close');
                    },
                    Aceptar: function() {
                        $(this).dialog('close');
                        url = settings.baseUrl + '/admin/login/logout/';
                        methods.redirect(url);                        
                    }
                }
            });
        },
        destroy : function () {

        },
        serializeObject: function () {
            if ( !this.length ) {
                return false;
            }

            var $el = this,
            data = {},
            lookup = data; //current reference of data

            $el.find(':input[type!="checkbox"][type!="radio"], input:checked').each(function() {
                // data[a][b] becomes [ data, a, b ]
                var named = this.name.replace(/\[([^\]]+)?\]/g, ',$1').split(','),
                cap = named.length - 1,
                i = 0;

                // Ensure that only elements with valid `name` properties will be serialized
                if ( named[ 0 ] ) {
                    for ( ; i < cap; i++ ) {
                        // move down the tree - create objects or array if necessary
                        lookup = lookup[ named[i] ] = lookup[ named[i] ] ||
                        ( named[i+1] == "" ? [] : {} );
                    }

                    // at the end, psuh or assign the value
                    if ( lookup.length != undefined ) {
                        lookup.push( $(this).val() );
                    }else {
                        lookup[ named[ cap ] ]  = $(this).val();
                    }

                    // assign the reference back to root
                    lookup = data;

                }
            });

            return data;
        }
    };

    $.fn.zradUIHelper = function(method){
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        return methods.init.apply(this, arguments);
    };

    $.zradUIHelper = function(method){
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        return methods.init.apply(this, arguments);
    };
})(jQuery);