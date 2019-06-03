var libro = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#libro_table').DataTable({
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
        $('a#libro_tablerefrescar').click(function (evento) {
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
                    $('table#libro_table').html(data);
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
        $('table#libro_table').on('click', 'a.eliminar_libro', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: 'Eliminar libro',
                message: '¿Está seguro que desea eliminar este libro?',
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
        $('select#libro_editorial').select2();
        $('select#libro_id_idautor').select2();
        $('select#libro_id_estado').select2();
        $('input#libro_id_fechaCaptacion').datepicker();
        $("body form[name='libro']").validate({
            rules: {
                'libro[id][titulo]': {required: true},
                'libro[id][fechaCaptacion]': {required: true},
                'libro[id][keywords]': {required: true},
                'libro[id][file]': {required: true},
                'libro[id][resumen]': {required: true},
                'libro[id][estado]': {required: true},
                'libro[editorial]': {required: true},
                'libro[volumen]': {required: true},
                'libro[numero]': {required: true},
                'libro[serie]': {required: true},
                'libro[paginas]': {required: true, min: 1},
                'libro[isbn]': {required: true},
            }
        });
    }

    var newAction = function () {
        $('body').on('submit', "form[name='libro']", function (evento)
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
        $('body').on('click','button#libro_file',function(){
            $('input#libro_id_file').click();
        });

        $('body').on('change','input#libro_id_file',function(){
            var fileName = document.getElementById("libro_id_file").files[0].name;
            var fileSize = document.getElementById("libro_id_file").files[0].size;
            var maxSize=20971520;
            if(fileSize>maxSize){
                toastr.error('El archivo seleccionado excede el tamaño permitido (20MB)');
                $('input#libro_id_file').val('');
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
