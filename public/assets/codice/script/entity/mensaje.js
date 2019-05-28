var mensaje = function () {
    var table = null;
    var obj = null;
    var bandeja=0;

    var configurarDataTable = function () {
        table = $('table#table_mensaje').DataTable({
                "pagingType": "simple_numbers",
                "language": {
                    url: datatable_url
                },
                columns: [
                    {data: 'descripcion'},
                    {data: 'fecha'},
                    {data: 'acciones'}
                ]
            }
        );
    };

    var refrescar = function () {
        $('a.messagebox').click(function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                //dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body", {
                        overlayColor: "#000000",
                        type: "loader",
                        state: "success",
                        message: 'Cargando'
                    });
                },
                success: function (data) {
                    $('table#table_mensaje').html(data['messages']);
                    $('small#message_inbox').html(data['message_inbox']);
                    bandeja=data['bandeja'];
                    if(bandeja==0)
                        $('table#table_mensaje').removeClass('message-send');
                    else
                        $('table#table_mensaje').addClass('message-send');
                    table.destroy();
                    configurarDataTable();
                },
                error: function () {
                    //base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    };


    var eliminar = function () {
        $('table#table_mensaje').on('click', 'a.eliminar_mensaje', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar mensaje",
                message: "<p>¿Está seguro que desea eliminar este mensaje?</p>",
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
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            // dataType: 'html', esta url se comentarea porque lo k estamos mandando es un json y no un html plano
                            url: link,
                            data: {
                                _token: token
                            },
                            beforeSend: function () {
                                mApp.block("body", {
                                    overlayColor: "#000000",
                                    type: "loader",
                                    state: "success",
                                    message: window.loadingMessage
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
                                //base.Error();
                            }
                        });
                }
            });
        });
    };

    return {
        init: function () {
            $().ready(function () {
                configurarDataTable();
                refrescar();
                eliminar();
                }
            );
        },
    }
}();


