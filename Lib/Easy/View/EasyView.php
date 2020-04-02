<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy\View;


use Symfony\Component\HttpFoundation\Request;

class EasyView
{
    const RENDER_TEXT = 'text';
    const RENDER_IMAGE = 'image';
    const RENDER_BOOLEAN = 'bool';
    const RENDER_DATE = 'date';
    const RENDER_TIME = 'time';
    const RENDER_DATETIME = 'datetime';
    const RENDER_TIMESTAMP = 'timestamp';
    const RENDER_RAW = 'raw';
    const RENDER_LINK = 'link';
    const RENDER_JSON = 'json';
    const RENDER_ARRAY = 'array';
    const RENDER_TRANSLATE = 'translate';

    protected $opciones = [];
    protected  $seccion = '';

    private $request;
    protected $columnas;
    protected $paths = [];
    protected $headers;

    protected $groupField = [];
    protected $includeGroup = false;

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

    /**
     * @return Request
     */
    private function getRequest(){
        if(is_null($this->request)){
            $this->request = Request::createFromGlobals();
        }
        return $this->request;
    }

    /**
     * @param object $object
     * @param $columna
     * @return string
     */
    protected function getValueObject($object,$columna){
        if(strpos($columna,'translate')!==false) {
            return $this->getOneValueObject($object, $columna) ;
        }else if(strpos($columna,'~')!==false) {
            $cadena = '';
            foreach (explode('~', $columna) as $item) {
                $cadena .= $this->getOneValueObject($object, $item) . ' ';
            }
            return $cadena;
        }else if(strpos($columna,'_')!==false){
            $cadena = '';
            foreach (explode('_',$columna) as $item){
                $cadena .= $this->getOneValueObject($object,$item);
            }
            return $cadena;
        }else{
            return $this->getOneValueObject($object,$columna);
        }
    }

    /**
     * @param object $object
     * @param $columna
     * @return array|string
     */
    protected function getOneValueObject($object,$columna){
        if(strpos($columna,'translate') !== false){
            $campo = substr($columna,10);
            if(strpos($campo,'.')!==false) {
                list($subobject,$cmp) = explode('.', $campo);
                $getter = 'get'.$subobject;
                if($object->$getter() != null){
                    $object = $object->$getter();
                    $campo = $cmp;
                }else{
                    return '';
                }
            }
            if(strpos($campo,'~')===false){
                $getter = 'get' . $campo;
                return $object->translate($this->getRequest()->getLocale())->$getter();
            }else{
                list($camp,$idiomas) = explode('~',$campo);
                $getter = 'get' . $camp;
                $lista = [];
                foreach (explode('|',$idiomas) as $idioma){
                    $lista[$idioma] = $object->translate($idioma)->$getter();
                }
                return $lista;
            }
        }else if(strpos($columna,'.')!==false){
            $lista = explode('.',$columna);
            if(count($lista) == 2){
                $tmp = 'get' . $lista[0];
                $tmp2 = 'get' . $lista[1];
                return ( !is_null($object->$tmp()) ) ? $object->$tmp()->$tmp2():'';
            }else if(count($lista) == 3){
                $tmp = 'get' . $lista[0];
                $tmp2 = 'get' . $lista[1];
                $tmp3 = 'get' . $lista[2];
                return ( !is_null($object->$tmp()) ) ?  ( ( !is_null($object->$tmp()->$tmp2()) )? $object->$tmp()->$tmp2()->$tmp3() :'' ) : '';
            }
        }else{
            $getter = 'get' . $columna;
            return $object->$getter();
        }
    }


    /**
     * @param $tipo
     * @param $valor
     * @return string
     */
    protected function formatValue($tipo, $valor,$path='')
    {
        if($tipo == self::RENDER_TEXT && is_array($valor)){
            $tipo = self::RENDER_ARRAY;
        }elseif (($tipo == self::RENDER_TEXT ) && $valor instanceof \DateTime){
            $tipo = self::RENDER_DATETIME;
        }

        if ($tipo == self::RENDER_TEXT) {
            return $valor;
        } elseif ($tipo == self::RENDER_BOOLEAN) {
            return is_null($valor) ? null : ( $valor == true );
        }elseif ($tipo == self::RENDER_IMAGE) {
            return (strpos($path,$valor)!==false)? $path :str_replace('//','/',$path.'/'.$valor);
        } elseif ($tipo == self::RENDER_LINK) {
            return (strpos($path,$valor)!==false)? $path :str_replace('//','/',$path.'/'.$valor);
        } elseif ($tipo == self::RENDER_JSON) {
            return json_decode($valor,true);
        } elseif ($tipo == self::RENDER_ARRAY) {
            return $valor;
        } elseif ($tipo == self::RENDER_DATE) {
            return is_null($valor)?'---':$valor->format("Y-m-d");
        } elseif ($tipo == self::RENDER_TIME) {
            return is_null($valor)?'---':$valor->format("H:i:s");
        } elseif ($tipo == self::RENDER_DATETIME) {
            return is_null($valor)?'---':$valor->format("Y-m-d H:i:s");
        } elseif ($tipo == self::RENDER_TIMESTAMP) {
            return is_null($valor)?0:$valor->getTimestamp()*1000;
        } elseif ($tipo == self::RENDER_TRANSLATE) {
            if(!is_null($valor)){
                if(!is_array($valor)){
                    $tmp = $valor;
                    $valor = [$tmp];
                }
                return $valor;
            }else{
                return null;
            }
        } elseif ($tipo == self::RENDER_RAW) {
            return $valor;
        } else {
            return $valor;
        }
    }

