var norma = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#norma_table').DataTable({
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
        $('a#norma_tablerefrescar').click(function (evento) {
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
                    $('table#norma_table').html(data);
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
        $('table#norma_table').on('click', 'a.eliminar_norma', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: 'Eliminar norma',
                message: '¿Está seguro que desea eliminar esta norma?',
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

    var configurarFormulario = function () {
        $('select#norma_id_idautor').select2();
        $('select#norma_id_estado').select2();
        $('select#norma_tipoNorma').select2();
        $('input#norma_id_fechaCaptacion').datepicker();
        $("body form[name='norma']").validate({
            rules: {
                'norma[id][titulo]': {required: true},
                'norma[id][fechaCaptacion]': {required: true},
                'norma[id][keywords]': {required: true},
                'norma[id][file]': {required: true},
                'norma[id][resumen]': {required: true},
                'norma[id][estado]': {required: true},
                'norma[noRegistro]': {required: true},
                'norma[paginas]': {required: true, min: 1},
                'norma[tipoNorma]': {required: true},
            }
        });
    }

    var newAction = function () {
        $('body').on('submit', "form[name='norma']", function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData:false,
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Guardando..."});
                },
                complete: function () {
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    } else {
                        window.location.href=data['ruta']
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var personalizarUploadFile=function(){
        $('body').on('click','button#norma_file',function(){
            mApp.block("body",
                {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando archivos..."});
            $('input#norma_id_file').click();
            mApp.unblock("body");
        });

        $('body').on('change','input#norma_id_file',function(){
            var fileName = document.getElementById("norma_id_file").files[0].name;
            var fileSize = document.getElementById("norma_id_file").files[0].size;
            var maxSize=20971520;
            if(fileSize>maxSize){
                toastr.error('El archivo seleccionado excede el tamaño permitido (20MB)');
                $('input#norma_id_file').val('');
            }else
                $('span.custom-file-control').addClass("selected").html(fileName);
        })
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
                    personalizarUploadFile();
                }
            );
        },

    }
}();
