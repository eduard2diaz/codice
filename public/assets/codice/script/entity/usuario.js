var usuario = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#usuario_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'correo'},
                {data: 'activo'},
                {data: 'acciones'}
            ]
        });
    }

    var refrescar = function () {
        $('a#usuario_tablerefrescar').click(function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get',
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Actualizando..."});
                },
                success: function (data) {
                    $('table#usuario_table').html(data);
                    table.destroy();
                    configurarDataTable();
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

    var show = function () {
        $('body').on('click', 'a.usuario_show', function (evento) {
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

    var configurarFormulario = function () {
        $("div#basicmodal form").validate({
            rules: {
                'usuario[nombre]': {required: true},
                'usuario[usuario]': {required: true},
                'usuario[email]': {required: true},
                'usuario[password][second]': {equalTo: "#usuario_password_first"},
            }
        })
    }

    var edicion = function () {
        $('body').on('click', 'a.edicion', function (evento) {
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
                        configurarFormulario();
                        $('div#basicmodal').modal('show');
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

    var eliminar = function () {
        $('table#usuario_table').on('click', 'a.eliminar_usuario', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: 'Eliminar administrador',
                message: '¿Está seguro que desea eliminar este administrador?',
                buttons: {
                    confirm: {
                        label: 'Si, estoy seguro',
                        className: 'btn-sm btn-primary'
                    },
                    cancel: {
                        label: 'Cancelar',
                        className: 'btn-sm btn-metal'
                    }
                },
                callback: function (result) {
                    if (result == true)
                        $.ajax({
                            type: 'get',
                            url: link,
                            data: {
                                _token: token
                            },
                            beforeSend: function () {
                                mApp.block("body",
                                    {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        message: "Eliminando..."
                                    });
                            },
                            complete: function () {
                                mApp.unblock("body")
                            },
                            success: function (data) {
                                table.row(obj.parents('tr'))
                                    .remove()
                                    .draw('page');
                                toastr.success(data['mensaje']);
                            },
                            error: function () {
                                base.Error();
                            }
                        });
                }
            });
        });
    }

    function previewfile(evt) {
        var files = evt.target.files; // FileList object

        // Obtenemos la imagen del campo "file".
        for (var i = 0, f; f = files[i]; i++) {
            //Solo admitimos imágenes.
            if (!f.type.match('image.*')) {
                continue;
            }
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    // Insertamos la imagen
                    $('#foto_perfil').attr('src', e.target.result);
                };
            })(f);
            reader.readAsDataURL(f);
            $('a#reload_picture').removeClass('m--hidden-desktop');
        }
    }

    var gestionarFoto = function () {
        $('div#basicmodal').on('click', "img#foto_perfil", function (evento) {
            mApp.block("div#basicmodal",
                {overlayColor: "#000000", type: "loader", state: "success", message: "Explorando archivos ..."});
            $('input#usuario_file').click();
            mApp.unblock("div#basicmodal")
            document.getElementById('usuario_file').addEventListener('change', previewfile, false);
        });
    }

    var reiniciarFoto = function () {
        $('body').on('click', 'a#reload_picture', function () {
            $('img#foto_perfil').attr('src', $('#foto_perfil').attr('data-image'));
            $('#usuario_file').val('');
            $('a#reload_picture').addClass('m--hidden-desktop');
        });
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', "form#usuario_new", function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    l.start();
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        var estado_label = data['activo'] == true ? 'SI' : 'NO';
                        var estado_class = data['activo'] == true ? 'success' : 'danger';
                        objeto = table.row.add({
                            "numero": total,
                            "nombre": data['nombre'],
                            "correo": data['correo'],
                            "activo": "<span class='m-badge m-badge--wide m-badge--" + estado_class + "'>" + estado_label + "</span>",
                            "acciones":
                            "<ul class='m-nav m-nav--inline m--pull-right'>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-secondary btn-sm usuario_show' data-href=" + Routing.generate('usuario_show', {id: data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-sm btn-info edicion' data-href=" + Routing.generate('usuario_edit', {id: data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-danger btn-sm  eliminar_usuario' data-csrf=" + data['csrf'] +" data-href=" + Routing.generate('usuario_delete', {id: data['id']}) + ">" +
                            "<i class='flaticon-delete-1'></i>Eliminar</a></li>" +
                            "</ul>",
                        });
                        objeto.draw();
                        table.page(pagina).draw('page');
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var edicionAction = function () {
        $('div#basicmodal').on('submit', "form#usuario_edit", function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    l.start();
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['correo']);
                        var estado_label = data['activo'] == true ? 'SI' : 'NO';
                        var estado_class = data['activo'] == true ? 'success' : 'danger';
                        var estado= "<span class='m-badge m-badge--wide m-badge--" + estado_class + "'>" + estado_label + "</span>";
                        obj.parents('tr').children('td:nth-child(4)').html(estado);
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }


    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    show();
                    edicion();
                    gestionarFoto();
                    reiniciarFoto();
                    newAction();
                    edicionAction();
                    eliminar();
                }
            );
        },
    }
}();
