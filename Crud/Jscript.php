<?php

/**
 * @see Zrad_Abstract
 */
require_once 'Zrad/Abstract.php';

class Zrad_Form_Jscript extends Zrad_Abstract
{
    /**
     * @var int
     */

    const MAX_COLUMN = 7;

    /**
     * @var array
     */
    private $_noValidColumns = array(
        'id',
        'created',
        'modified',
        'ip'
    );

    public static function createResource($actionName, $controllerName, $moduleName = null)
    {
        $dir = 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'scripts';
        if (!is_dir($dir))
            mkdir($dir, 0777);
        if ($moduleName !== null) {
            $dir .= DIRECTORY_SEPARATOR . $moduleName;
            if (!is_dir($dir))
                mkdir($dir, 0777);
        }
        $controllerName = strtolower($controllerName);
        $dir .= DIRECTORY_SEPARATOR . $controllerName;
        if (!is_dir($dir))
            mkdir($dir, 0777);
        $actionName = strtolower($actionName);
        $dir .= DIRECTORY_SEPARATOR . $actionName . '.js';
        fopen($dir, 'x');
    }

    private function _getAfterCall()
    {
        $dirModule = '';
        if ($this->_moduleName !== null) {
            $dirModule = '/admin';
        }
        $paginationAction = $dirModule . '/' . $this->_util->format($this->_controllerName, 2);

        //$afterCall = 'var id = null;' . "\n";
        //$afterCall .= 'var option = null;' . "\n" . "\n";
        $afterCall = 'function afterCall(){' . "\n";
        //$afterCall .= $this->_indentation . '$(this).dialog(\'close\');' . "\n";
        $afterCall .= $this->setIndent(4)->getIndent() . 'switch(option){' . "\n";
        $afterCall .= $this->setIndent(8)->getIndent() . 'case 1:' . "\n";
        $afterCall .= $this->setIndent(12)->getIndent() . '$.post(\'' . $paginationAction . '/delete\', {\'id\': id}, function(r) {' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . 'var titulo = \'ELIMINAR REGISTRO\';' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . 'var mensaje = \'Se ha eliminado el registro exitosamente\';' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . 'if(r.state){' . "\n";
        $afterCall .= $this->setIndent(20)->getIndent() . '$(\'#list\').trigger(\'reloadGrid\');' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . '}else{' . "\n";
        $afterCall .= $this->setIndent(20)->getIndent() . 'mensaje = r.info;' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . '}' . "\n";
        $afterCall .= $this->setIndent(16)->getIndent() . 'dialog(titulo,mensaje);' . "\n";
        $afterCall .= $this->setIndent(12)->getIndent() . '});' . "\n";
        $afterCall .= $this->setIndent(8)->getIndent() . 'break;' . "\n";
        $afterCall .= $this->setIndent(4)->getIndent() . '}' . "\n";
        $afterCall .= '}';
        return $afterCall;
    }

