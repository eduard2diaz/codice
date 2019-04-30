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
                    $('table#autor_table').html(data);
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
                    if (data['mensaje'])
                        toastr.success(data['mensaje']);
                    enlace.children('i').removeClass();
                    enlace.children('i').addClass('m-nav__link-icon ' + data['class']);
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
            var token = $(this).attr('data-csrf');
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
        $('select#autor_pais').select2();
        $('select#autor_ministerio').select2();
        $('select#autor_institucion').select2();
        $('select#autor_area').select2();
        $('select#autor_gradoCientifico').select2();
        $('select#autor_idrol').select2();
        $('select#autor_jefe').select2();
        $("div.tab-content form").validate({
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
                'autor[password][second]': {equalTo: "#autor_password_first"},
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
                        $('select#autor_institucion').change();
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

    var institucionListener = function () {
        $('body').on('change', 'select#autor_institucion', function (evento) {
            institucionId = $(this).val();
            if (institucionId > 0) {
                areaListener(institucionId);
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('autor_finddirectivosbyinstitucion', {'id': institucionId}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando directivos..."
                            });
                    },
                    success: function (data) {
                        var cadena = "<option></option>";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#autor_jefe').html(cadena);
                        $('select#autor_jefe').change();
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("body")
                    }
                });
            }
        });
    }

    function areaListener(institucionId) {
        $.ajax({
            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
            dataType: 'html',
            url: Routing.generate('area_findbyinstitucion', {'id': institucionId}),
            beforeSend: function (data) {
                mApp.block("body",
                    {
                        overlayColor: "#000000",
                        type: "loader",
                        state: "success",
                        message: "Cargando áreas..."
                    });
            },
            success: function (data) {
                var cadena = "";
                var array = JSON.parse(data);
                for (var i = 0; i < array.length; i++)
                    cadena += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                $('select#autor_area').html(cadena);
                $('select#autor_area').change();
            },
            error: function () {
                base.Error();
            },
            complete: function () {
                mApp.unblock("body")
            }
        });
    }

    var jefeListener = function () {
        $('body').on('change', 'select#autor_jefe', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('area_findbyautor', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando áreas..."});
                    },
                    success: function (data) {
                        $('select#autor_area').html(data);
                        //LANZANDO YO MIMSO EL EVENTO
                        //  $('select#autor_area').change();
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("body");
                    }
                });
            else {
                if ($('select#autor_institucion').length > 0)
                    var institucionId = $('select#autor_institucion').val();
                else
                    var institucionId = currentInstitucion;
                if (institucionId > 0)
                    areaListener(institucionId);
            }
        });
    }

    var reiniciarFoto = function () {
        $('body').on('click', 'a#reload_picture', function () {
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

    function createDateRangeForm(title, form_id, action) {
        var dialog = bootbox.dialog({
                title: title,
                message: '<form id="' + form_id + '" class="daterange" action="' + action + '">' +
                    '<div class="row">' +
                    '<div class="col-md-6"><label for="finicio">Fecha de inicio</label>' +
                    '<input type="text" class="form-control input-medium" id="finicio" name="finicio"/></div>' +
                    '<div class="col-md-6"><label for="ffin">Fecha de fin</label>' +
                    '<input type="text" class="form-control input-medium" id="ffin" name="ffin"/></div>' +
                    '</div>' +
                    '</form>',
                buttons: {
                    cancel: {
                        label: "Cancelar",
                        className: 'btn-metal btn-sm',
                    },
                    noclose: {
                        label: "Enviar",
                        className: 'btn btn-primary btn-sm',
                        callback: function () {
                            if ($('div.bootbox form.daterange').valid()) {
                                $('div.bootbox form.daterange').submit();
                            } else {
                                return false;
                            }
                        }
                    },
                }
            }
        );

        $('input#finicio').datepicker();
        $('input#ffin').datepicker();
        jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                return moment(value) > moment($(params).val());
            }, 'Tiene que ser superior a la fecha de inicio');


        $("div.bootbox form.daterange").validate({
            rules: {
                'finicio': {required: true},
                'ffin': {required: true, greaterThan: "#finicio"},
            }
        });
    }

    var resumenPeriodoLink = function () {
        $('body').on('click', 'a#resumenperiodo_link', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var form_id = 'resumenperiodo';
            var title = 'Resumen de publicaciones en el período';
            createDateRangeForm(title, form_id, link);
        });
    }

    var autorResumenPeriodoAction = function () {
        $('body').on('submit', 'form#resumenperiodo', function (evento) {
            evento.preventDefault();
            $('div.bootbox').modal('hide');
            var action = $(this).attr("action");
            var data = $(this).serialize();

            setTimeout(function () {
                $.ajax({
                    url: action,
                    type: "POST",
                    data: data, //para enviar el formulario hay que serializarlo
                    beforeSend: function () {
                        mApp.block("body",
                            {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                    },
                    complete: function () {
                        mApp.unblock("body");
                    },
                    success: function (data) {
                        {
                            $('div#basicmodal').html(data.html);
                            $('div#basicmodal').modal('show');
                            am4core.useTheme(am4themes_animated);
                            // Themes end
                            // Create chart instance
                            var chart = am4core.create("resumen_grafico", am4charts.PieChart);
                            // Add data
                            chart.data = JSON.parse(data.data);
                            // Add and configure Series
                            var pieSeries = chart.series.push(new am4charts.PieSeries());
                            pieSeries.dataFields.value = "total";
                            pieSeries.dataFields.category = "entidad";
                            pieSeries.slices.template.stroke = am4core.color("#fff");
                            pieSeries.slices.template.strokeWidth = 2;
                            pieSeries.slices.template.strokeOpacity = 1;
                            // This creates initial animation
                            pieSeries.hiddenState.properties.opacity = 1;
                            pieSeries.hiddenState.properties.endAngle = -90;
                            pieSeries.hiddenState.properties.startAngle = -90;
                            //Guardo el ultimo reporte realizado
                            ultimoreporte=data.pdf;

                            $('div#basicmodal table#resumen_por_subordinado').DataTable({
                                "pagingType": "simple_numbers",
                                "language": {
                                    url: datatable_url
                                },
                            });
                        }
                    },
                    error: function () {
                        base.Error();
                    }
                });
            }, 500)

        });
    }

    var newAction = function () {
        $('body').on('submit', "form[name='autor']", function (evento) {
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

    var exportarAction = function () {
        $('div#basicmodal').on('click', 'a.exportar_reporte', function (evento)
        {
            evento.preventDefault();
            var l = Ladda.create(document.querySelector('div#basicmodal a.ladda-button'));
            l.start();
            $.fileDownload(Routing.generate('reporte_exportar'), {
                data:{
                    form: ultimoreporte
                },
                successCallback: function (url) {
                    l.stop();
                },
                prepareCallback: function (url) {
                    l.stop();
                },
                failCallback: function (url) {
                    base.Error();
                },
            });
        });
    }

    var gestionarFoto = function () {
        $('body').on('click','#foto_perfil',function () {
            mApp.block("body",
                {
                    overlayColor: "#000000",
                    type: "loader",
                    state: "success",
                    message: "Explorando archivos ..."
                });
            $('#autor_file').click();
            mApp.unblock("body");
            document.getElementById('autor_file').addEventListener('change', previewfile, false);
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
        show: function () {
            $().ready(function () {
                    var ultimoreporte = null;
                    subscribir();
                    resumenPeriodoLink();
                    autorResumenPeriodoAction();
                    exportarAction();
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
                    institucionListener()
                    jefeListener();
                    newAction();
                    gestionarFoto();
                    reiniciarFoto();
                }
            );
        },
    }
}();
