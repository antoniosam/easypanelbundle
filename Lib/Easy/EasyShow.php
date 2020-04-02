<?php
/**
 * Created by antonisam.
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyShow extends EasyView
{

    private $objeto;
    private $arrayObject;
    private $has_delete = false;
    private $deleteform;

    private $hasincludelayout = false;
    private $includelayout;


    function __construct($seccion,$objeto,$columnas)
    {
        $this->seccion = $seccion;
        $this->objeto = $objeto;
        $this->columnas = [];
        foreach ($columnas as $columna){
            $this->columnas[$columna] = self::RENDER_TEXT;
        }
    }

    public function setDeleteForm( $form)
    {
        $this->has_delete = true;
        $this->deleteform = $form;
    }

    public function setLabelsFields(array $labels){
        $this->headers = $labels;
    }

    /**
     * @param mixed $includelayout
     */
    public function setIncludeLayout($includelayout)
    {
        $this->hasincludelayout = true;
        $this->includelayout = $includelayout;
    }

    public function parseObject(){
        $this->arrayObject = [];
        $paths = count($this->paths) > 0;
        $item = [];
        foreach ($this->columnas as $columna=>$type) {
            $path = ($paths && isset($this->paths[$columna]))?$this->paths[$columna]:'';
            $value = $this->getValueObject($this->objeto,$columna);
            $item[$columna] = $this->formatValue($type, $value , $path);
        }
        $this->arrayObject = $item;

    }
    /**
     * @return array
     */
    public function generatetoHtml(){

        $return = [];
        $return["seccion"]= $this->seccion;
        $headers = $this->defineHeaders($this->columnas,$this->headers);
        $fila = [];
        $this->parseObject();
        $i=0;
        foreach ($this->arrayObject as $columna=>$value) {
            $fila[] = ['label' => $headers[$i], 'valor' => $this->renderColumna( $this->columnas[$columna] , $value )];
            $i++;
        }
        $return["filas"] = $fila;
        $return["rutas"] = $this->generateParameters($this->arrayObject,$this->opciones);
        $return["has_delete"] = $this->has_delete;
        $return["delete"] = $this->deleteform;
        $return["has_includelayout"] = $this->hasincludelayout;
        $return["includelayout"] = $this->includelayout;

        return $return;
    }

    /**
     * @return array
     */
    public function generatetoApi(){
        $this->parseObject();
        if($this->includeGroup){
            $data = $this->groupFieldsApi($this->arrayObject);
        }else{
            $data = $this->arrayObject;
        }
        return ['code'=> true ,'message'=>'','data'=> $data];
    }


    public function fixRenders(){
        $this->renderAsBoolean("activo");
        $this->renderAsBoolean("completado");
        $this->renderAsBoolean("activa");
        $this->renderAsImage("imagen");
        $this->renderAsImage("foto");
        $this->renderAsDate("fecha");
        $this->renderAsDate("dia");
    }

    public function defaultConfigShow($prefix,$headers,$formDelete){
        $this->addLinkBack($prefix.'_index',[],"Regresar ");
        $this->addLinkEdit($prefix.'_edit',array("id"=>"id"),"Editar ".$this->getSeccion());
        $this->setLabelsFields($headers);
        $this->setDeleteForm($formDelete);
    }


    /**
     * @param $seccion
     * @param $consulta
     * @param $columnas
     * @param $prefix
     * @return EasyShow
     */
    public static function easy($seccion , $consulta, $columnas){
        $show = new EasyShow($seccion, $consulta, $columnas);
        $show->fixRenders();
        return $show;
    }


}