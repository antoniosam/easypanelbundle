<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyList extends EasyView
{

    private $new = [];
    private $nueva = false;
    private $seccion = "";
    private $cabeceras = [];
    private $opciones = array();

    private $rutas = array();

    private $columnas;
    private $consulta;

    function __construct($seccion, $consulta, $columnas)
    {
        $this->seccion = $seccion;
        $this->consulta = $consulta;
        $this->columnas = [];
        foreach ($columnas as $columna) {
            $this->columnas[$columna] = self::RENDER_TEXTO;
        }
    }

    /**
     * @return string
     */
    public function getSeccion()
    {
        return $this->seccion;
    }



    public function setCabeceras(array $cabceras)
    {
        $this->cabeceras = $cabceras;
    }

    public function setNew($route, $parametros, $texto, $clase = 'btn-primary', $fa_icon = 'fa-plus')
    {
        $this->nueva = true;
        $this->new = $this->opcion($route, $parametros, $texto, $clase, $fa_icon);
    }

    public function tableLinkEdit($route, $parametros, $nombre)
    {
        $this->tableLink($route, $parametros, $nombre, 'btn-info', 'fa-edit');
    }

    public function tableLinkShow($route, $parametros, $nombre)
    {
        $this->tableLink($route, $parametros, $nombre, 'btn-success', 'fa-list-ul');
    }

    public function tableLink($route, $parametros, $texto, $clase = 'btn-secondary', $fa_icon = null)
    {
        $this->opciones[] = $this->opcion($route, $parametros, $texto, $clase, $fa_icon);
    }

    public function tableCleanLinks()
    {
        $this->opciones[] = [];
    }

    public function renderAsImage($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_IMAGE;
        }
    }

    public function renderAsBoolean($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_BOOLEAN;
        }
    }

    public function renderAsDate($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_FECHA;
        }
    }

    public function renderAsTime($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_TIME;
        }
    }

    public function renderAsDateTime($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_FECHATIME;
        }
    }

    public function renderAsRaw($columna)
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_RAW;
        }
    }

    /**
     * @return array
     */
    public function generar()
    {
        $return = array();
        $return["seccion"] = $this->seccion;

        $return["headers"] = $this->defineHeaders($this->columnas, $this->cabeceras, $this->opciones);
        $filas = [];
        foreach ($this->consulta as $objet) {
            $fila = array();
            foreach ($this->columnas as $columna => $tipo) {
                $temp = 'get' . $columna;
                $fila[] = $this->renderColumna($tipo, $objet->$temp());
            }
            $filas[] = array('fila' => $fila, 'rutas' => $this->generateParameters($objet, $this->opciones));
        }
        $return["tabla"] = $filas;
        $return["has_new"] = $this->nueva;
        $return["new"] = $this->new;
        return $return;
    }

    public function fixRenders()
    {
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
     * @return EasyList
     */
    public static function easy($seccion, $consulta, $columnas, $prefix = null)
    {
        $list = new EasyList("Lista de " . $seccion, $consulta, $columnas);
        $list->fixRenders();
        if(!empty($prefix)){
            $list->tableLinkShow($prefix . '_show', array("id" => "id"), "Ver " . $seccion);
            $list->tableLinkEdit($prefix . '_edit', array("id" => "id"), "Editar " . $seccion);
            $list->setNew($prefix . '_new', [], "Nuevo " . $seccion);
        }
        return $list;
    }


}