    /**
     * @param $tipo
     * @param $valor
     * @return string
     */
    protected function  renderColumna($tipo, $valor)
    {
        if ($tipo == self::RENDER_TEXT) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_BOOLEAN) {
            return is_null($valor) ? '<i class="fa fa-minus"></i>':(($valor==true)?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
        }elseif ($tipo == self::RENDER_IMAGE) {
            return '<img src="' . $valor . '" alt="Image" class="img-responsive img-fluid easypanel-img" />';
        } elseif ($tipo == self::RENDER_LINK) {
            return '<a href="' . $valor . '" target="_blank"  class="easypanel-link">'.$valor.'</a>';
        } elseif ($tipo == self::RENDER_JSON) {
            $list = $valor;
            $html = '<ul>';
            foreach ($list as $clave=>$item){
                if(is_array($item)){
                    $html .= '<ol>';
                    foreach ($list as $clav=>$ite){
                        $html .= '<li>'.$clave.' => '.json_encode($ite).'</li>';
                    }
                    $html .= '</ol>';
                }else{
                    $html .= '<li>'.$clave.' => '.$item.'</li>';
                }
            }
            $html .= '<ul>';
            return $html;
        } elseif ($tipo == self::RENDER_ARRAY) {
            $html = '<ul>';
            foreach ($valor as $clave=>$item){
                $html .= '<li>'.$clave.' => '.$item.'</li>';
            }
            $html .= '<ul>';
            return $html;
        } elseif ($tipo == self::RENDER_DATE) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_TIME) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_DATETIME) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_TIMESTAMP) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_TRANSLATE) {
            if(!is_null($valor)){
                if(!is_array($valor)){
                    $tmp = $valor;
                    $valor = [$tmp];
                }
                $html = '';
                foreach ($valor as $idioma => $val):
                    $html .= '<div class="render-translate-item row ">';
                    $html .= '<div class="render-translate-languaje col-1 col-sm-1">'.$idioma.'</div>';
                    $html .= '<div class="render-translate-content col-10 col-sm-10">'.$val.'</div>';
                    $html .= '</div>';
                endforeach;
                return $html;
            }else{
                return '';
            }
        } elseif ($tipo == self::RENDER_RAW) {
            return $valor;
        } else {
            return $valor;
        }
    }

    protected function opcion($route, $parametros, $title, $clase = null, $fa_icon = null)
    {
        if (is_null($fa_icon)) {
            $fa_icon = 'fa-square-o';
        }
        if (is_null($clase)) {
            $clase = '';
        }
        return array(
            "route" => $route,
            "parameters" => $parametros,
            "texto" => $title,
            "fa_icon" => $fa_icon,
            "clase" => $clase
        );
    }

    protected function defineHeaders($columnas, $cabeceras,$firstColumnCount = false)
    {
        if (count($cabeceras) == 0) {
            $cabeceras = [];
            if($firstColumnCount){
                $cabeceras[]='#';
            }
            foreach ($columnas as $columna => $render) {
                if(strpos($columna,'.')!==false){
                    list($titulo,$submetodo)= explode('.',$columna);
                    $cabeceras[] = ucwords($titulo);
                }else{
                    $cabeceras[] = ucwords($columna);
                }
            }
        }


        return $cabeceras;
    }

    protected function generateParameters($arrayObject, $opciones)
    {
        $limite = count($opciones);
        for ($i = 0; $i < $limite; $i++) {
            $param = [];
            foreach ($opciones[$i]["parameters"] as $key => $campo):
                $param[$key] = $arrayObject[$key];
            endforeach;
            $opciones[$i]["parameters"] = $param;
        }
        return $opciones;
    }


    public function renderAsImage ($columna, $path=''){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_IMAGE; $this->paths[$columna]=$path;} }
    public function renderAsBoolean($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_BOOLEAN;}}
    public function renderAsDate ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_DATE;} }
    public function renderAsTime ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_TIME;} }
    public function renderAsDateTime ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_DATETIME;} }
    public function renderAsTimestamp($columna) { if (isset($this->columnas[$columna])) { $this->columnas[$columna] = self::RENDER_TIMESTAMP; } }
    public function renderAsRaw ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_RAW;} }
    public function renderAsLink ($columna, $path=''){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_LINK; $this->paths[$columna]=$path;} }
    public function renderAsJson ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_JSON;} }
    public function renderAsArray ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_ARRAY;} }
    public function renderAsTranslate ($columna){ if (isset($this->columnas[$columna])){ $this->columnas[$columna] = self::RENDER_TRANSLATE;} }

    public function apiGroupField($field){
        $this->includeGroup = true;
        $this->groupField[] = $field;
    }

    protected function groupFieldsApi($arrayObject){
        foreach ($this->groupField as $field){
            if(isset($arrayObject[$field])){
                throw new \Error('No puedes agrupar valores en el campo '.$field.' por que es un valor que ya existe');
            }else{
                foreach ($arrayObject as $label=>$value ){
                    if(strpos($label,$field.'.')!==false){
                        $arrayObject[$field][str_replace($field.'.','',$label)] = $value;
                        unset($arrayObject[$label]);
                    }
                }
            }
        }
        return $arrayObject;
    }


}