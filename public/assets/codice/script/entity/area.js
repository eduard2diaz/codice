var area = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#area_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'institucion'},
                {data: 'acciones'}
            ]
        });
    }

    var configurarFormulario = function () {
        $('select#area_padre').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#area_pais').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#area_ministerio').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#area_institucion').select2({
            dropdownParent: $("#basicmodal"),
        });
        $("div#basicmodal form").validate({
            rules: {
                'area[nombre]': {required: true},
                'area[pais]': {required: true},
                'area[ministerio]': {required: true},
                'area[institucion]': {required: true},
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

    var show = function () {
        $('body').on('click', 'a.area_show', function (evento) {
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

    var refrescar = function () {
        $('a#area_tablerefrescar').click(function (evento) {
            evento.preventDefault();
            var link = $(this).attr('href');
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
                    $('table#area_table').html(data);
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

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#area_new', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(),
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
                        objeto = table.row.add({
                            "numero": total,
                            "nombre": data['nombre'],
                            "institucion": data['institucion'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-secondary area_show' data-href=" + Routing.generate('area_show', {id: data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>"
                                +"<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-info edicion' data-href=" + Routing.generate('area_edit', {id: data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-danger btn-sm  eliminar_area' data-csrf=" + data['csrf'] +" data-href=" + Routing.generate('area_delete', {id: data['id']}) + ">" +
                                "<i class='flaticon-delete-1'></i>Eliminar</a></li></ul>",
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
        $('div#basicmodal').on('submit', 'form#area_edit', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(),
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
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['institucion']);
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('table#area_table').on('click', 'a.eliminar_area', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: 'Eliminar área',
                message: '¿Está seguro que desea eliminar esta área?',
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

    var paisListener = function () {
        $('div#basicmodal').on('change', 'select#area_pais', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('ministerio_findbypais', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("div#basicmodal div.modal-body",
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
                        $('select#area_ministerio').html(cadena);
                        $('select#area_ministerio').change();
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("div#basicmodal div.modal-body")
                    }
                });
        });
    }

    var ministerioListener = function () {
        $('div#basicmodal').on('change', 'select#area_ministerio', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('institucion_findbyministerio', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("div#basicmodal div.modal-body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando instituciones..."
                            });
                    },
                    success: function (data) {
                        var cadena = "";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#area_institucion').html(cadena);
                        $('select#area_institucion').change();
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("div#basicmodal div.modal-body")
                    }
                });
        });
    }

    var institucionListener = function () {
        $('div#basicmodal').on('change', 'select#area_institucion', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('area_findbyinstitucion', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("div#basicmodal div.modal-body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando áreas..."
                            });
                    },
                    success: function (data) {
                        var cadena = "<option></option>";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#area_padre').html(cadena);
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("div#basicmodal div.modal-body")
                    }
                });
        });
    }

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    newAction();
                    show();
                    edicion();
                    edicionAction();
                    eliminar();
                    paisListener();
                    ministerioListener();
                    institucionListener();
                }
            );
        }
    }
}();
