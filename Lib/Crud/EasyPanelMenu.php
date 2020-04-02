<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;


use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;
use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use \Doctrine\ORM\EntityManager;
use \Twig\Environment;

class EasyPanelMenu
{


    protected $em;
    protected $templating;
    protected $kernel_project_dir;
    protected $proyecto;
    protected $folderdir;
    protected $folder;
    protected $entitybundle;
    protected $prefix;
    protected $tema;

    public function __construct(
        EntityManager $entityManager,
        Environment $templating,
        $kernel_project_dir,
        $proyecto,
        $listaentitys,
        $prefix,
        $folder,
        $tema
    )
    {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir ;
        $this->proyecto = $proyecto;
        $this->folder = $folder;
        $this->entitybundle = $listaentitys;
        $this->prefix = $prefix;
        $this->tema = $tema;

        $this->folderdir = $this->kernel_project_dir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$this->folder;

    }

    public function create()
    {
        if(is_array($this->entitybundle)){
            $entitys = $this->entitybundle;
        }else{
            $entitys =[];
            foreach (InfoEntityImport::folder($this->em,$this->entitybundle) as $entitybundle):
                $entitys[] = Util::getFileNamespace($entitybundle);
            endforeach;
        }
        $lista = [];
        foreach ($entitys as $entity):
            $ruta = strtolower($entity);
            if(in_array($ruta,['fotos','foto','imagenes','galeria'])){
                $lista[]=[$ruta,$this->prefix."_".$ruta,'fa-picture-o'];
            }elseif(in_array($ruta,['usuarios','usuario','clientes','cliente'])){
                $lista[]=[$ruta,$this->prefix."_".$ruta,'fa-users'];
            }elseif(in_array($ruta,['adminitradores','adminitrador'])){
                $lista[]=[$ruta,$this->prefix."_".$ruta,'fa-user-circle-o'];
            }elseif(in_array($ruta,['config','configuraciones'])){
                $lista[]=[$ruta,$this->prefix."_".$ruta,'fa-gear'];
            }else{
                $lista[]=[$ruta,$this->prefix."_".$ruta,'fa-list-ul'];
            }
        endforeach;

        $parametros=[];
        $parametros['proyecto'] = $this->proyecto;
        $parametros['simple']= EasyPanelCreate::MENU_EXPAND;
        $parametros['rutas'] = $lista;
        $parametros['prefix'] = $this->prefix;

        if($this->tema == 'material'){
            $html = $this->templating->render('@EasyPanel/Crud/menumaterial.html.twig', $parametros);
        }else{
            $html = $this->templating->render('@EasyPanel/Crud/menu.html.twig', $parametros);
        }


        file_put_contents($this->folderdir.'/menu_gen.html.twig',$html);

        return 'Menu Creado con '.count($lista).' rutas';
    }



}