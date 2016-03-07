<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

/**
 * @see Zrad_Form_Biuld
 */
require_once 'zrad/Js/Element.php';

class Zrad_Js_Build extends Zrad_Abstract
{

    /**
     *
     */
    private $_script = '';

    /**
     * @var string
     */
    private $_scriptDate = '';

    /**
     * @var string
     */
    private $_scriptSelect = '';

    /**
     * @var int
     */

    const MAX_COLUMN = 7;

    /**
     * Init
     */
    protected function _init()
    {
        $this->_fileName = ucfirst($this->_util->format(strtolower($this->_tableName), 1));
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->_script;
    }

    /**
     * Create Form
     * @param string $action define la accion editar o crear
     * @return string
     */
    public function createForm($action = null)
    {
        // Link pagination
        $paginationAction = '/' . $this->_moduleName . '/' . $this->_util->format($this->_controllerName, 2);

        if (isset($this->_config['isFacebook'])) {
            // Removemos es_activo y paso de tabla participante
            unset($this->_mapper['fields']['es_activo']);
            unset($this->_mapper['fields']['paso']);
            unset($this->_mapper['fields']['fecha_nacimiento']);
            unset($this->_mapper['fields']['edad']);
        }

        $this->_script = ''
            . '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n\n";

        if ($this->_target != 'frontend') {
            $this->_script .= $this->setIndent(4)->getIndent() . 'id = <?php echo $this->id ?>;' . "\n\n";
        }

        $this->_script .= $this->setIndent(4)->getIndent() . '$(function() {' . "\n\n";

        if (isset($this->_config['isFacebook'])) {
            $this->_script .= $this->setIndent(8)->getIndent() . '$(\'#dlgRegistro\').dialog({' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'title: \'Registro\',' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'modal: true,' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'show: \'fade\',' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'hide: \'fade\',' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'autoOpen: false,' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'draggable: true,' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'resizable: false,' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'buttons: {' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '\'Aceptar\': function() {' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '$(this).dialog(\'close\');' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(8)->getIndent() . '});' . "\n" . "\n";
        }

        $this->_script .= $this->setIndent(8)->getIndent() . '$(\'#form' . $this->_util->format($this->_mapper['table'], 1) . '\').validate({' . "\n"
            . $this->setIndent(12)->getIndent() . 'rules: {' . "\n";

        $element = new Zrad_Js_Element();
        $element->setAction($action);
        //recorremos los campos
        $i = 1;
        foreach ($this->_mapper['fields'] as $field => $attributes) {
            // Obtenemos el elemento
            $coma = (count($this->_mapper['fields']) > $i) ? ',' : '';
            $this->_script .= $element->generate($field, $attributes, $this->_target) . $coma . "\n";

            // Fechas
            if (!isset($this->_config['isFacebook'])) {
                $this->_processDate($field, $attributes['validators']);
            }

            // Selectores
            if ($attributes['element'] == 'select') {
                $this->_processSelect($field);
            }
            //file
            if ($attributes['element'] == 'file' || $attributes['element'] == 'image') {
                //$this->_processFile($field);
            }
            $i++;
        }

        if (isset($this->_config['captcha']) && $this->_config['captcha']) {
            $this->_script .= ',' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . 'verificacion: {' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . 'required: true,' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . 'rangelength:[5,5]' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '}' . "\n";
        }

        $this->_script .= ''
            . $this->setIndent(12)->getIndent() . '}' . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n\n";
        if ($this->_target == 'frontend') {
            $this->_script .= $this->setIndent(8)->getIndent() . '$(\'form label.error\').attr(\'generated\',\'true\');' . "\n\n";
        }

        if (isset($this->_config['isFacebook'])) {
            $this->_script .= $this->setIndent(8)->getIndent() . '$(\'#btnRegistrar\').click(function() {' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . 'if ($(\'#form' . $this->_util->format($this->_mapper['table'], 1) . '\').valid()) {' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '$.zradUILoader({message: \'Registrando tu informacion...\', cWidth: 0, cHeight: 2});' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '$.post(\'concurso/registro/procesar\', $(\'#form' . $this->_util->format($this->_mapper['table'], 1) . '\').serialize(), function(s) { ' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '$(\'#dlgRegistro ul\').html(\'\');' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '$.zradUILoader(\'hide\');' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . 'if (s.state) { ' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . 'var options = {' . "\n";
            $this->_script .= $this->setIndent(28)->getIndent() . 'buttons: {' . "\n";
            $this->_script .= $this->setIndent(32)->getIndent() . '\'SIGUIENTE\': function () {' . "\n";
            $this->_script .= $this->setIndent(36)->getIndent() . 'redirect(baseUrl + \'/concurso\');' . "\n";
            $this->_script .= $this->setIndent(32)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(28)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . '};' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . '$(\'#dlgRegistro\').dialog(\'option\', options);' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . '$(\'#dlgRegistro\').html(\'Te has Registrado Exitosamente!\'); ' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '} else {' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . 'var errors = s.errors;' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . '$.each(errors, function(index, value) { ' . "\n";
            $this->_script .= $this->setIndent(28)->getIndent() . '$.each(value, function(indexIn, valueIn) {' . "\n";
            $this->_script .= $this->setIndent(32)->getIndent() . '$(\'#dlgRegistro ul\').append(\'<li>\' + index + \' \' + valueIn + \'<\/li>\');' . "\n";
            $this->_script .= $this->setIndent(28)->getIndent() . '});' . "\n";
            $this->_script .= $this->setIndent(24)->getIndent() . '});' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(20)->getIndent() . '$(\'#dlgRegistro\').dialog(\'open\');' . "\n";
            $this->_script .= $this->setIndent(16)->getIndent() . '},\'json\');' . "\n";
            $this->_script .= $this->setIndent(12)->getIndent() . '}' . "\n";
            $this->_script .= $this->setIndent(8)->getIndent() . '});' . "\n\n";
        }

        if (!empty($this->_scriptDate)) {
            $this->_script .= ''
                . $this->_scriptDate . "\n\n";
        }

        if (!empty($this->_scriptSelect)) {
            $this->_script .= ''
                . $this->_scriptSelect . "\n\n";
        }

        if ($this->_target == 'backend') {
            $this->_script .= ''
                . $this->setIndent(8)->getIndent() . '$(\'form\').bind(\'keypress\', function(e) {' . "\n"
                . $this->setIndent(12)->getIndent() . 'if (e.keyCode == 13) return false;' . "\n"
                . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
                . $this->setIndent(8)->getIndent() . '$(\'.btnBack\').click(function(){' . "\n"
                . $this->setIndent(12)->getIndent() . 'url = baseUrl + \'' . $paginationAction . '/list\';' . "\n"
                . $this->setIndent(12)->getIndent() . 'redirect(url);' . "\n"
                . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
                . $this->setIndent(8)->getIndent() . '$(\'.btnSave\').bind(\'click\', function() {' . "\n"
                . $this->setIndent(16)->getIndent() . 'if ($(\'#' . $formName . '\').valid()) {' . "\n"
                . $this->setIndent(16)->getIndent() . '$("#formProducto").submit();' . "\n"
                . $this->setIndent(8)->getIndent() . '}' . "\n"
                . $this->setIndent(8)->getIndent(). '});' . "\n" . "\n";
        }

        $this->_script .= ''
            . $this->setIndent(4)->getIndent() . '});' . "\n\n"
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>';

        return $this->_script;
    }