    public function createList()
    {

        $dirModule = '';
        if ($this->_moduleName !== null) {
            $dirModule = '/admin';
        }
        $paginationAction = $dirModule . '/' . $this->_util->format($this->_controllerName, 2);

        $nFields = count($this->_fields);
        $i = 1;

        //colnames
        $nValid = 1;
        $colNames = $this->_indentation . $this->_indentation . 'colNames:[';
        foreach ($this->_fields as $field) {
            if ($nValid > self::MAX_COLUMN) {
                break;
            }
            $coma = ($i > $nFields) ? '' : ',';
            if ($this->_repository->isColumnAllowed($field['COLUMN_NAME'])) {
                if ($nValid == 1) {
                    $colNames .= '\'' . $this->_util->format(strtoupper($field['COLUMN_NAME']), 4) . '\'';
                    /* if ($field['COLUMN_NAME'] == 'id') {
                      $colNames .= '\'Nº\'';
                      } else {
                      $colNames .= '\'' . $this->_util->format(strtoupper($field['COLUMN_NAME']), 4) . '\'';
                      } */
                } else {
                    $colNames .= $coma . '\'' . $this->_util->format(strtoupper($field['COLUMN_NAME']), 4) . '\'';
                    /* if ($field['COLUMN_NAME'] == 'id') {
                      $colNames .= $coma . '\'Nº\'';
                      } else {
                      $colNames .= $coma . '\'' . $this->_util->format(strtoupper($field['COLUMN_NAME']), 4) . '\'';
                      } */
                }
                $nValid++;
            }
            $i++;
        }
        $colNames .= '],';

        //colModel
        $colModel = $this->_indentation . $this->_indentation . 'colModel:[{';
        $i = 1;
        $nValid = 1;
        $rulesIndentation = $this->_indentation . $this->_indentation . $this->_indentation;
        foreach ($this->_fields as $field) {
            if ($nValid > self::MAX_COLUMN) {
                break;
            }
            $coma = ($i > $nFields) ? '' : '}, {';
            if ($this->_repository->isColumnAllowed($field['COLUMN_NAME'])) {
                if ($nValid == 1) {
                    $model = "\n";
                    $model .= $rulesIndentation . 'name:\'' . $field['COLUMN_NAME'] . '\',' . "\n";
                    $model .= $rulesIndentation . 'index:\'' . $field['COLUMN_NAME'] . '\',' . "\n";
                    $model .= $rulesIndentation . 'sortable:false,' . "\n";
                    $model .= $rulesIndentation . 'resizable:false,' . "\n";
                    $model .= $rulesIndentation . 'width:10' . "\n";
                } else {
                    $model = $this->_indentation . $this->_indentation . $coma . "\n";
                    $model .= $rulesIndentation . 'name:\'' . $field['COLUMN_NAME'] . '\',' . "\n";
                    $model .= $rulesIndentation . 'index:\'' . $field['COLUMN_NAME'] . '\',' . "\n";
                    $model .= $rulesIndentation . 'sortable:false,' . "\n";
                    $model .= $rulesIndentation . 'resizable:false,' . "\n";
                    $model .= $rulesIndentation . 'width:10' . "\n";
                }
                $nValid++;
                $colModel .= $model;
            }
            $i++;
        }
        $colModel .= $this->_indentation . $this->_indentation . '}],';

        //others
        $others = $this->_indentation . $this->_indentation . 'hidegrid: false,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'rowNum:20,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'height:440,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'mtype: \'POST\',' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'autowidth: true,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'forceFit : true,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'rowList:[20,40,60,80,100],' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'pager: \'#panPagination\',' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'sortname: \'id\',' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'viewrecords: true,' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'sortorder: \'desc\',' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'caption:\'\',' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'gridComplete: function(){' . "\n";
        $others .= $this->_indentation . $this->_indentation . '},' . "\n";
        $others .= $this->_indentation . $this->_indentation . 'ondblClickRow: function(rowid) {' . "\n";
        $others .= $rulesIndentation . 'id = rowid;' . "\n";
        $others .= $rulesIndentation . 'url = baseUrl + \'' . $paginationAction . '/edit/id/\'+id;' . "\n";
        $others .= $rulesIndentation . 'redirect(url);' . "\n";
        $others .= $this->_indentation . $this->_indentation . '}';

        $afterCall = $this->_getAfterCall();

        $body =
            '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n"
            . $afterCall . "\n" . "\n"
            . '$(function() {' . "\n"
            . $this->_indentation . '$(\'#list\').jqGrid({' . "\n"
            . $this->_indentation . $this->_indentation . 'url: baseUrl + \'' . $paginationAction . '/pagination\',' . "\n"
            . $this->_indentation . $this->_indentation . 'datatype: \'json\',' . "\n"
            . $colNames . "\n"
            . $colModel . "\n"
            . $others . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . $this->_indentation . '$(\'#new_btn\').click(function(){' . "\n"
            . $this->_indentation . $this->_indentation . 'url = baseUrl + \'' . $paginationAction . '/new/\';' . "\n"
            . $this->_indentation . $this->_indentation . 'redirect(url);' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . $this->_indentation . '$(\'#edit_btn\').click(function(){' . "\n"
            . $this->_indentation . $this->_indentation . 'var id = $(\'#list\').jqGrid(\'getGridParam\',\'selrow\');' . "\n"
            . $this->_indentation . $this->_indentation . 'if(id == null){' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'var title = \'EDITAR REGISTRO\';' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'var message = \'Seleccione un registro de la lista\';' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'dialog(title,message);' . "\n"
            . $this->_indentation . $this->_indentation . '}else{' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'url = baseUrl + \'' . $paginationAction . '/edit/id/\'+id;' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'redirect(url);' . "\n"
            . $this->_indentation . $this->_indentation . '}' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . $this->_indentation . '$(\'#delete_btn\').click(function(){' . "\n"
            . $this->_indentation . $this->_indentation . 'id = $(\'#list\').jqGrid(\'getGridParam\',\'selrow\');' . "\n"
            . $this->_indentation . $this->_indentation . 'var title = \'ELIMINAR REGISTRO\';' . "\n"
            . $this->_indentation . $this->_indentation . 'var message = \'Esta seguro de eliminar el registro?\';' . "\n"
            . $this->_indentation . $this->_indentation . 'option = 1;' . "\n"
            . $this->_indentation . $this->_indentation . 'if(id == null){' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'var message = \'Seleccione un registro de la lista\';' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . 'dialog(title,message);' . "\n"
            . $this->_indentation . $this->_indentation . '}else{' . "\n"
            . $this->_indentation . $this->_indentation . $this->_indentation . ' dialogExecute(title,message);' . "\n"
            . $this->_indentation . $this->_indentation . '}' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . '});' . "\n" . "\n"
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>';

        return $body;
    }

