<?php
/**
 * Created by antoniosam.
 */
namespace Ast\EasyPanelBundle\Lib\Easy;

use Ast\EasyPanelBundle\Lib\Easy\EasyForm;
use Ast\EasyPanelBundle\Lib\Easy\EasyList;
use Ast\EasyPanelBundle\Lib\Easy\EasyShow;

class Panel
{
    
    public const PANEL_HTML = 'html';
    public  const PANEL_API = 'api';
    private $matrix = [];
    private $location = "My Dashboard";
    private $directories = [];

    private $layout;

    private $hasincludelayout = false;
    private $includelayout;
    private $typePanel = 0;

    function __construct($typePanel = self::PANEL_HTML, $layout = null)
    {
        $this->typePanel =  $typePanel;
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
        $this->matrix[] = array('type' => "form", 'data' => $datos->generar());
    }

    /**
     * @param EasyList $datos
     */
    public function addList(EasyList $datos)
    {
        if($this->typePanel == self::PANEL_HTML){
            $this->matrix[] = ['type' => 'list', 'data' => $datos->generatetoHtml()];
        }else if($this->typePanel == self::PANEL_API){
            $this->matrix = $datos->generatetoApi();
        }
    }

    /**
     * @param EasyShow $datos
     */
    public function addShow(EasyShow $datos)
    {
        if($this->typePanel == self::PANEL_HTML){
            $this->matrix[] = ['type' => "show", 'data' => $datos->generatetoHtml()];
        }else if($this->typePanel == self::PANEL_API){
            $this->matrix = $datos->generatetoApi();
        }
    }

    /**
     * @param EasyShow $datos
     */
    public function addHtml($html)
    {
        $this->matrix[] = array("type" => "html", "data" => $html);
    }

    public function create()
    {
        if($this->typePanel == self::PANEL_HTML){
            return array('cards' => $this->matrix ,'location'=>$this->location ,"directories"=>$this->directories,"layout"=>$this->layout,'has_includelayout' => $this->hasincludelayout,'includelayout' => $this->includelayout);
        }else if($this->typePanel == self::PANEL_API){
            return $this->matrix;
        }
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
     * @param $seccion
     * @param $consulta
     * @param array $columnas
     * @param $prefix
     * @param null $deleteform
     * @return array
     */
    public static function easyShowApi($seccion, $consulta, array $columnas)
    {
        $panel = new Panel(Panel::PANEL_API);
        $panel->addShow(EasyShow::easy($seccion,$consulta,$columnas));
        return $panel->create();
    }



}