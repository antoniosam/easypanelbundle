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
use Twig\Environment;
use Doctrine\ORM\EntityManager;

class EasyPanelService
{

    private $twig;
    private $panellayout;
    private $panelvista;
    private $panelmenu;
    private $panelnompreproyecto;
    private $panelrutalogout;

    private $paneldefaultindex;
    private $paneldefaultdashboard;

    function __construct(Environment $twig,  $layout, $vista, $layoutlogin, $menu, $nombreproyecto, $rutalogout)
    {
        $this->twig = $twig;
        $this->panellayout = ($layout == null)?'@EasyPanel/layoutmaterial.html.twig':$layout;
        $this->panelvista = ($vista == null)?'@EasyPanel/viewmaterial.html.twig':$vista;
        $this->panellogin = ($layoutlogin == null)?((\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'@EasyPanel/loginlayout.html.twig':'@EasyPanel/loginlayoutsf3.html.twig'):$layoutlogin;
        $this->panelmenu = ($menu == null)?'@EasyPanel/Default/menumaterial.html.twig':$menu;
        $this->panelnompreproyecto = ($nombreproyecto == null)?'':$nombreproyecto;
        $this->panelrutalogout = ($rutalogout == null)?'':$rutalogout;

        if($layout == null || $layout == '@EasyPanel/layoutmaterial.html.twig'){
            $this->paneldefaultdashboard = '@EasyPanel/Default/dashboard.material.html.twig';
            $this->paneldefaultindex = '@EasyPanel/Default/index.material.html.twig';
        }else{
            $this->paneldefaultdashboard = '@EasyPanel/Default/dashboard.html.twig';
            $this->paneldefaultindex = '@EasyPanel/Default/index.html.twig';
        }

    }

    function paramsRenderLayout(){
        $parameters = [];
        $parameters['layout'] = $this->panellayout;
        $parameters['menu'] = $this->panelmenu;
        $parameters['nombreproyecto'] = $this->panelnompreproyecto;
        $parameters['rutalogout'] = $this->panelrutalogout;

        return $parameters;
    }

    function json($vista, Response $response = null)
    {
        if(is_array($vista)) {
            $panel = new Panel(Panel::PANEL_API);
            $seccion = '';
            foreach ($vista as $view){
                if($view instanceof EasyList){
                    $panel->addList($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if($view instanceof EasyShow){
                    $panel->addShow($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if($view instanceof EasyForm) {
                    $panel->addForm($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if(is_string($view) ) {
                    $panel->addHtml($view);
                }
            }
            $panel->setLocation($seccion);
            $parameters = $panel->create();
        }elseif($vista instanceof EasyList){
            $panel = new Panel(Panel::PANEL_API);
            $panel->addList($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if($vista instanceof EasyShow){
            $panel = new Panel(Panel::PANEL_API);
            $panel->addShow($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if($vista instanceof EasyForm){
            $panel = new Panel(Panel::PANEL_API);
            $panel->addForm($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if(is_string($vista)){
            $panel = new Panel(Panel::PANEL_API);
            $panel->addHtml($vista);
            $parameters = $panel->create();
        }else if($vista instanceof Panel){
            $parameters = $vista->create();
        }

        if (null === $response) {
            $response = new Response();
        }
        $response->setContent(json_encode($parameters));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    function render($vista, Response $response = null)
    {
        if(is_array($vista)) {
            $panel = new Panel();
            $seccion = '';
            foreach ($vista as $view){
                if($view instanceof EasyList){
                    $panel->addList($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if($view instanceof EasyShow){
                    $panel->addShow($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if($view instanceof EasyForm) {
                    $panel->addForm($view);
                    if($seccion == ''){
                        $seccion = $view->getSeccion();
                    }
                }else if(is_string($view) ) {
                    $panel->addHtml($view);
                }
            }
            $panel->setLocation($seccion);
            $parameters = $panel->create();
        }elseif($vista instanceof EasyList){
            $panel = new Panel();
            $panel->addList($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if($vista instanceof EasyShow){
            $panel = new Panel();
            $panel->addShow($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if($vista instanceof EasyForm){
            $panel = new Panel();
            $panel->addForm($vista);
            $panel->setLocation($vista->getSeccion());
            $parameters = $panel->create();
        }else if(is_string($vista)){
            $panel = new Panel();
            $panel->addHtml($vista);
            $parameters = $panel->create();
        }else if($vista instanceof Panel){
            $parameters = $vista->create();
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

    function renderTestHome(){
        $parameters = [];
        $parameters['layout'] = $this->panellayout;
        $parameters['menu'] = $this->panelmenu;
        $parameters['nombreproyecto'] = $this->panelnompreproyecto;
        $parameters['rutalogout'] = $this->panelrutalogout;

        $content = $this->twig->render($this->paneldefaultindex, $parameters);
        $response = new Response();

        $response->setContent($content);

        return $response;

    }

    function renderTestDashboard(){
        $parameters = [];
        $parameters['layout'] = $this->panellayout;
        $parameters['menu'] = $this->panelmenu;
        $parameters['nombreproyecto'] = $this->panelnompreproyecto;
        $parameters['rutalogout'] = $this->panelrutalogout;

        $content = $this->twig->render($this->paneldefaultdashboard, $parameters);
        $response = new Response();

        $response->setContent($content);

        return $response;

    }

    function renderLogin($params, Response $response = null){
        $params['nombreproyecto'] = $this->panelnompreproyecto;
        $content = $this->twig->render($this->panellogin, $params);
        if (null === $response) {
            $response = new Response();
        }
        $response->setContent($content);

        return $response;

    }
}