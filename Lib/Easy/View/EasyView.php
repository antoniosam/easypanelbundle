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
    const RENDER_RAW = "raw";

    /**
     * @param $tipo
     * @param $valor
     * @return string
     */
    protected function renderColumna($tipo,$valor){

        if($tipo == self::RENDER_IMAGE){
            return '<img src="'.$valor.'" alt="Image" "class"="img-responsive" />';
        }elseif($tipo == self::RENDER_BOOLEAN){
            return ($valor)? '<i class="fa fa-check"></i>':'<i class="fa fa-times"></i>';
        }elseif($tipo == self::RENDER_FECHA){
            return $valor->format("Y-m-d");
        }elseif($tipo == self::RENDER_RAW){
            return $valor;
        }else{
            return $valor;
        }
    }

    protected function opcion($route,$parametros,$title,$clase= null,$fa_icon = null){
        if(is_null($fa_icon)){
            $fa_icon = 'fa-square-o';
        }
        if(is_null($clase)){
            $clase = '';
        }
        return array("route"=>$route,"parameters"=>$parametros,"texto"=>$title, "fa_icon"=>$fa_icon, "clase"=>$clase);
    }

    protected function defineHeaders($columnas,$cabeceras,$opciones = null){
        if(count($cabeceras)==0){
            $cabeceras = array();
            foreach ($columnas as $columna => $render) {
                $cabeceras[] = ucwords($columna);
            }
        }
        if($opciones!=null){
            if(count($opciones)>0){
                $cabeceras[]="";
            }
        }
        return $cabeceras;
    }

    protected function generateParameters($objeto, $opciones){
        $limite = count($opciones);
        for ($i = 0; $i<$limite ;$i++){
            $param = [];
            foreach ($opciones[$i]["parameters"] as $key=>$campo):
                $getter = 'get'.$campo;
                $param[$key] = $objeto->$getter();
            endforeach;
            $opciones[$i]["parameters"] = $param;
        }
        return $opciones;
    }

}