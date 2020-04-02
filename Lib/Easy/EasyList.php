<?php
/**
 * Created by antoniosam.
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyDoctrine\EasyData;
use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyList extends EasyView
{

    private $new = [];
    private $nueva = false;

    private $tableopciones = array();
    private $buscar = false;
    private $busqueda ;
    private $paginar = false;
    private $paginacion = array();
    private $paginainfo = ' ';
    private $autopaginate = [];
    private $ordenar = false;
    private $orderby = [];
    private $firstColumnCount = false;
    private $firstColumnCountInit = 0;

    private $consulta;
    private $infoParse;
    private $easydata;

    private $hasincludelayout = false;
    private $includelayout;

    function __construct($seccion, EasyData $easyData, $columnas)
    {
        $this->seccion = $seccion;
        $this->easydata = $easyData;
        $this->consulta = $this->easydata->data;
        $this->columnas = [];
        foreach ($columnas as $columna) {
            $this->columnas[$columna] = self::RENDER_TEXT;
        }
    }

    public function parseConsulta(){
        $this->infoParse = [];
        $paths = count($this->paths) > 0;
        foreach ($this->consulta as $objet) {
            $item = [];
            foreach ($this->columnas as $columna=>$type) {
                $path = ($paths && isset($this->paths[$columna]))?$this->paths[$columna]:'';
                $value = $this->getValueObject($objet,$columna);
                $item[$columna] = $this->formatValue($type, $value , $path);
            }
            $this->infoParse[] = $item;
        }
    }

    public function setLabelsTable(array $labels){
        $this->headers = $labels;
    }

    public function setNew($route, $parametros, $texto, $clase = 'btn-primary', $fa_icon = 'fa-plus')
    {
        $this->nueva = true;
        $this->new = $this->opcion($route, $parametros, $texto, $clase, $fa_icon);
    }

    public function clearNew(){
        $this->nueva = false;
        $this->new = null;
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
        $this->tableopciones[] = $this->opcion($route, $parametros, $texto, $clase, $fa_icon);
    }

    public function tableCleanLinks()
    {
        $this->tableopciones[] = [];
    }

    public function enableOrder($route,$parametros,$col=1,$direccion='ASC'){
        $this->ordenar = true;
        $this->orderby = array('route'=>$route,'parameters'=>$parametros,'columna' => $col,'orden'=>$direccion);
    }

    public function enableSearch($route_reset,$params_reset,$value, $textbutton = 'fa-search',$classbutton='btn',$classcontainer=''){
        $this->buscar = true;

        $hasicon = strpos($textbutton,'fa-');
        if($hasicon === 0){
            $textbutton = '<i class="fa '.$textbutton.'"></i><span class="sr-only">Buscar</span>';
        }
        $this->busqueda = array('route_reset'=>$route_reset,'params_reset'=>$params_reset,'value'=>$value,'text_button'=>$textbutton,'class_button'=>$classbutton,'class_container'=>$classcontainer);
    }

    public function setFirstColumnCount($initnumber){
        $this->firstColumnCount= true;
        $this->firstColumnCountInit = $initnumber;

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
     * @param $totalresults
     * @param $pagina
     * @param $search
     * @param $route
     * @param $classitem
     * @param $classactive
     * @param string $first
     * @param string $last
     */
    public function enablePagination(
        $search,
        $route,
        $params,
        $classitem = 'paginate_button',
        $classactive = 'active',
        $first = "fa-angle-double-left",
        $last = "fa-angle-double-right"
    ) {
        $this->paginar = true;
        $this->autopaginate = array('search'=>$search,'route'=>$route,'params'=>$params,'classitem'=>$classitem,'classactive'=>$classactive,'first'=>$first,'last'=>$last);

    }

    /**
     * @param $page
     * @param $search
     * @return array
     */
    protected  function parameterPages($page, $search,$params)
    {
        $back = [];
        if(count($params)>0){
            $back = array_merge($back,$params);
        }
        if ($search != '') {
            $back['buscar'] = $search;
        }
        if($this->ordenar && $this->orderby['columna'] > 0){
            $back['columna']=$this->orderby['columna'];
            $back['orden']=$this->orderby['orden'];
        }
        $back['pagina'] = $page;
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
    public function generatetoHtml()
    {
        $return = array();
        $return["seccion"] = $this->seccion;

        $return["headers"] = $this->defineHeaders($this->columnas, $this->headers,$this->firstColumnCount);
        $filas = [];
        $this->parseConsulta();
        foreach ($this->infoParse as $item) {
            $fila = array();
            if ($this->firstColumnCount) {
                $fila[] = $this->firstColumnCountInit;
                $fila[] = ['label' => 'count', 'valor' =>  $this->firstColumnCountInit];
                $this->firstColumnCountInit++;
            }
            foreach ($item as $columna=>$value) {
                $fila[] = $this->renderColumna( $this->columnas[$columna] , $value );
            }
            $filas[] = array('fila' => $fila, 'rutas' => $this->generateParameters($item, $this->tableopciones));
        }
        $return["tabla"] = $filas;
        $return["has_tabla_rutas"] = count($this->tableopciones);
        $return["has_new"] = $this->nueva;
        $return["new"] = $this->new;
        $return["has_search"] = $this->buscar;
        $return["search"] = $this->busqueda;
        if($this->paginar ){
            $this->generatePagination();
        }
        $return["has_paginate"] = $this->paginar;
        $return["pages"] = $this->paginacion;
        $return['page_info'] = $this->paginainfo;
        $return["has_order"]  = $this->ordenar;
        if($this->ordenar){
            $param = $this->orderby['parameters'];
            $columna = $this->orderby['columna'] == null ? 1 : $this->orderby['columna'];
            $direccion = $this->orderby['columna'] == null ? 'ASC' : $this->orderby['orden'];
            if($this->buscar){
                if(trim($this->busqueda['value'])!=''){
                    $param['buscar']= $this->busqueda['value'];
                }
            }
            $return["order"]=['route'=>$this->orderby['route'],'parameters'=>$param,'columna'=>$columna,'orden'=>$direccion];
        }
        $return["rutas"] = $this->opciones;
        $return["has_includelayout"] = $this->hasincludelayout;
        $return["includelayout"] = $this->includelayout;

        return $return;
    }

    private function generatePagination(){
        $search = $this->autopaginate['search'];
        $route = $this->autopaginate['route'];
        $params = $this->autopaginate['params'];
        $classitem = $this->autopaginate['classitem'];
        $classactive = $this->autopaginate['classactive'];
        $first = $this->autopaginate['first'];
        $last = $this->autopaginate['last'];

        $lista = [];
        if ($first != "") {
            $lista[] = array($route, $this->parameterPages(1, $search,$params), $first, $classitem);
        }
        foreach ($this->easydata->pages as $page) {
            if ($page == $this->easydata->page) {
                $lista[] = array($route, $this->parameterPages($page, $search,$params), $page, $classitem . ' ' . $classactive);
            } else {
                $lista[] = array($route, $this->parameterPages($page, $search,$params), $page, $classitem);
            }
        }
        if ($last != "") {
            $lista[] = array($route, $this->parameterPages($this->easydata->totalpages, $search,$params), $last, $classitem);
        }

        $this->addPages($lista);

        if($this->paginainfo==' ' || $this->paginainfo==''){
            $this->setPageInfo('Pagina ' . $this->easydata->page . ' de ' . $this->easydata->totalpages);
        }
    }

    /**
     * @return array
     */
    public function generatetoApi()
    {
        $items = [];
        $this->parseConsulta();
        if($this->includeGroup){
            foreach ($this->infoParse as $arrayObject) {
                $items[] = $this->groupFieldsApi($arrayObject);
            }
        }else{
            $items = $this->infoParse;
        }

        return ['items'=> $items,'total'=>$this->easydata->totalrecords,'results'=> count($items)];
    }

    public function defaultConfigList($prefix,$headers,$route,$buscar,$orderBy,$orderType){
        $this->tableLinkShow($prefix . '_show', array("id" => "id"), "Ver " . $this->getSeccion());
        $this->tableLinkEdit($prefix . '_edit', array("id" => "id"), "Editar " . $this->getSeccion());
        $this->setNew($prefix . '_new', [], "Nuevo " . $this->getSeccion());
        $this->setLabelsTable($headers);
        $this->enableOrder($route , [] , $orderBy , $orderType);
        $this->enableSearch($route , [] , $buscar);
        $this->enablePagination( $buscar , $route , []);
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
        $this->renderAsDate("creado");
    }

    /**
     * @param $seccion
     * @param $consulta
     * @param $columnas
     * @param $prefix
     * @return EasyList
     */
    public static function easy($seccion, EasyData $consulta, $columnas)
    {
        $list = new EasyList( $seccion, $consulta, $columnas);
        $list->fixRenders();
        return $list;
    }

}


