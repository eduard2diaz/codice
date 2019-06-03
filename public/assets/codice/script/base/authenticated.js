//dentro de este tipo de funciones se pueden definir variables y otras funciones
var authenticated = function () {
    var obj = null;
    var cantidadNotificaciones = 0;
    var cantidadMensajes = 0;

    var notificacionesAction = function () {
        $.ajax({
            url: Routing.generate('notificacion_index', {'_format': 'json'}),
            type: "GET",
            success: function (data) {
                if (data['contador'] > 0) {
                    $('span#notificacion_contador').append("<span class='m-nav__link-badge m-badge m-badge--danger'>" + data['contador'] + "</span>");
                    cantidadNotificaciones = data['contador'];
                }
                $('div#notificacion_content').html(data['html']);
            },
            error: function () {
                //       base.Error();
            }
        });
    }

    var notificacionShow = function () {
        $('body').on('click', 'a.notificacion_show', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get',
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        $('div#basicmodal').modal('show');
                        if (cantidadNotificaciones > 0 && !obj.hasClass('notificacion-vista')) {
                            cantidadNotificaciones--;
                            if (cantidadNotificaciones == 0) {
                                $('span#notificacion_contador span.m-nav__link-badge').html('').removeClass('m-nav__link-badge m-badge m-badge--danger');
                            } else
                                $('span#notificacion_contador span.m-nav__link-badge').html(cantidadNotificaciones);
                        }
                        obj.addClass('notificacion-vista');
                    }
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    //CONFIGURACION DE LOS CAMPOS DEL FORMULARIO DE MENSAJES
    var configurarFormularioMensaje = function () {
        $('select#mensaje_iddestinatario').select2({
            dropdownParent: $("#basicmodal"),
            ajax: {
                url: Routing.generate('autor_searchfilter'),
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $("div#basicmodal form#message_new").validate({
            rules: {
                'mensaje[iddestinatario][]': {required: true},
                'mensaje[descripcion]': {required: true},
                'mensaje[asunto]': {required: true},
            }
        });
    }

    var cargarMensajes = function () {
        var link = Routing.generate('mensaje_recent');
        $.ajax({
            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
            url: link,
            beforeSend: function (data) {
            },
            success: function (data) {
                if (data['contador'] > 0) {
                    $('span#mensaje_contador').append("<span class='m-nav__link-badge m-badge m-badge--danger'>" + data['contador'] + "</span>");
                    cantidadMensajes=data['contador'];
                }
                $('div#mensaje_content').html(data['html']);
            },
            error: function () {
                //   base.Error();
            },
            complete: function () {
            }
        });
    }

    //ESCUCHA DE EVENTO DE ENVIO DE MENSAJES
    var enviarMensaje = function () {
        $('body').on('click', 'a.enviarmensaje', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        configurarFormularioMensaje();
                        $('div#basicmodal').modal('show');
                    }
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body");
                }
            });
        });
    }

    //PROCESAMIENTO DEL FORMULARIO DE ENVIO DE MENSAJES
    var enviarMensajeAction = function () {
        $('div#basicmodal').on('submit', 'form#message_new', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                complete: function () {
                    l.stop();
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormularioMensaje();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);
                        $('div#basicmodal').modal('hide');

                        if($('table#table_mensaje') && $('table#table_mensaje').hasClass('message-send')){
                            $('a.messagebox').click();
                        }
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var mensajeShow = function () {
        $('body').on('click', 'a.mensaje_show', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        $('div#basicmodal').modal('show');


                        if (cantidadMensajes > 0 && !obj.hasClass('mensaje-visto')) {
                            cantidadMensajes--;
                            if (cantidadMensajes == 0) {
                                $('span#mensaje_contador span.m-nav__link-badge').html('').removeClass('m-nav__link-badge m-badge m-badge--danger');
                            } else
                                $('span#mensaje_contador span.m-nav__link-badge').html(cantidadMensajes);
                        }
                        obj.addClass('mensaje-visto');
                    }
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body");
                }
            });
        });
    }



    return {
        init: function () {
            $().ready(function () {
                //Personalizo el formato de datepicker para cuando me seleccionan una fecha
                $.fn.datepicker.defaults.format = 'yyyy-mm-dd';
                notificacionShow();
                notificacionesAction();
                cargarMensajes();
                enviarMensaje();
                enviarMensajeAction();
                mensajeShow();
            });
        },
    };
}();