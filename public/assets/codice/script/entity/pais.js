var pais = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#pais_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'capital'},
                {data: 'codigo'},
                {data: 'acciones'}
            ]});
    }

    var configurarFormulario = function () {
        $("div#basicmodal form").validate({
            rules:{
                'pais[nombre]': {required:true},
                'pais[capital]': {required:true},
                'pais[codigo]': {required:true},
            }
        })
    }

    var edicion = function () {
        $('body').on('click', 'a.edicion', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get',
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        configurarFormulario();
                        $('div#basicmodal').modal('show');
                    }
                },
                error: function ()
                {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var refrescar = function () {
        $('a#pais_tablerefrescar').click(function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('href');
            obj = $(this);
            $.ajax({
                type: 'get',
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    $('table#pais_table').html(data);
                    table.destroy();
                    configurarDataTable();
                },
                error: function ()
                {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }});
        });
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#pais_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
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
                    }
                    else
                    {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "numero": total,
                            "nombre": data['nombre'],
                            "capital": data['capital'],
                            "codigo": data['codigo'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-info edicion' data-href=" + Routing.generate('pais_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "</ul>",
                        });
                        objeto.draw();
                        table.page(pagina).draw('page');
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }



    var edicionAction = function () {
        $('div#basicmodal').on('submit', 'form#pais_edit', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
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
                    }
                    else
                    {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['capital']);
                        obj.parents('tr').children('td:nth-child(4)').html(data['codigo']);
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('div#basicmodal').on('click', 'a.eliminar_pais', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            $('div#basicmodal').modal('hide');
            bootbox.confirm({
                title: 'Eliminar país',
                message: '¿Está seguro que desea eliminar este país?',
                buttons: {
                    confirm: {
                        label: 'Si, estoy seguro',
                        className: 'btn-sm btn-primary'},
                    cancel: {
                        label: 'Cancelar',
                        className: 'btn-sm btn-metal'}
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
                                    {overlayColor:"#000000",type:"loader",state:"success",message:"Eliminando..."});
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
                            error: function ()
                            {
                                base.Error();
                            }
                        });
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
                    edicion();
                    edicionAction();
                    eliminar();
                }
            );
        }
    }
}();
