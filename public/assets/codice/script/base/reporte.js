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

var reporte = function () {
    var obj = null;
    var ultimoreporte=null;

    var resumenPeriodoLink = function () {
        $('body').on('click', 'a.report_link', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var form_id = 'resumenperiodo';
            createDateRangeForm('Seleccione el período', form_id, link);
        });
    }

    var resumenPeriodoAction = function () {
        $('body').on('submit', 'form#resumenperiodo', function (evento) {
            evento.preventDefault();
            $('div.bootbox').modal('hide');
            var action = $(this).attr("action");
            var data = $(this).serialize();

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
                        reporte.ultimoreporte=data.pdf;

                        $('div#basicmodal table').DataTable({
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
                    form: reporte.ultimoreporte
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


    return {
        init: function () {
            $().ready(function () {
                    resumenPeriodoLink();
                    resumenPeriodoAction();
                    exportarAction();
                }
            );
        },
    }
}();
