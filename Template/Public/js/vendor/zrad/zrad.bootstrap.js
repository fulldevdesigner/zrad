/**
 * Presenta la pantalla para agregar un nuevo registro
 *
 * @param {string} module
 * @param {string} params
 */
function newItem(module, params) {
    params = (typeof(params) != 'undefined') ? params : '';
    url = baseUrl + '/admin/' + module + '/new/';
    redirect(url);
}

/**
 * Eliminamos un registro de la lista
 *
 * @param {int} rowid
 */
function deleteItem(rowid) {
    $('#zrad-container').zradUIHelper({
        title : 'ELIMINAR REGISTRO',
        message : '¿Esta seguro de eliminar el registro?',
        baseUrl : baseUrl
    });
    if (rowid == null) {
        $('#zrad-container').zradUIHelper({
            message : 'Seleccione un registro de la lista'
        });
        $('#zrad-container').zradUIHelper('dialog');
    } else {
        option = 1;
        id = rowid;
        $('#zrad-container').zradUIHelper('dialogExecute');
    }
}

/**
 * Editamos un registro de la lista
 *
 * @param {int} rowid
 * @param {string} module
 * @param {string} params
 */
function editItem(rowid, module, params) {
    if (rowid == null){
        $('#zrad-container').zradUIHelper({
            title : 'EDITAR REGISTRO',
            message : 'Seleccione un registro de la lista',
            baseUrl : baseUrl
        });
        $('#zrad-container').zradUIHelper('dialog');
    } else {
        params = (typeof(params) != 'undefined') ? params : '';
        url = baseUrl + '/admin/' + module + '/edit/id/' + rowid + '/' + params;
        redirect(url);
    }
}

/**
 * @param {jqGrid} grid
 * @param {string} columnName
 */
function getColumnIndexByName(grid, columnName) {
    var cm = grid.jqGrid('getGridParam','colModel');
    for (var i=0,l=cm.length; i<l; i++) {
        if (cm[i].name === columnName) {
            return i; // return the index
        }
    }
    return -1;
}

$(function() {

    $.validator.setDefaults({
        highlight: function(input) {
            $(input).addClass("ui-state-highlight");
        },
        unhighlight: function(input) {
            $(input).removeClass("ui-state-highlight");
        }
    });

    $('input').focus(function () {
        $(this).addClass('ui-state-hover');
    });

    $('input').focusout(function () {
        $(this).removeClass('ui-state-hover');
    });

    $('#progressbar').progressbar({
        value: 100
    });

    $("#zrad-buttonset").buttonset();

    $('button#btnNew').button({icons: {primary: 'ui-icon-plusthick'}});
    $('button#btnEdit').button({icons: {primary: 'ui-icon-pencil'}});
    $('button#btnDelete').button({icons: {primary: 'ui-icon-trash'}});
    
    $('button.btnBack').button({icons: {primary: 'ui-icon-arrowreturn-1-w'}});
    $('button.btnSave').button({icons: {primary: 'ui-icon-disk'}});
    $('button#btnSearch').button({icons: {primary: 'ui-icon-search'}});

    $('button.btnCurrentImage').button({icons: {primary: 'ui-icon-image'}});   
    $('button.btnImage').button({icons: {primary: 'ui-icon-image'}});

    $('button.btnYoutube').button({icons: {primary: 'ui-icon-video'}});
    $('button.btnFile').button({icons: {primary: 'ui-icon-document'}});

    $('.btnExit').click(function(){
        $('#zrad-content').zradUIHelper({
            title : 'SALIR DEL ADMINISTRADOR',
            message : '¿Esta seguro de salir del administrador?',
            baseUrl : baseUrl
        });
        $('#zrad-content').zradUIHelper('dialogExit');
    });
    
});


