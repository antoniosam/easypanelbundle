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

    function __construct(Twig_Environment $twig,  $layout, $vista)
    {
        $this->twig = $twig;
        $this->panellayout = ($layout == null)?'@EasyPanel/layout.html.twig':$layout;
        $this->panelvista = ($vista == null)?'@EasyPanel/view.html.twig':$vista;
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

        $content = $this->twig->render($this->panelvista, $parameters);
        if (null === $response) {
            $response = new Response();
        }
        $response->setContent($content);

        return $response;
    }
}