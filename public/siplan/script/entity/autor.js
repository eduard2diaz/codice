var autor = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#autor_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'apellidos'},
                {data: 'pais'},
                {data: 'acciones'}
            ]
        });
    }

    var refrescar = function () {
        $('a#autor_tablerefrescar').click(function (evento) {
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
                    $('table#autor_tabletable').html(data);
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

    var subscribir = function () {
        $('a.follow-link').click(function (evento) {
            evento.preventDefault();
            var enlace = $(this);
            var link = $(this).attr('data-href');
            $.ajax({
                type: 'get',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if(data['mensaje'])
                        toastr.success(data['mensaje']);
                    enlace.children('i').removeClass();
                    enlace.children('i').addClass('m-nav__link-icon '+data['class']);
                    enlace.children('span').html(data['label']);
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
        $('table#autor_table').on('click', 'a.eliminar_autor', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            bootbox.confirm({
                title: 'Eliminar usuario',
                message: '¿Está seguro que desea eliminar este usuario?',
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

    var configurarFormulario = function () {
        $('select#autor_pais').select2();
        $('select#autor_ministerio').select2();
        $('select#autor_institucion').select2();
        $('select#autor_area').select2();
        $('select#autor_gradoCientifico').select2();
        $('select#autor_idrol').select2();
        $('select#autor_jefe').select2();
        Ladda.bind('.mt-ladda-btn');
        $("div#basicmodal form").validate({
            rules: {
                'autor[nombre]': {required: true},
                'autor[apellidos]': {required: true},
                'autor[pais]': {required: true},
                'autor[ministerio]': {required: true},
                'autor[institucion]': {required: true},
                'autor[area]': {required: true},
                'autor[gradoCientifico]': {required: true},
                'autor[usuario]': {required: true},
                'autor[email]': {required: true},
                'autor[password][first]': {required: true},
                'autor[password][second]': {required: true},
            }
        })
    }

    var paisListener = function () {
        $('body').on('change', 'select#autor_pais', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('ministerio_findbypais', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando ministerios..."
                            });
                    },
                    success: function (data) {
                        var cadena = "";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#autor_ministerio').html(cadena);
                        $('select#autor_ministerio').change();
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

    var ministerioListener = function () {
        $('body').on('change', 'select#autor_ministerio', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('institucion_findbyministerio', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando centros de trabajo..."
                            });
                    },
                    success: function (data) {
                        var cadena = "";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#autor_institucion').html(cadena);
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


    var reiniciarFoto = function () {
        $('div#basicmodal').on('click', 'a#reload_picture', function () {
            $('img#foto_perfil').attr('src', $('#foto_perfil').attr('data-image'));
            $('#autor_file').val('');
            $('a#reload_picture').addClass('m--hidden-desktop');
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

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    eliminar();
                }
            );
        },
        show: function () {
            $().ready(function () {
                subscribir();
                }
            );
        },
        seguidores: function () {
            $().ready(function () {
                configurarDataTable();
                }
            );
        },
        nuevo: function () {
            $().ready(function () {
                    configurarFormulario();
                    paisListener();
                    ministerioListener();

                    $('#foto_perfil').click(function () {
                        $('#autor_file').click();
                    });
                    document.getElementById('autor_file').addEventListener('change', previewfile, false);
                    reiniciarFoto();
                }
            );
        },

    }
}();
