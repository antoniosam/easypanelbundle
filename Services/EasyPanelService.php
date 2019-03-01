<?php
/**
 * Created by marcosamano
 * Date: 27/02/19
 */
namespace Ast\EasyPanelBundle\Services;

use Ast\EasyPanelBundle\Lib\Easy\EasyForm;
use Ast\EasyPanelBundle\Lib\Easy\EasyList;
use Ast\EasyPanelBundle\Lib\Easy\EasyShow;
use Ast\EasyPanelBundle\Lib\Easy\Panel;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Doctrine\ORM\EntityManager;

class EasyPanelService
{

    private $twig;
    private $panellayout;
    private $panelvista;
    private $panelmenu;
    private $panelnompreproyecto;
    private $panelrutalogout;

    function __construct(Twig_Environment $twig,  $layout, $vista, $menu, $nombreproyecto, $rutalogout)
    {
        $this->twig = $twig;
        $this->panellayout = ($layout == null)?'@EasyPanel/layoutmaterial.html.twig':$layout;
        $this->panelvista = ($vista == null)?'@EasyPanel/viewmaterial.html.twig':$vista;
        $this->panelmenu = ($menu == null)?'@EasyPanel/Default/menumaterial.html.twig':$menu;
        $this->panelnompreproyecto = ($nombreproyecto == null)?'':$nombreproyecto;
        $this->panelrutalogout = ($rutalogout == null)?'':$rutalogout;
    }

    function render($vista, Response $response = null)
    {
        if($vista instanceof EasyList){
            $panel = new Panel();
            $panel->addList($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->createView();
        }else if($vista instanceof EasyShow){
            $panel = new Panel();
            $panel->addShow($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->createView();
        }else if($vista instanceof EasyForm){
            $panel = new Panel();
            $panel->addForm($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->createView();
        }else if($vista instanceof Panel){
            $parameters = $vista->createView();
        }

        $parameters['layout'] = $this->panellayout;
        $parameters['menu'] = $this->panelmenu;
        $parameters['nombreproyecto'] = $this->panelnompreproyecto;
        $parameters['rutalogout'] = $this->panelrutalogout;

        $content = $this->twig->render($this->panelvista, $parameters);
        if (null === $response) {
            $response = new Response();
        }
        $response->setContent($content);

        return $response;
    }
}