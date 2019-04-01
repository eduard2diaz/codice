var encuentro = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#encuentro_table').DataTable({
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
        $('a#encuentro_tablerefrescar').click(function (evento) {
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
                    $('table#encuentro_tabletable').html(data);
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
        $('table#encuentro_table').on('click', 'a.eliminar_encuentro', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            bootbox.confirm({
                title: 'Eliminar encuentro',
                message: '¿Está seguro que desea eliminar este encuentro?',
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
        $('select#encuentro_id_pais').select2();
        $('select#encuentro_id_idautor').select2();
        $('select#encuentro_id_estado').select2();
        $('select#encuentro_tipoEncuentro').select2();
        $('select#encuentro_organizador').select2();
        $('input#encuentro_id_fechaCaptacion').datepicker();
        $("body form[name='encuentro']").validate({
            rules: {
                'encuentro[id][titulo]': {required: true},
                'encuentro[id][pais]': {required: true},
                'encuentro[id][fechaCaptacion]': {required: true},
                'encuentro[id][keywords]': {required: true},
                'encuentro[id][file]': {required: true},
                'encuentro[id][resumen]': {required: true},
                'encuentro[id][estado]': {required: true},

                'encuentro[isbn]': {required: true},
                'encuentro[issn]': {required: true},
                'encuentro[organizador]': {required: true},
                'encuentro[ciudad]': {required: true},
                'encuentro[tipoEncuentro]': {required: true},
            }
        });
    }

    var newAction = function () {
        $('body').on('submit', "form[name='encuentro']", function (evento) {
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
