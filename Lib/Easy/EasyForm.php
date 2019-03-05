<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyForm extends EasyView
{

    private $seccion = "";
    private $opciones = array();
    private $has_delete = false;
    private $deleteform;

    private $form;

    private $hasincludelayout = false;
    private $includelayout;

    function __construct($seccion, $form)
    {
        $this->seccion = $seccion;
        $this->form = $form;

    }

    /**
     * @return string
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    public function addLinkBack( $route, $parametros,$nombre )
    {
        $this->addLink( $route,$parametros,$nombre, 'btn-secondary', 'fa-arrow-left');
    }

    public function addLinkShow($route, $parametros, $nombre)
    {
        $this->addLink( $route, $parametros, $nombre, 'btn-success', 'fa-list-ul');
    }

    public function addLink($route, $parametros, $texto, $clase = 'btn-secondary', $fa_icon = null)
    {
        $this->opciones[] = $this->opcion($route, $parametros, $texto, $clase, $fa_icon);
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
     * @param mixed $includelayout
     */
    public function setIncludeLayout($includelayout)
    {
        $this->includelayout = $includelayout;
        $this->hasincludelayout = true;
    }

    /**
     * @return array
     */
    public function generar()
    {
        $return = array();
        $return["seccion"] = $this->seccion;
        $return["form"] = $this->form;
        $return["rutas"] = $this->opciones;
        $return["has_delete"] = $this->has_delete;
        $return["delete"] = $this->deleteform;
        $return["has_includelayout"] = $this->hasincludelayout;
        $return["includelayout"] = $this->includelayout;

        return $return;
    }

    /**
     * @param $seccion
     * @param $form
     * @param $prefix
     * @param null $deleteform
     * @return EasyForm
     */
    public static function easy($seccion, $form, $prefix = null)
    {
        $form = new EasyForm("Formulario de " . $seccion, $form);
        if(!empty($prefix)){
            $form->addLinkBack($prefix . '_index', [], "Regresar " );
        }
        return $form;
    }


}