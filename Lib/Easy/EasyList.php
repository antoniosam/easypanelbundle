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
    private $buscar = false;
    private $busqueda ;
    private $paginar = false;
    private $paginacion = array();
    private $paginainfo = ' ';
    private $autopaginate = [];
    private $ordenar = false;
    private $orderby = [];
    private $firstColumnCount=false;
    private $firstColumnCountInit=0;

    private $rutas = array();

    private $columnas;
    private $consulta;

    private $paths = [];

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

    public function renderAsImage($columna, $path='')
    {
        if (isset($this->columnas[$columna])) {
            $this->columnas[$columna] = self::RENDER_IMAGE;
            $this->paths[$columna]=$path;
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

    public function enableOrder($route,$parametros,$col=1,$direccion='ASC'){
        $this->ordenar = true;
        $this->orderby = array('route'=>$route,'parameters'=>$parametros,'columna' => $col,'direccion'=>$direccion);
    }

    public function enableSearch($value,$textbutton = 'fa-search',$classbutton='btn',$classcontainer=''){
        $this->buscar = true;

        $hasicon = strpos($textbutton,'fa-');
        if($hasicon === 0){
            $textbutton = '<i class="fa '.$textbutton.'"></i><span class="sr-only">Buscar</span>';
        }
        $this->busqueda = array('value'=>$value,'text_button'=>$textbutton,'class_button'=>$classbutton,'class_container'=>$classcontainer);
    }

    public function setFirstColumnCount($initnumber=0){
        $this->firstColumnCount= true;
        $this->firstColumnCountInit = $initnumber;

    }
    /**
     * @param $totalresults
     * @param $pagina
     * @param $search
     * @param $route
     * @param $classitem
     * @param $classactive
     * @param string $first
     * @param string $last
     */
    public function createListPages(
        $totalpages,
        $currentpage,
        $search,
        $route,
        $classitem,
        $classactive,
        $first = "",
        $last = ""
    ) {
        $this->paginar = true;
        $this->autopaginate = array('total'=>$totalpages,'current'=>$currentpage,'search'=>$search,'route'=>$route,'classitem'=>$classitem,'classactive'=>$classactive,'first'=>$first,'last'=>$last);

    }

    /**
     * @param $page
     * @param $search
     * @return array
     */
    protected  function parameterPages($page, $search)
    {
        $back = [];
        if ($search != '') {
            $back['buscar'] = $search;
        }
        if($this->ordenar){
            $back['order']=$this->orderby['columna'];
            $back['ordertype']=$this->orderby['direccion'];
        }
        $back['p'] = $page;
        return $back;

    }
    public function addPages(array $array){
        foreach ($array as $item){
            if(count($item)==3){
                $this->addPage($item[0],$item[1],$item[2]);
            }elseif(count($item)==4) {
                $this->addPage($item[0],$item[1],$item[2],$item[3]);
            }
        }

    }

    public function setPageInfo($pageinfo){
        $this->paginainfo = $pageinfo;
    }

    public function addPage($route,$parameters,$pagina,$class=''){
        $this->paginar = true;
        $hasicon = strpos($pagina,'fa-');
        if($hasicon === 0){
            $pagina = '<i class="fa '.$pagina.'"></i>';
        }
        $this->paginacion[] = array('route'=>$route,'parameters'=>$parameters,'texto'=>$pagina,'class'=>$class);
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
        $path ='';
        reset($this->columnas);
        $firstcolum = key($this->columnas);
        //echo $firstcolum;
        foreach ($this->consulta as $objet) {
            $fila = array();
            foreach ($this->columnas as $columna => $tipo) {
                if(count($this->paths)>0){
                    $path = (isset($this->paths[$columna]))?$this->paths[$columna]:'';
                }
                if($this->firstColumnCount && $firstcolum == $columna){
                    $fila[] = $this->firstColumnCountInit;
                    $this->firstColumnCountInit++;
                }elseif(strpos($columna,'.')!==false){
                    list($subobjeto,$submetodo)= explode('.',$columna);
                    $temp = 'get' . $subobjeto;
                    $sub = $objet->$temp();
                    if($sub!=null){
                        $temp = 'get' . $submetodo;
                        $fila[] = $this->renderColumna($tipo, $sub->$temp(),$path);
                    }else{
                        $fila[] = '';
                    }
                }else{
                    $temp = 'get' . $columna;
                    $fila[] = $this->renderColumna($tipo, $objet->$temp(),$path);
                }
            }
            $filas[] = array('fila' => $fila, 'rutas' => $this->generateParameters($objet, $this->opciones));
        }
        $return["tabla"] = $filas;
        $return["has_new"] = $this->nueva;
        $return["new"] = $this->new;
        $return["has_search"] = $this->buscar;
        $return["search"] = $this->busqueda;
        if($this->paginar && count($this->autopaginate)>0){
            $this->generatePagination();
        }
        $return["has_paginate"] = $this->paginar;
        $return["pages"] = $this->paginacion;
        $return['page_info'] = $this->paginainfo;
        $return["has_order"] = $this->ordenar;
        if($this->ordenar){
            $param = $this->orderby['parameters'];
            $columna = $this->orderby['columna']==null ? 1 : $this->orderby['columna'];
            $direccion = $this->orderby['columna']==null ? 'ASC' : $this->orderby['direccion'];
            if($this->buscar){
                if(trim($this->busqueda['value'])!=''){
                    $param['buscar']= $this->busqueda['value'];
                }
            }
            $return["order"]=['route'=>$this->orderby['route'],'parameters'=>$param,'columna'=>$columna,'direccion'=>$direccion];
        }
        return $return;
    }
    private function generatePagination(){
        $totalpages = $this->autopaginate['total'];
        $currentpage = $this->autopaginate['current'];
        $search = $this->autopaginate['search'];
        $route = $this->autopaginate['route'];
        $classitem = $this->autopaginate['classitem'];
        $classactive = $this->autopaginate['classactive'];
        $first = $this->autopaginate['first'];
        $last = $this->autopaginate['last'];
        if($totalpages > 0) {
            if ($totalpages <= 7) {
                $inicio = 1;
                $fin = $totalpages;
            } else {
                if ($currentpage - 3 < 0) {
                    $inicio = 1;
                    $fin = $currentpage + 3 + (3 - $currentpage);//3 a la derecha mas los links que no se puedieron mostrar en la izquierda
                } elseif (($currentpage + 3) > $totalpages) {
                    $inicio = $currentpage - 3 - (($currentpage + 3) - $totalpages);//3 a la izquierda mas los que no se puedieron mostrar el lado derecho
                    $fin = $totalpages;
                } else {
                    $inicio = $currentpage - 3;
                    $fin = $currentpage + 3;
                }
            }

            $lista = [];
            if ($first != "") {
                $lista[] = array($route, $this->parameterPages(1, $search), $first, $classitem);
            }
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $currentpage) {
                    $lista[] = array($route, $this->parameterPages($i, $search), $i, $classitem . ' ' . $classactive);
                } else {
                    $lista[] = array($route, $this->parameterPages($i, $search), $i, $classitem);
                }

            }
            if ($last != "") {
                $lista[] = array($route, $this->parameterPages($totalpages, $search), $last, $classitem);
            }

            $this->addPages($lista);

            if($this->paginainfo==' ' || $this->paginainfo==''){
                $this->setPageInfo('Pagina ' . $currentpage . ' de ' . $totalpages);
            }

        }
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


