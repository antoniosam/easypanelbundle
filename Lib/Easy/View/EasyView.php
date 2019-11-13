<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy\View;


use Symfony\Component\HttpFoundation\Request;

class EasyView
{
    const RENDER_TEXT = "text";
    /**
     * @deprecated deprecated since version 2.5
     */
    const RENDER_TEXTO = "text";
    const RENDER_IMAGE = "image";
    const RENDER_BOOLEAN = "bool";
    const RENDER_DATE = "date";
    /**
     * @deprecated deprecated since version 2.5
     */
    const RENDER_FECHA = "date";
    const RENDER_TIME = "time";
    const RENDER_DATETIME = "datetime";
    const RENDER_TIMESTAMP = 'timestamp';
    /**
     * @deprecated deprecated since version 2.5
     */
    const RENDER_FECHATIME = "datetime";
    const RENDER_RAW = "raw";
    const RENDER_LINK = "link";
    const RENDER_JSON = 'json';
    const RENDER_ARRAY = 'array';
    const RENDER_TRANSLATE = 'translate';

    private $request;


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
        if($tipo == self::RENDER_TEXTO && is_array($valor)){
            $tipo = self::RENDER_ARRAY;
        }elseif ($tipo == self::RENDER_TEXTO && $valor instanceof \DateTime){
            $tipo = self::RENDER_FECHATIME;
        }

        if ($tipo == self::RENDER_TEXT || $tipo == self::RENDER_TEXTO) {
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
        } elseif ($tipo == self::RENDER_DATE || $tipo == self::RENDER_FECHA) {
            return is_null($valor)?'---':$valor->format("Y-m-d");
        } elseif ($tipo == self::RENDER_TIME) {
            return is_null($valor)?'---':$valor->format("H:i:s");
        } elseif ($tipo == self::RENDER_DATETIME || $tipo == self::RENDER_FECHATIME) {
            return is_null($valor)?'---':$valor->format("Y-m-d H:i:s");
        } elseif ($tipo == self::RENDER_TIMESTAMP) {
            return is_null($valor)?0:$valor->getTimestamp();
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
     * @param $type
     * @param $valor
     * @return string
     */
    protected function renderValue($type, $value)
    {
        if($type == self::RENDER_TEXTO && is_array($value)){
            $type = self::RENDER_ARRAY;
        }elseif ($type == self::RENDER_TEXTO && $value instanceof \DateTime){
            $type = self::RENDER_FECHATIME;
        }

        if ($type == self::RENDER_TEXTO) {
            return '<p>' . $value . '</p>';
        } elseif ($type == self::RENDER_BOOLEAN) {
            return is_null($value) ? '<i class="fa fa-minus"></i>':(($value==true)?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
        }elseif ($type == self::RENDER_IMAGE) {
            return '<img src="' . $value . '" alt="Image" class="img-responsive img-fluid easypanel-img" />';
        } elseif ($type == self::RENDER_LINK) {
            return '<a href="' . $value . '" target="_blank"  class="easypanel-link">'.$value.'</a>';
        } elseif ($type == self::RENDER_JSON) {
            $html = '<ul>';
            foreach ($value as $clave=>$item){
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
        } elseif ($type == self::RENDER_ARRAY) {
            $html = '<ul>';
            foreach ($value as $clave=>$item){
                $html .= '<li>'.$clave.' => '.$item.'</li>';
            }
            $html .= '<ul>';
            return $html;
        } elseif ($type == self::RENDER_FECHA) {
            return $value;
        } elseif ($type == self::RENDER_TIME) {
            return $value;
        } elseif ($type == self::RENDER_FECHATIME) {
            return $value;
        } elseif ($type == self::RENDER_TRANSLATE) {
            $html = '';
            foreach ($value as $idioma => $val):
                $html .= '<div class="render-translate-item row ">';
                $html .= '<div class="render-translate-languaje col-1 col-sm-1">'.$idioma.'</div>';
                $html .= '<div class="render-translate-content col-10 col-sm-10">'.$val.'</div>';
                $html .= '</div>';
            endforeach;
            return $html;
        } elseif ($type == self::RENDER_RAW) {
            return $value;
        } else {
            return $value;
        }
    }


    /**
     * @param $tipo
     * @param $valor
     * @return string
     */
    protected function renderColumna($tipo, $valor,$path='')
    {
        if($tipo == self::RENDER_TEXTO && is_array($valor)){
            $tipo = self::RENDER_ARRAY;
        }elseif ($tipo == self::RENDER_TEXTO && $valor instanceof \DateTime){
            $tipo = self::RENDER_FECHATIME;
        }

        if ($tipo == self::RENDER_TEXTO) {
            return '<p>' . $valor . '</p>';
        } elseif ($tipo == self::RENDER_BOOLEAN) {
            return is_null($valor) ? '<i class="fa fa-minus"></i>':(($valor==true)?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
        }elseif ($tipo == self::RENDER_IMAGE) {
            $url = (strpos($path,$valor)!==false)? $path :str_replace('//','/',$path.'/'.$valor);
            return '<img src="' . $url . '" alt="Image" class="img-responsive img-fluid easypanel-img" />';
        } elseif ($tipo == self::RENDER_LINK) {
            $url = (strpos($path,$valor)!==false)? $path :str_replace('//','/',$path.'/'.$valor);
            return '<a href="' . $url . '" target="_blank"  class="easypanel-link">'.$valor.'</a>';
        } elseif ($tipo == self::RENDER_JSON) {
            $list = json_decode($valor,true);
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
        } elseif ($tipo == self::RENDER_FECHA) {
            return is_null($valor)?'---':$valor->format("Y-m-d");
        } elseif ($tipo == self::RENDER_TIME) {
            return is_null($valor)?'---':$valor->format("H:i:s");
        } elseif ($tipo == self::RENDER_FECHATIME) {
            return is_null($valor)?'---':$valor->format("Y-m-d H:i:s");
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
                $html .= '';
            }else{
                $html = 'NULL';
            }
            return $html;
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

    protected function defineHeaders($columnas, $cabeceras)
    {
        if (count($cabeceras) == 0) {
            $cabeceras = array();
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

    protected function generateParameters($objeto, $opciones)
    {
        $limite = count($opciones);
        for ($i = 0; $i < $limite; $i++) {
            $param = [];
            foreach ($opciones[$i]["parameters"] as $key => $campo):
                if(strpos($campo,'.')!==false){
                    list($subobjeto,$submetodo)= explode('.',$campo);
                    $temp = 'get' . $subobjeto;
                    $sub = $objeto->$temp();
                    if($sub!=null){
                        $temp = 'get' . $submetodo;
                        $param[$key] = $sub->$temp();
                    }else{
                        $param[$key] = null;
                    }
                }else{
                    $getter = 'get' . $campo;
                    $param[$key] = $objeto->$getter();
                }
            endforeach;
            $opciones[$i]["parameters"] = $param;
        }
        return $opciones;
    }

}