<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use Ast\EasyPanelBundle\Lib\Easy\Panel;
use \Doctrine\ORM\EntityManager;
use Twig\Environment;

class EasyPanelCreate
{
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

    protected $claseLogin = 'administrador';

    public function __construct(
        EntityManager $entityManager,
        Environment $templating,
        $kernel_project_dir,
        $panelType,
        $proyecto,
        $entitybundle,
        $prefix,
        $folder,
        $exclude
    )
    {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir;
        $this->panelType = $panelType;
        $this->proyecto = $proyecto;
        $this->folder = $folder;
        $this->entitybundle = $entitybundle;
        $this->prefix = $prefix;
        $this->exclude = $exclude;

    }

    /**
     * @param string $claseLogin
     */
    public function setClaseLogin(string $claseLogin): void
    {
        $this->claseLogin = $claseLogin;
    }


    public function create($ignorar=null)
    {
        $instrucciones = ['Controladores Creados'];
        $crud = new EasyPanelController($this->em,$this->templating,$this->kernel_project_dir,$this->panelType,$this->prefix,$this->folder);
        if($this->panelType == Panel::PANEL_HTML){
            $filename = $crud->createDefaultController();
            $instrucciones[]='Default Creado ';
            $instrucciones[]=$filename;
        }

        $listaclases = InfoEntityImport::folder($this->em,$this->kernel_project_dir.DIRECTORY_SEPARATOR.$this->entitybundle.DIRECTORY_SEPARATOR);
        $listaclases = $this->excluirEntidades($listaclases);
        $creados = [];

        if(count($listaclases)>0){
            $listaentitys = [];
            foreach ($listaclases as $namespaceEntity):

                $entity = Util::getFileNamespace($namespaceEntity);

                if(strtolower($entity) == $this->claseLogin){
                    $crud->createLoginController($namespaceEntity);
                }

                $tmp = $crud->createController($namespaceEntity);
                $creados[] = $tmp;
                $instrucciones []= $tmp.PHP_EOL;
                $listaentitys[] = $entity;
            endforeach;

            //$menu = (new EasyPanelMenu($this->em,$this->templating,$this->kernel_project_dir,$this->proyecto,$this->panelbundle,$listaentitys,$this->prefix,'material'))->create();
            //$instrucciones .= PHP_EOL.PHP_EOL.$menu;
        }

        return $instrucciones;
    }



    protected function createController()
    {
        $crud = new EasyPanelController($this->em, $this->templating, $this->kernel_project_dir, $this->panelType, $this->folder);
        $crud->createDefaultController();

        $entity = 'Default';
        $ruta = $this->prefix . '_' . strtolower($entity);
        if ($this->panelType == Panel::PANEL_HTML) {
            $crud = new EasyPanelController($this->em, $this->templating, $this->kernel_project_dir, $this->panelType, $this->panelbundle, $entity, $this->prefix, $ruta, ucfirst(''));
            $crud->createDefaultController();
        }

        $listaclases = InfoEntityImport::folder($this->em, $this->kernel_project_dir . $this->entitybundle);
        $listaclases = $this->excluirEntidades($listaclases);
        $creados = [];
        $instrucciones = 'Controladores Creados' . PHP_EOL;
        if (count($listaclases) > 0) {
            $listaentitys = [];
            foreach ($listaclases as $clase):

                $entity = Util::getFileNamespace($clase);
                $ruta = $this->prefix . '_' . strtolower($entity);
                if (strtolower($entity) == 'administrador') {
                    $crud = new EasyPanelController($this->em, $this->templating, $this->kernel_project_dir, $this->panelType, $this->panelbundle, $clase, $this->prefix, $ruta, ucfirst(''));
                    $crud->createLoginController();
                }

                $crud = new EasyPanelController($this->em, $this->templating, $this->kernel_project_dir, $this->panelType, $this->panelbundle, $clase, $this->prefix, $ruta, ucfirst($entity), $ignorar);
                $tmp = $crud->createController();
                $creados[] = $tmp;
                $instrucciones .= $tmp . PHP_EOL;
                $listaentitys[] = $entity;
            endforeach;

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