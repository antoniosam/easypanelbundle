<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyShow extends EasyView
{

    private $opciones = array();
    private $seccion = "";
    private $cabeceras = [];
    private $columnas;
    private $consulta;
    private $has_delete = false;
    private $deleteform;
    private $paths = [];

    private $hasincludelayout = false;
    private $includelayout;

    function __construct($seccion,$objeto,$columnas)
    {
        $this->seccion = $seccion;
        $this->consulta = $objeto;
        $this->columnas = [];
        foreach ($columnas as $columna){
            $this->columnas[$columna] = self::RENDER_TEXTO;
        }
    }

    /**
     * @param $seccion
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;
    }

    /**
     * @return string
     */
    public function getSeccion()
    {
        return $this->seccion;
    }


    public function renderAsImage ($columna, $path=''){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_IMAGE; $this->paths[$columna]=$path;} }
    public function renderAsBoolean($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_BOOLEAN;}}
    public function renderAsDate ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_FECHA;} }
    public function renderAsTime ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_TIME;} }
    public function renderAsDateTime ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_FECHATIME;} }
    public function renderAsRaw ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_RAW;} }
    public function renderAsLink ($columna, $path=''){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_LINK; $this->paths[$columna]=$path;} }
    public function renderAsJson ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_JSON;} }
    public function renderAsArray ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_ARRAY;} }
    public function renderAsTranslate ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_TRANSLATE;} }

    public function addLinkEdit( $route, $parametros, $nombre)
    {
        $this->opciones[]= $this->opcion( $route,$parametros,$nombre, 'btn-info', 'fa-edit');
    }

    public function addLinkBack( $route, $parametros,$nombre )
    {
        $this->opciones[] = $this->opcion( $route,$parametros,$nombre, 'btn-secondary', 'fa-arrow-left');
    }

    public function addLink($route, $parametros, $texto,$clase = 'btn-secondary',$fa_icon = null)
    {
        $this->opciones[] = $this->opcion( $route,$parametros,$texto, $clase, $fa_icon);
    }

    public function cleanLinks()
    {
        $this->opciones[] = [];
    }

    public function setDeleteForm( $form)
    {
        $this->has_delete = true;
        $this->deleteform = $form;
    }

    /**
     * @Deprecated
     *
     * @param $nombre
     * @param $route
     * @param $parametros
     */
    public function setRemoveLink($nombre, $route, $parametros )
    {
        $this->opciones[] = $this->opcion( $route,$parametros,$nombre, 'btn-danger', 'fa-trash');
    }


    public function setCabeceras(array $cabceras){
        $this->cabeceras = $cabceras;
    }

    /**
     * @param mixed $includelayout
     */
    public function setIncludeLayout($includelayout)
    {
        $this->hasincludelayout = true;
        $this->includelayout = $includelayout;
    }

    /**
     * @return array
     */
    public function generar()
    {
        $return = array();
        $return["seccion"]= $this->seccion;
        $this->cabeceras = $this->defineHeaders($this->columnas,$this->cabeceras);
        $fila = [];
        $path ='';
        $key = 0;
        foreach ($this->columnas as $columna=>$tipo) {
            if(count($this->paths)>0){
                $path = (isset($this->paths[$columna]))?$this->paths[$columna]:'';
            }
            $value = $this->getValueObject($this->consulta,$columna);
            $html = $this->renderColumna($tipo, $value , $path);
            $fila[] = array('label' => $this->cabeceras[$key], 'valor' => $html);
            $key++;
        }
        $return["filas"] = $fila;
        $return["rutas"] = $this->generateParameters($this->consulta,$this->opciones);
        $return["has_delete"] = $this->has_delete;
        $return["delete"] = $this->deleteform;
        $return["has_includelayout"] = $this->hasincludelayout;
        $return["includelayout"] = $this->includelayout;

        return $return;
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
    /**
     * @param $seccion
     * @param $consulta
     * @param $columnas
     * @param $prefix
     * @return EasyShow
     */
    public static function easy($seccion , $consulta,$columnas,$prefix = null){
        $show = new EasyShow("Ver ".$seccion,$consulta,$columnas);
        $show->fixRenders();
        if(!empty($prefix)){
            $show->addLinkBack($prefix.'_index',[],"Regresar ");
            $show->addLinkEdit($prefix.'_edit',array("id"=>"id"),"Editar ".$seccion);
        }

        return $show;
    }


}