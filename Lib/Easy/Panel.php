<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\EasyForm;
use Ast\EasyPanelBundle\Lib\Easy\EasyList;
use Ast\EasyPanelBundle\Lib\Easy\EasyShow;

class Panel
{
    private $matrix = [];
    private $location = "My Dashboard";
    private $directories = [];

    private $layout;

    private $hasincludelayout = false;
    private $includelayout;

    function __construct($layout=null)
    {
        $this->layout = (is_null($layout))?'layout.html.twig':$layout;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function addDirectory($texto,$route, $parametros = [] )
    {
        $this->directories[] =  array("route"=>$route,"parameters"=>$parametros,"texto"=>$texto );
    }


    /**
     * @param EasyList $datos
     */
    public function addForm(EasyForm $datos)
    {
        $this->matrix[] = array("type" => "form", "data" => $datos->generar());
    }

    /**
     * @param EasyList $datos
     */
    public function addList(EasyList $datos)
    {
        $this->matrix[] = array("type" => "list", "data" => $datos->generar());
    }

    /**
     * @param EasyShow $datos
     */
    public function addShow(EasyShow $datos)
    {
        $this->matrix[] = array("type" => "show", "data" => $datos->generar());
    }

    /**
     * @param EasyShow $datos
     */
    public function addHtml($html)
    {
        $this->matrix[] = array("type" => "html", "data" => $html);
    }

    public function createView()
    {
        return array('cards' => $this->matrix ,'location'=>$this->location ,"directories"=>$this->directories,"layout"=>$this->layout,'has_includelayout' => $this->hasincludelayout,'includelayout' => $this->includelayout);
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
     * @param EasyList $datos
     * @return array
     */
    public static function createList(EasyList $datos,$layout=null){
        $panel = new Panel($layout);
        $panel->addList($datos);
        $panel->setLocation($datos->getSeccion());
        return $panel->createView();
    }

    /**
     * @param EasyShow $datos
     * @return array
     */
    public static function createShow(EasyShow $datos,$layout=null){
        $panel = new Panel($layout);
        $panel->addShow($datos);
        $panel->setLocation($datos->getSeccion());
        return $panel->createView();
    }

    /**
     * @param EasyForm $datos
     * @return array
     */
    public static function createForm(EasyForm $datos,$layout=null){
        $panel = new Panel($layout);
        $panel->addForm($datos);
        $panel->setLocation($datos->getSeccion());
        return $panel->createView();
    }

    /**
     * @param $seccion
     * @param array $consulta
     * @param array $columnas
     * @param $prefix
     * @return array
     */
    public static function easyList($seccion, array $consulta, array $columnas, $prefix)
    {
        $panel = new Panel();
        $panel->addList(EasyList::easy($seccion, $consulta, $columnas, $prefix));
        $panel->setLocation($seccion);
        return $panel->createView();
    }

    /**
     * @param $seccion
     * @param $consulta
     * @param array $columnas
     * @param $prefix
     * @param null $deleteform
     * @return array
     */
    public static function easyShow($seccion, $consulta, array $columnas, $prefix,$deleteform = null)
    {
        $panel = new Panel();
        $panel->addShow(EasyShow::easy($seccion, $consulta, $columnas, $prefix,$deleteform));
        $panel->setLocation($seccion);
        return $panel->createView();
    }

    /**
     * @param $seccion
     * @param $form
     * @param $prefix
     * @param null $deleteform
     * @return array
     */
    public static function easyForm($seccion, $form, $prefix,$deleteform = null)
    {
        $panel = new Panel();
        $panel->addForm(EasyForm::easy($seccion, $form, $prefix,$deleteform));
        $panel->setLocation($seccion);
        return $panel->createView();
    }

}