//dentro de este tipo de funciones se pueden definir variables y otras funciones
var authenticated = function () {
    var obj = null;

    var notificacionesAction = function () {
        $.ajax({
            url: Routing.generate('notificacion_index',{'_format':'json'}),
            type: "GET",
            success: function (data) {
                if(data['contador']>0)
                    $('span#notificacion_contador').append("<span class='m-nav__link-badge m-badge m-badge--danger'>"+data['contador']+"</span>");
                $('div#notificacion_content').html(data['html']);
            },
            error: function () {
                base.Error();
            }
        });
    }

    //CONFIGURACION DE LOS CAMPOS DEL FORMULARIO DE MENSAJES
    var configurarFormularioMensaje = function () {
        $('select#mensaje_iddestinatario').select2({
            dropdownParent: $("#basicmodal"),
            ajax: {
                url: Routing.generate('autor_ajax'),
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        //results: [{'id': 00, 'text' : 'tag-name' }]
                        results: data
                    };
                },
                cache: true
            }
            //allowClear: true
        });
        Ladda.bind('.mt-ladda-btn');
        $("div#basicmodal form#message_new").validate({
            rules:{
                'mensaje[iddestinatario][]': {required:true},
                'mensaje[descripcion]': {required:true},
            }
        });
    }

    var cargarMensajes = function () {
        var link = Routing.generate('mensaje_recent');
        $.ajax({
            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
            url: link,
            beforeSend: function (data) {
                // base.blockUI({message: 'Cargando'});
            },
            success: function (data) {
                if(data['contador']>0)
                    $('span#mensaje_contador').append("<span class='m-nav__link-badge m-badge m-badge--danger'>"+data['contador']+"</span>");
                $('div#mensaje_content').html(data['html']);
            },
            error: function () {
                base.Error();
            },
            complete: function () {
                //  base.unblockUI();
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
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
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
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
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
                        /* if ($('table#table_usuario')) {
                             var pagina = table.page();
                             obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                             obj.parents('tr').children('td:nth-child(3)').html(data['apellido']);
                             obj.parents('tr').children('td:nth-child(4)').html(data['usuario']);
                             obj.parents('tr').children('td:nth-child(5)').html("<span class='badge badge-"+data['badge_color']+"'>"+data['badge_texto']+"</span>");
                         }*/
                        $('div#basicmodal').modal('hide');
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
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
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

    var sugerirAutores = function () {
        $.ajax({
            type: "GET",
            dataType: 'html',
            url: Routing.generate('autor_sugerir'),
            beforeSend: function (data) {
                mApp.block("div#sugerencia_table",
                    {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
            },
            success: function (data) {
                $('div#sugerencia_table').html(data);
            },
            error: function () {
                base.Error();
            },
            complete: function () {
                mApp.unblock("div#sugerencia_table")
            }
        });
    }

    return {
        init: function () {
            $().ready(function(){
                notificacionesAction();
                cargarMensajes();
                enviarMensaje();
                enviarMensajeAction();
                mensajeShow();
            });
        },
        sugerirAutores: function () {
            $().ready(function(){
                sugerirAutores();
            });
        },
    };
}();