    /**
     * Create list backend
     * @return strng
     */
    public function createList()
    {
        // Link pagination
        $paginationAction = '/' . $this->_moduleName . '/' . $this->_util->format($this->_controllerName, 2);

        // colName
        $columns = $this->_mapper['grid']['colnames'];

        $colNames = $this->setIndent(12)->getIndent() . 'colNames:[\'Nº\',';
        $i = 1;
        foreach ($columns as $indice => $column) {
            $coma = (count($columns) > $i) ? ',' : '';
            $colNames .= '\'' . $column . '\'' . $coma;
            $i++;
        }
        $colNames .= ',\'\',\'\'';
        $colNames .= '],';

        $nRows = 20;
        $type = $this->_mapper['controller']['type'];
        if (array_key_exists('image', $type)) {
            $nRows = 5;
        }

        // colModel
        $i = 1;
        $columns = $this->_mapper['grid']['colmodels'];
        $colModel = $this->setIndent(12)->getIndent() . 'colModel:[{' . "\n";

        // Columna Numerada
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'index\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'index\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        foreach ($columns as $indice => $column) {
            $coma = (count($columns) > $i) ? '}, {' . "\n" : '';
            $colModel .= $this->setIndent(16)->getIndent() . 'name: \'' . $column['name'] . '\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'index: \'' . $column['name'] . '\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'sortable: true,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'width: ' . $column['width'] . '' . "\n";
            $colModel .= $this->setIndent(12)->getIndent() . $coma;
            $i++;
        }
        // Boton de edicion 
        $colModel .= $this->setIndent(0)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'edit\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'edit\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'formatter:function(){' . "\n";
        $colModel .= $this->setIndent(20)->getIndent() . 'return \'<span class="ui-icon ui-icon-pencil"><\/span>\'' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . '}' . "\n";
        // Boton de eliminacion
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'delete\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'delete\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'formatter:function(){' . "\n";
        $colModel .= $this->setIndent(20)->getIndent() . 'return \'<span class="ui-icon ui-icon-trash"><\/span>\'' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . '}' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '}],';

        // Others
        $others = $this->setIndent(12)->getIndent() . 'hidegrid: false,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'rowNum:' . $nRows . ',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'height:440,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'mtype: "POST",' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'autowidth: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'forceFit: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'rowList:[' . $nRows . ',' . ($nRows * 2) . ',' . ($nRows * 3) . '],' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'pager: \'#panPagination\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'sortname: \'id\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'viewrecords: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'sortorder: \'desc\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'postData:{},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'caption:\'\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'gridComplete: function(){' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'ondblClickRow: function(rowid) {' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'id = rowid;' . "\n";
        $others .= $this->setIndent(16)->getIndent() . '// Test' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'beforeSelectRow: function (rowid, e) {' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'var iCol = $.jgrid.getCellIndex(e.target);' . "\n" . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'var actionSelect = buttonNames[iCol];' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'if (iCol >= firstButtonColumnIndex) {' . "\n";
        $others .= $this->setIndent(20)->getIndent() . 'gridActionSelect(actionSelect,rowid);' . "\n";
        $others .= $this->setIndent(16)->getIndent() . '}' . "\n";
        $others .= $this->setIndent(16)->getIndent() . '// prevent row selection if one click on the button' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'return (iCol >= firstButtonColumnIndex)? false: true;' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'onCellSelect: function (rowid, iCol, cellcontent) {' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'if(cellcontent.length == 0){' . "\n";
        $others .= $this->setIndent(20)->getIndent() . 'showImage(rowid, iCol)' . "\n";
        $others .= $this->setIndent(16)->getIndent() . '}' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '}' . "\n";
        // Creamos las vistas        
        $body =
            '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n" . "\n"
            . $this->_generateCallBack() . "\n" . "\n"
            . $this->_generateShowImage() . "\n" . "\n"
            . $this->_generateGridActionSelect() . "\n" . "\n"
            . $this->setIndent(4)->getIndent() . '$(function() {' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . 'var firstButtonColumnIndex;' . "\n"
            . $this->setIndent(8)->getIndent() . 'var buttonNames = {};' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . 'var grid = $(\'#tblContent\').jqGrid({' . "\n"
            . $this->setIndent(12)->getIndent() . 'url: baseUrl + \'' . $paginationAction . '/pagination\',' . "\n"
            . $this->setIndent(12)->getIndent() . 'datatype: \'json\',' . "\n"
            . $colNames . "\n"
            . $colModel . "\n"
            . $others . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . '$("#tblContent").jqGrid(\'filterToolbar\');' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . 'firstButtonColumnIndex = getColumnIndexByName(grid,\'edit\');' . "\n"
            . $this->setIndent(8)->getIndent() . 'buttonNames[firstButtonColumnIndex] = \'Edit\';' . "\n"
            . $this->setIndent(8)->getIndent() . 'buttonNames[firstButtonColumnIndex+1] = \'Delete\';' . "\n\n"
            . $this->setIndent(8)->getIndent() . '$(\'#btnNew\').click(function(){' . "\n"
            . $this->setIndent(12)->getIndent() . 'newItem(\'' . $this->_util->format($this->_controllerName, 2) . '\');' . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . '$(\'#btnEdit\').click(function(){' . "\n"
            . $this->setIndent(12)->getIndent() . 'var id = $(\'#tblContent\').jqGrid(\'getGridParam\',\'selrow\');' . "\n"
            . $this->setIndent(12)->getIndent() . 'editItem(id, \'' . $this->_util->format($this->_controllerName, 2) . '\');' . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . '$(\'#btnDelete\').click(function(){' . "\n"
            . $this->setIndent(12)->getIndent() . 'id = $(\'#tblContent\').jqGrid(\'getGridParam\',\'selrow\');' . "\n"
            . $this->setIndent(12)->getIndent() . 'deleteItem(id);' . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
            . $this->setIndent(4)->getIndent() . '});' . "\n" . "\n"
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>';
        return $body;
    }

    /**
     *
     * 
     */
    private function _generateCallBack()
    {
        $paginationAction = '/' . $this->_moduleName . '/' . $this->_util->format($this->_controllerName, 2);
        $body = $this->setIndent(4)->getIndent() . '/**' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* Funcion de llamada luego de aceptar una accion' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '*/' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'function callback(){' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'switch(option){' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'case 1:' . "\n";
        $body .= $this->setIndent(16)->getIndent() . '$.post(baseUrl + \'' . $paginationAction . '/delete\', {id: id},' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'function(response){' . "\n";
        $body .= $this->setIndent(20)->getIndent() . 'response = eval(response);' . "\n";
        $body .= $this->setIndent(20)->getIndent() . 'var title = \'ELIMINAR REGISTRO\';' . "\n";
        $body .= $this->setIndent(20)->getIndent() . 'var message = response[\'response\'];' . "\n";
        $body .= $this->setIndent(20)->getIndent() . 'if(response[\'state\'] == \'ok\'){' . "\n";
        $body .= $this->setIndent(24)->getIndent() . '$(\'#tblContent\').trigger(\'reloadGrid\');' . "\n";
        $body .= $this->setIndent(20)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(20)->getIndent() . '$(\'#zrad-main\').zradUIHelper({' . "\n";
        $body .= $this->setIndent(24)->getIndent() . 'title : title,' . "\n";
        $body .= $this->setIndent(24)->getIndent() . 'message : message' . "\n";
        $body .= $this->setIndent(20)->getIndent() . '});' . "\n";
        $body .= $this->setIndent(20)->getIndent() . '$(\'#zrad-main\').zradUIHelper(\'dialog\');' . "\n";
        $body .= $this->setIndent(16)->getIndent() . '}, \'json\');' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}';
        return $body;
    }

    /**
     * 
     * @return string $body cuerpo de la funcion
     */
    private function _generateShowImage()
    {
        $body = $this->setIndent(4)->getIndent() . '/**' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* Muestra una imagen seleccionada de la lista de registros' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '*' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* @param {int} rowid' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* @param {int} iCol' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '*/' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'function showImage(rowid, iCol) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'switch (iCol) {' . "\n";
        foreach ($this->_mapper['grid']['colmodels'] as $field => $attributes) {
            if ($attributes['type'] == 'image') {
                $body .= $this->setIndent(12)->getIndent() . 'case ' . $attributes['index'] . ':' . "\n";
                $body .= $this->setIndent(16)->getIndent() . '$(\'#zrad-main\').zradUIHelper({' . "\n";
                $body .= $this->setIndent(20)->getIndent() . 'baseUrl : baseUrl,' . "\n";
                $body .= $this->setIndent(20)->getIndent() . 'entity : \'' . $this->_tableName . '\',' . "\n";
                $body .= $this->setIndent(20)->getIndent() . 'id : rowid,' . "\n";
                $body .= $this->setIndent(20)->getIndent() . 'column : \'' . $field . '\'' . "\n";
                $body .= $this->setIndent(16)->getIndent() . '});' . "\n";
                $body .= $this->setIndent(16)->getIndent() . '$(\'#zrad-main\').zradUIHelper(\'openImage\');' . "\n";
                $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
            }
        }
        $body .= $this->setIndent(12)->getIndent() . 'default:' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}';
        return $body;
    }

    /**
     * 
     * @return string $body cuerpo de la funcion
     */
    private function _generateGridActionSelect()
    {
        $body = $this->setIndent(4)->getIndent() . '/**' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* Eventos del grid' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '*' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* @param {string} actionSelect' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '* @param {int} rowid' . "\n";
        $body .= $this->setIndent(5)->getIndent() . '*/' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'function gridActionSelect(actionSelect, rowid) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'switch (actionSelect) {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'case \'Edit\':' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'editItem(rowid, \'' . $this->_tableName . '\');' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'case \'Delete\':' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'deleteItem(rowid);' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'default:' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}';
        return $body;
    }

    /**
     *
     */
    private function _processDate($field, $validators)
    {
        foreach ($validators as $validator => $value) {
            if ($validator == 'Date') {
                $this->_scriptSelect .= "\n";
                $this->_scriptSelect .= $this->setIndent(8)->getIndent() . '$(\'#' . $this->_util->format($field, 1) . '\').datepicker({' . "\n";
                $this->_scriptSelect .= $this->setIndent(12)->getIndent() . 'changeMonth: true,' . "\n";
                $this->_scriptSelect .= $this->setIndent(12)->getIndent() . 'changeYear: true' . "\n";
                $this->_scriptSelect .= $this->setIndent(8)->getIndent() . '});' . "\n";
            }
        }
    }

    /**
     * @param string $field
     */
    private function _processSelect($field)
    {
        $this->_scriptSelect .= "\n";
        $this->_scriptSelect .= $this->setIndent(8)->getIndent() . '$(\'select#' . $this->_util->format($field, 1) . '\').selectmenu({' . "\n";
        $this->_scriptSelect .= $this->setIndent(12)->getIndent() . 'style:\'dropdown\',' . "\n";
        $this->_scriptSelect .= $this->setIndent(12)->getIndent() . 'maxHeight: 200,' . "\n";
        $this->_scriptSelect .= $this->setIndent(12)->getIndent() . 'width: 255' . "\n";
        $this->_scriptSelect .= $this->setIndent(8)->getIndent() . '});' . "\n";
    }

    /**
     * Create list backend
     * @return strng
     */
    public function createListRaffle($workTable = null)
    {
        // Columnas Validas
        $columnas = array('id', 'detalle', 'imagen', 'votos', 'es_activo');

        // Link pagination
        $paginationAction = '/' . $this->_moduleName . '/' . $this->_util->format($this->_controllerName, 2);

        // Verificamos si es internacional
        $esIternacional = false;
        $columnasInternacional = array('tipo_documento', 'nro_documento');

        $model = $this->_model = new Zrad_Db_Model();
        $participanteCampos = $model->describeTable('participante');

        foreach ($participanteCampos as $indice => $column) {
            $columnT = strtolower($column['COLUMN_NAME']);
            if (in_array($columnT, $columnasInternacional)) {
                $esIternacional = true;
                break;
            }
        }


        $colNamesDni = ($esIternacional) ? '\'TIPO DOCUMENTO\',\'NRO DOCUMENTO\'' : '\'DNI\'';

        $colNames = $this->setIndent(12)->getIndent() . 'colNames:[\'N°\',\'PARTID\',\'PARTICIPANTE\',\'TIPO\',' . $colNamesDni . ',\'FBUID\',\'NACIMIENTO\',\'EMAIL\',\'CIUDAD\',\'PASO\',';

        // Verificamos la tabla relacionada
        if (null !== $workTable) {
            $colNameP = strtoupper(substr($workTable, 0, 3));
            $colNames .= '';
            // Obtenemos todas las columnas de la tabla work
            $i = 1;
            foreach ($this->_mapper['grid']['colmodels'] as $indice => $column) {
                $column['name'] = strtolower($column['name']);
                //$coma = (count($this->_mapper['grid']['colmodels']) > $i) ? ',' : '';
                if (in_array($column['name'], $columnas)) {
                    $colNames .= '\'' . $colNameP . ' ' . strtoupper($column['name']) . '\',';
                }
                $i++;
            }
        }

        if (',' == substr($colNames, -1)) {
            $colNames = substr($colNames, 0, -1) . ']';
        }
        $colNames .= ',';

        // colModel                
        $colModel = $this->setIndent(12)->getIndent() . 'colModel:[{' . "\n";
        // Columna Numerada
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'index\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'index\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'participante_id\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'participante_id\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'participante_nombres\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'participante_nombres\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : true,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 100' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'tipo\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'tipo\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : true,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";

        if ($esIternacional) {
            $colModel .= $this->setIndent(16)->getIndent() . 'name: \'tipo_documento\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'index: \'tipo_documento\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
            $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";

            $colModel .= $this->setIndent(16)->getIndent() . 'name: \'nro_documento\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'index: \'nro_documento\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
            $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        } else {
            $colModel .= $this->setIndent(16)->getIndent() . 'name: \'dni\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'index: \'dni\',' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
            $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
            $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        }



        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'fb_uid\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'fb_uid\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 60' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'fecha_nacimiento\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'fecha_nacimiento\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'email\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'email\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 80' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'ciudad\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'ciudad\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 40' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},{ ' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'name: \'paso\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'index: \'paso\',' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'search : false,' . "\n";
        $colModel .= $this->setIndent(16)->getIndent() . 'width: 20' . "\n";
        $colModel .= $this->setIndent(12)->getIndent() . '},';

        // Verificamos la tabla relacionada
        if (null !== $workTable) {
            $i = 1;
            // Obtenemos todas las columnas de la tabla work
            foreach ($this->_mapper['grid']['colmodels'] as $indice => $column) {
                $column['name'] = strtolower($column['name']);
                $coma = (count($this->_mapper['grid']['colmodels']) > $i) ? ',' : ']';
                if (in_array($column['name'], $columnas)) {
                    $colModel .= '{' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'name: \'' . strtolower($workTable) . '_' . $column['name'] . '\',' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'index: \'' . strtolower($workTable) . '_' . $column['name'] . '\',' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'sortable: false,' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'search: false,' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'resizable: false,' . "\n";
                    $colModel .= $this->setIndent(16)->getIndent() . 'width: ' . $column['width'] . '' . "\n";
                    $colModel .= $this->setIndent(12)->getIndent() . '}' . $coma;
                }
            }
        }

        // trim elimina \n
        if (',' == substr(trim($colModel), -1)) {
            $colModel = substr(rtrim($colModel), 0, -1) . ']';
        }
        $colModel .= ',';

        // Others
        $others = $this->setIndent(12)->getIndent() . 'hidegrid: false,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'rowNum: total,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'height:440,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'mtype: "POST",' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'autowidth: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'forceFit: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'rowList:[],' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'pager: \'#panPagination\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'sortname: \'participante_nombres\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'viewrecords: true,' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'sortorder: \'ASC\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'postData:{},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'caption:\'\',' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'gridComplete: function(){' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'ondblClickRow: function(rowid) {' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'id = rowid;' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '},' . "\n";
        $others .= $this->setIndent(12)->getIndent() . 'onCellSelect: function (rowid, iCol, cellcontent) {' . "\n";
        $others .= $this->setIndent(16)->getIndent() . 'if(cellcontent.length == 0){' . "\n";
        $others .= $this->setIndent(20)->getIndent() . 'showImage(rowid, iCol)' . "\n";
        $others .= $this->setIndent(16)->getIndent() . '}' . "\n";
        $others .= $this->setIndent(12)->getIndent() . '}' . "\n";
        // Creamos las vistas        
        $body = '$(\'#tblContent\').jqGrid({' . "\n"
            . $this->setIndent(12)->getIndent() . 'url: baseUrl + \'' . $paginationAction . '/pagination\',' . "\n"
            . $this->setIndent(12)->getIndent() . 'datatype: \'json\',' . "\n"
            . $colNames . "\n"
            . $colModel . "\n"
            . $others . "\n"
            . $this->setIndent(8)->getIndent() . '});' . "\n" . "\n"
            . $this->setIndent(8)->getIndent() . '$("#tblContent").jqGrid(\'filterToolbar\');' . "\n";
        return $body;
    }

}

