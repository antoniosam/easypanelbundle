<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;


class EasyPanelController
{
    private $em;
    private $templating;
    private $kernel_project_dir;
    private $panelbundle;
    private $entitybundle;
    private $entity;
    private $prefix;
    private $ruta;
    private $seccion;
    private $campos;
    private $columnas;
    private $rutacontroller;
    private $rutaform;
    private $rutatemplates;

    private $namespacedircontroller ;
    private $namespacedirform ;
    private $formnamespace;


    public function __construct(
        \Doctrine\ORM\EntityManager $entityManager,
        \Twig_Environment $templating,
        $kernel_project_dir,
        $panelbundle,
        $entity,
        $prefix,
        $ruta,
        $seccion
    ) {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir ;
        $this->panelbundle = $panelbundle;
        $this->namespaceentity = $entity;
        $this->ruta = $ruta;
        $this->prefix = $prefix;
        $this->seccion = $seccion;
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $this->rutacontroller = $this->kernel_project_dir.'Controller/'.$this->panelbundle;
            $this->rutaform = $this->kernel_project_dir.'Form/'.$this->panelbundle;
            $this->rutatemplates = $this->kernel_project_dir.'../templates/'.$this->panelbundle;
        }else{
            $this->rutacontroller =  $this->kernel_project_dir . $this->panelbundle . '/Controller/';
            $this->rutaform = $this->kernel_project_dir.$this->panelbundle .'/Form/';
            $this->rutatemplates = $this->kernel_project_dir.$this->panelbundle .'/Resources/views/';
        }
        Util::createDir($this->rutacontroller);
        Util::createDir($this->rutaform);
        Util::createDir($this->rutatemplates);
    }

    protected function createBundleEntity($entity,$panelbunle){
        $temp = str_replace("\\", "/", $entity);
        if(strpos($temp,"/")!==false){
            return $entity;
        }elseif(strpos($temp,"\\")!==false){
            return $entity;
        }else{
            return $panelbunle."\\Entity\\".$entity;
        }
    }

    public function create($type_crud=null,$ignore=null)
    {
        //Obtener la ruta completa de la clase que se creara
        $a = new \ReflectionClass($this->namespaceentity);
        $filename = $a->getFileName();


        if(file_exists($filename)){
            //Recuperamos el nombre de la clase
            $this->entity = Util::getFileNamespace($this->namespaceentity);
            //lista de campos excluyendos los campos a ignorar
            $this->campos = $this->fieldsEntity($this->namespaceentity,Util::getArray($ignore));
            if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
                $this->namespacedircontroller = 'App\\Controller\\'.$this->panelbundle;
                $this->namespacedirform = 'App\\Form\\'.$this->panelbundle;
                $this->formnamespace = 'App\\Form\\'.$this->panelbundle.'\\'.$this->entity.'Type';
            }else{
                $this->namespacedircontroller = $this->panelbundle.'\\Controller';
                $this->namespacedirform = $this->panelbundle.'\\Form';
                $this->formnamespace = $this->panelbundle.'\\Form\\'.$this->entity.'Type';
            }

            $controller = $this->createController($this->campos, $this->panelbundle, $this->namespaceentity, $this->entity, $this->ruta, $this->seccion, $type_crud);
            $form = $this->createForm($this->campos, $this->panelbundle, $this->namespaceentity,$this->entity);

            $tempdir = Util::fixFilename($this->kernel_project_dir);
            return 'Crud ' . str_replace($tempdir,'',$controller) . ' ' . str_replace($tempdir,'',$form).' '.PHP_EOL;
        }else{
            return 'Error  ' .$this->namespaceentity.PHP_EOL;
        }



    }

    private function createController(
        $campos,
        $panelbundle,
        $entitybundle,
        $entity,
        $ruta,
        $seccion,
        $type_crud
    ) {
        $indexexclude = ['creado', 'actualizado', 'contenido', 'descripcion', 'slug', 'folio'];
        $showexclude = ['creado', 'actualizado', 'slug'];

        $indexlist = $this->getColumnas($this->ignoreFields($campos,$indexexclude));

        $showlist =  $this->getColumnas($this->ignoreFields($campos,$showexclude));


        $parametros = array(
            'seccion' => $seccion,
            'ruta' => $ruta,
            'entity' => $entity,
            'entitybundle' => $entitybundle,
            'form' => $entity . 'Type',
            'bundle' => $panelbundle,
            'indexlist' => $indexlist,
            'showlist' => $showlist,
            'namespace' => $this->namespacedircontroller,
            'formnamespace' => $this->formnamespace,
            'prefix_controller_route' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'/'.$this->prefix:''
        );

        /*if ($type_crud == EasyPanelCreateAuto::TYPE_EASY) {
            $html = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $parametros);
        } elseif ($type_crud == EasyPanelCreateAuto::TYPE_EASY_MIN)  {
            $html = $this->templating->render('@EasyPanel/Crud/controller.easy.min.html.twig', $parametros);
        }elseif ($type_crud == EasyPanelCreateAuto::TYPE_NORMAL){
            $html = $this->templating->render('@EasyPanel/Crud/controller.html.twig', $parametros);
        }else{

        }*/
        $html = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $parametros);
        $name = $entity . "Controller.php";

        file_put_contents($this->rutacontroller .'/'. $name, $html);

        return $name;
    }

    private function createForm($columnas, $bundle, $entitybundle, $entity)
    {

        $formexclude = ['creado', 'actualizado', 'slug'];

        $formlist =  $this->ignoreFields($columnas,$formexclude);

        $parametros = array(
            'bundle' => $bundle,
            'entity' => $entity,
            'entitybundle' => $entitybundle,
            'columnas' => $formlist,
            'namespace' => $this->namespacedirform,
        );

        $html = $this->templating->render('@EasyPanel/Crud/form.html.twig', $parametros);


        $name = $entity . "Type.php";

        file_put_contents($this->rutaform .'/'. $name, $html);

        return $name;
    }



    private function fieldsEntity($entitybundle, array  $excluir = [])
    {
        $columnas = InfoEntityImport::fieldsEntity($this->em,$entitybundle);
        return (count($excluir)>0)?$this->ignoreFields($columnas,$excluir): $columnas;
    }

    private function ignoreFields($columnas, $excluir)
    {
        $list = [];
        foreach ($columnas as $columna):
            if (!in_array($columna['fieldName'], $excluir)) {
                $list[] = $columna;
            }
        endforeach;
        return $list;
    }


    private function getColumnas($columnas)
    {
        $list = [];
        foreach ($columnas as $columna):
            $list[] = $columna['fieldName'];
        endforeach;
        return $list;
    }


}