    public function createNew()
    {
        $dirModule = '';
        if ($this->_moduleName !== null) {
            $dirModule = '/admin';
        }

        $paginationAction = $dirModule . '/' . $this->_util->format($this->_controllerName, 2);

        $this->_controllerName = ucfirst($this->_controllerName);
        $controllerLink = $this->_util->format($this->_controllerName, 2);

        //create form
        $formName = 'form-' . $this->_tableName;

        $rules = '';
        $messages = '';
        $nFields = count($this->_fields);
        $i = 1;
        $rulesIndentation = $this->_indentation . $this->_indentation . $this->_indentation . $this->_indentation;

        //campos date
        $bodyDate = '';
        $bodySelect = '';

        foreach ($this->_fields as $field) {
            $columnName = strtolower($field['COLUMN_NAME']);
            $originalColumnName = $columnName;
            if (!in_array($columnName, $this->_noValidColumns)) {
                $columnName = $this->_util->format($columnName, 1);
                //type
                $type = 'text';
                $validators = '';
                $errors = '';
                $filters = '';

                switch ($field['DATA_TYPE']) {
                    case 'int':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'number:true';
                        break;
                    case 'tinyint':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'number:true';
                        break;
                    case 'date':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'date: true';
                        $bodyDate .= '$(\'#' . $columnName . '\').datepicker({' . "\n";
                        $bodyDate .= $this->_indentation . $this->_indentation . 'changeMonth: true,' . "\n";
                        $bodyDate .= $this->_indentation . $this->_indentation . 'changeYear: true' . "\n";
                        $bodyDate .= $this->_indentation . '});' . "\n" . "\n";
                        break;
                }

                switch ($field['COLUMN_NAME']) {
                    case 'email':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'email:true';
                        break;
                    case 'dni':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'rangelength:[8,8]';
                        break;
                    case 'ruc':
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'rangelength:[11,11]';
                        break;
                }



                //verificando si es imagen
                preg_match_all("/image/", $originalColumnName, $output);
                if (!empty($output[0])) {
                    $image = $output[0][0];
                    if ($image == 'image') {
                        $validators = ',' . "\n"
                            . $rulesIndentation . 'accept:true';
                    }
                }

                //verificamos si es select
                preg_match('/_id/i', $originalColumnName, $output);
                if (!empty($output)) {
                    /*$bodySelect .= '$(\'select#' . $columnName . '\').selectmenu({' . "\n";
                    $bodySelect .= $this->_indentation . $this->_indentation . 'style:\'dropdown\',' . "\n";
                    $bodySelect .= $this->_indentation . $this->_indentation . 'maxHeight: 120,' . "\n";
                    $bodySelect .= $this->_indentation . $this->_indentation . 'width: 250' . "\n";
                    $bodySelect .= $this->_indentation . '});' . "\n" . "\n";*/
                }

                $coma = ($i == $nFields) ? '' : ',';
                $rules .= $this->_indentation . $this->_indentation . $this->_indentation . $columnName . ': {' . "\n"
                    . $rulesIndentation . 'required: true' . $validators . "\n"
                    . $this->_indentation . $this->_indentation . $this->_indentation . '}' . $coma . "\n";
                $messages .= "\n" . $this->_indentation . $this->_indentation . $this->_indentation . $columnName . ': {' . "\n"
                    . $rulesIndentation . 'required: \'Este campo es requerido\'' . $errors . "\n"
                    . $this->_indentation . $this->_indentation . $this->_indentation . '}' . $coma . "\n";
            }
            $i++;
        }

        if (empty($bodyDate)) {
            $bodyDate = "\n";
        } else {
            $bodyDate = $this->_indentation . '//date fields' . "\n"
                . $this->_indentation . $bodyDate;
        }

        $afterCall = $this->_getAfterCall();
        $body = $afterCall . "\n" . "\n";
        $body =
            '<script type="text/javascript">' . "\n"
            . '<?php $this->headScript()->captureStart() ?>' . "\n"
            . '$(function() {' . "\n"
            . $this->_indentation . $bodyDate
            . $this->_indentation . $bodySelect
            . $this->_indentation . '$(\'#' . $formName . '\').validate({' . "\n"
            . $this->_indentation . $this->_indentation . 'rules: {' . "\n"
            . $rules
            . $this->_indentation . $this->_indentation . '}' . "\n"
//            . $this->_indentation . $this->_indentation . 'messages: {'
//            . $messages
//            . $this->_indentation . $this->_indentation . '}' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . $this->_indentation . '$(\'.back_btn\').click(function(){' . "\n"
            . $this->_indentation . $this->_indentation . 'url = baseUrl + \'' . $paginationAction . '/list\';' . "\n"
            . $this->_indentation . $this->_indentation . 'redirect(url);' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . $this->_indentation . '$(\'.btnSave\').bind(\'click\', function() {' . "\n"
            . $this->setIndent(8)->getIndent() .'if ($(\'#' . $formName . '\').valid()) {' . "\n"
            . $this->setIndent(16)->getIndent() .'$("#formProducto").submit();' . "\n"
            . $this->setIndent(8)->getIndent() .'}' . "\n"
            . $this->_indentation . '});' . "\n" . "\n"
            . '});' . "\n"
            . '<?php $this->headScript()->captureEnd() ?>' . "\n"
            . '</script>';

        return $body;
    }

}
