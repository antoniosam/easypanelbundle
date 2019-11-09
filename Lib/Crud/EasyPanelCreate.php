<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use namespace Ast\EasyPanelBundle\Lib\Easy\Panel;

class EasyPanelCreate
{
    const TYPE_EASY = "EasyPanel";
    const TYPE_EASY_MIN = "EasyPanelMin";
    const TYPE_NORMAL = "Normal";
    const MENU_COLLAPSE = "Collapse";
    const MENU_EXPAND = "Expand";

    protected $em;
    protected $templating;
    protected $kernel_project_dir;
    protected $panelType;
    protected $proyecto;
    protected $panelbundledir;
    protected $panelbundle;
    protected $entitybundle;
    protected $prefix;
    protected $exclude;

    public function __construct(
        \Doctrine\ORM\EntityManager $entityManager,
        \Twig_Environment $templating,
        $kernel_project_dir,
        $panelType,
        $proyecto,
        $panelbundle,
        $entitybundle,
        $prefix,
        $exclude
    )
    {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir;
        $this->panelType = $panelType;
        $this->proyecto = $proyecto;
        $this->panelbundle = $panelbundle;
        $this->entitybundle = $entitybundle;
        $this->prefix = $prefix;
        $this->exclude = $exclude;

    }


    public function create($ignorar=null)
    {
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $entity = 'Default';
            $ruta = $this->prefix.'_'.strtolower($entity);
            if($this->panelType == Panel::PANEL_HTML){
                $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelType,$this->panelbundle,$entity,$this->prefix,$ruta,ucfirst(''));
                $crud->createDefaultController();    
            }
            
            $listaclases = InfoEntityImport::folder($this->em,$this->kernel_project_dir.$this->entitybundle);
            $listaclases = $this->excluirEntidades($listaclases);
            $creados = [];
            $instrucciones = 'Controladores Creados'.PHP_EOL;
            if(count($listaclases)>0){
                $listaentitys = [];
                foreach ($listaclases as $clase):

                    $entity = Util::getFileNamespace($clase);
                    $ruta = $this->prefix.'_'.strtolower($entity);
                    if(strtolower($entity) == 'administrador'){
                        $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelType,$this->panelbundle,$clase,$this->prefix,$ruta,ucfirst(''));
                        $crud->createLoginController();
                    }

                    $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelType,$this->panelbundle,$clase,$this->prefix,$ruta,ucfirst($entity),$ignorar);
                    $tmp = $crud->createController();
                    $creados[] = $tmp;
                    $instrucciones .= $tmp.PHP_EOL;
                    $listaentitys[] = $entity;
                endforeach;

                $menu = (new EasyPanelMenu($this->em,$this->templating,$this->kernel_project_dir,$this->proyecto,$this->panelbundle,$listaentitys,$this->prefix,'material'))->create();
                $instrucciones .= PHP_EOL.PHP_EOL.$menu;
            }

        }else{
            $instrucciones = $this->createSf3($ignorar);
        }

        return $instrucciones;
    }

    protected function createSf3($ignorar){
        if($this->findBundle()){

            $entity = 'Default';
            $ruta = $this->prefix.'_'.strtolower($entity);
            if($this->panelType == Panel::PANEL_HTML){
                $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelbundle,$entity,$this->prefix,$ruta,ucfirst(''));
                $crud->createDefaultController();
            }

            $listaclases = InfoEntityImport::folder($this->em,$this->kernel_project_dir.$this->entitybundle);
            $listaclases = $this->excluirEntidades($listaclases);
            $creados = [];

            $instrucciones = ' Respuesta'.PHP_EOL;
            if(count($listaclases)>0){
                $listaentitys = [];
                foreach ($listaclases as $clase):
                    $entity = Util::getFileNamespace($clase);
                    $ruta = $this->prefix.'_'.strtolower($entity);
                    if(strtolower($entity) == 'administrador'){
                        $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelbundle,$clase,$this->prefix,$ruta,ucfirst(''));
                        $crud->createLoginController();
                    }
                    $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelbundle,$clase,$this->prefix,$ruta,ucfirst($entity),$ignorar);
                    $tmp = $crud->createController();
                    $creados[] = $tmp;
                    $instrucciones .= $tmp.PHP_EOL;
                    $listaentitys[] = $entity;
                endforeach;
                $menu = (new EasyPanelMenu($this->em,$this->templating,$this->kernel_project_dir,$this->proyecto,$this->panelbundle,$listaentitys,$this->prefix,'material'))->create();
                $instrucciones .= PHP_EOL.PHP_EOL.$menu;
            }
            $instrucciones .= ' Controladores Creados'.PHP_EOL;
            $instrucciones .="Para concluir debes agregar bundle a la configuracion".PHP_EOL;
            $instrucciones .="En el archivo routing.yml debes agregar la nueva ruta".PHP_EOL;
            $instrucciones .=$this->prefix."_route:".PHP_EOL;
            $instrucciones .="    resource: '@".$this->panelbundle."/Controller/'".PHP_EOL;
            $instrucciones .='    type: annotation'.PHP_EOL;
            $instrucciones .='    prefix: /'.$this->prefix.PHP_EOL;

        }else{
            $instrucciones = "Interrumpido, Debes configurar el nuevo bundle antes de continuar ".PHP_EOL .PHP_EOL ;
            $instrucciones.='Agrega al composer.json dentro de psr-4 => "'.$this->panelbundle.'\\": "src/'.$this->panelbundle.'"'.PHP_EOL;
            $instrucciones.='Agrega al AppKernel.php => new '.$this->panelbundle.'\\'.$this->panelbundle.'(),' .PHP_EOL;
            $instrucciones.= "Limpia la cache => composer dump- && php bin/console cache:clear".PHP_EOL.PHP_EOL ;
            $instrucciones.= "Despues vuelve a ejecutar el creador".PHP_EOL  ;
            return $instrucciones;
        }
        return $instrucciones;
    }

    protected function findBundle(){
        $this->panelbundledir = $this->kernel_project_dir . $this->panelbundle . '/';
        Util::createDir($this->panelbundledir);
        if (!file_exists($this->panelbundledir.$this->panelbundle.'.php')) {
            $html ='<?php'.PHP_EOL.PHP_EOL.'namespace '.$this->panelbundle.';'.PHP_EOL.PHP_EOL.'use Symfony\Component\HttpKernel\Bundle\Bundle;'.PHP_EOL.PHP_EOL.'class '.$this->panelbundle.' extends Bundle'.PHP_EOL.'{'.PHP_EOL.'}'.PHP_EOL;
            file_put_contents($this->panelbundledir.$this->panelbundle.'.php',$html);
            return false;
        }else{
            return true;
        }

    }

    protected function excluirEntidades($listaclases){
        if(!empty($this->exclude)){
            $excluir = [];
            foreach (explode(',',$this->exclude) as $exclude):
                $excluir[] = strtolower(trim($exclude));
            endforeach;
            $lista = [];
            foreach ($listaclases as $clase) :
                $entity = strtolower(Util::getFileNamespace($clase));
                if(!in_array($entity ,$excluir)){
                    $lista[] = $clase;
                }
            endforeach;
            return  $lista;
        }else{
            return $listaclases;
        }
    }




}