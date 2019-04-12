var monografia = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#monografia_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'titulo'},
                {data: 'fecha'},
                {data: 'acciones'}
            ]
        });
    }

    var refrescar = function () {
        $('a#monografia_tablerefrescar').click(function (evento) {
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
                    $('table#monografia_tabletable').html(data);
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

    var eliminar = function () {
        $('table#monografia_table').on('click', 'a.eliminar_monografia', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            bootbox.confirm({
                title: 'Eliminar monografia',
                message: '¿Está seguro que desea eliminar esta monografia?',
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
        $('select#monografia_id_idautor').select2();
        $('select#monografia_id_estado').select2();
        $('select#monografia_tipoNorma').select2();
        $('input#monografia_id_fechaCaptacion').datepicker();
        $("body form[name='monografia']").validate({
            rules: {
                'monografia[id][titulo]': {required: true},
                'monografia[id][fechaCaptacion]': {required: true},
                'monografia[id][keywords]': {required: true},
                'monografia[id][file]': {required: true},
                'monografia[id][resumen]': {required: true},
                'monografia[id][estado]': {required: true},

                'monografia[isbn]': {required: true},
                'monografia[paginas]': {required: true},
                'monografia[cenda]': {required: true},
                'monografia[number]': {required: true},
            }
        });
    }

    var newAction = function () {
        $('body').on('submit', "form[name='monografia']", function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Guardando..."});
                },
                complete: function () {
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    } else {
                        window.location.href = data['ruta']
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
                    eliminar();
                }
            );
        },
        nuevo: function () {
            $().ready(function () {
                    configurarFormulario();
                    newAction();
                }
            );
        },

    }
}();
