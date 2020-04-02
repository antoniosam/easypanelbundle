<?php
/**
 * Created by antoniosam
 */

namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\View\EasyView;

class EasyForm extends EasyView
{

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

    public function defaultConfigForm($routeBack,$paramsRoute,$formDelete = null){
        $this->addLinkBack($routeBack,$paramsRoute,'Regresar');
        if($formDelete){
            $this->setDeleteForm($formDelete);
        }
    }

    /**
     * @param $seccion
     * @param $form
     * @param $prefix
     * @param null $deleteform
     * @return EasyForm
     */
    public static function easy($seccion, $form)
    {
        $form = new EasyForm("Formulario de " . $seccion, $form);
        return $form;
    }


}