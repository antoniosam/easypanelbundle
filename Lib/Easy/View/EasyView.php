<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy\View;


class EasyView
{

    const RENDER_TEXTO = "text";
    const RENDER_IMAGE = "image";
    const RENDER_BOOLEAN = "bool";
    const RENDER_FECHA = "date";
    const RENDER_TIME = "time";
    const RENDER_FECHATIME = "datetime";
    const RENDER_RAW = "raw";
    const RENDER_LINK = "link";
    const RENDER_JSON = 'json';

    /**
     * @param $tipo
     * @param $valor
     * @return string
     */
    protected function renderColumna($tipo, $valor,$path='')
    {

        if ($tipo == self::RENDER_IMAGE) {
            return '<img src="' . str_replace('//','/',$path.'/'.$valor) . '" alt="Image" class="img-responsive easypanel-img" />';
        } elseif ($tipo == self::RENDER_BOOLEAN) {
            return is_null($valor) ? '<i class="fa fa-minus"></i>':(($valor==true)?'<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
        } elseif ($tipo == self::RENDER_LINK) {
            return '<a href="' . str_replace('//','/',$path.'/'.$valor) . '" target="_blank"  class="easypanel-link">'.$valor.'</a>';
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
        } elseif ($tipo == self::RENDER_FECHA) {
            return is_null($valor)?'---':$valor->format("Y-m-d");
        } elseif ($tipo == self::RENDER_TIME) {
            return is_null($valor)?'---':$valor->format("H:i:s");
        } elseif ($tipo == self::RENDER_FECHATIME) {
            return is_null($valor)?'---':$valor->format("Y-m-d H:i:s");
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