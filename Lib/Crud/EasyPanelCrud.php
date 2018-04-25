<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;

class EasyPanelCrud
{
    private $em;
    private $templating;
    private $kernel_project_dir;
    private $panelbundle;
    private $entitybundle;
    private $entity;
    private $ruta;
    private $seccion;
    private $campos;
    private $columnas;

    public function __construct(
        \Doctrine\ORM\EntityManager $entityManager,
        \Twig_Environment $templating,
        $kernel_project_dir,
        $panelbundle,
        $entity,
        $ruta,
        $seccion
    ) {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir ;
        $this->panelbundle = $panelbundle;
        $this->namespaceentity = $entity;
        $this->ruta = $ruta;
        $this->seccion = $seccion;
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
        $filename = Util::fixFilename($this->kernel_project_dir.DIRECTORY_SEPARATOR.$this->namespaceentity).'.php';

        if(file_exists($filename)){
            $this->entity = Util::getFileNamespace($this->namespaceentity);
            //$this->entitybundle = $this->createBundleEntity($this->entity,$this->panelbundle);
            $ignorar = Util::getArray($ignore);
            $this->campos = $this->fieldsEntity($this->namespaceentity,$ignorar);
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
            "indexlist" => $indexlist,
            "showlist" => $showlist
        );

        if ($type_crud == EasyPanelCreateAuto::TYPE_EASY) {
            $html = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $parametros);
        } elseif ($type_crud == EasyPanelCreateAuto::TYPE_EASY_MIN)  {
            $html = $this->templating->render('@EasyPanel/Crud/controller.easy.min.html.twig', $parametros);
        }elseif ($type_crud == EasyPanelCreateAuto::TYPE_NORMAL){
            $html = $this->templating->render('@EasyPanel/Crud/controller.html.twig', $parametros);
        }else{
            $html = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $parametros);
        }

        $ruta = $this->createDir($panelbundle,'Controller');
        $name = $entity . "Controller.php";

        file_put_contents($ruta . $name, $html);

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
            'columnas' => $formlist
        );

        $html = $this->templating->render('@EasyPanel/Crud/form.html.twig', $parametros);

        $ruta = $this->createDir($bundle,'Form');
        $name = $entity . "Type.php";

        file_put_contents($ruta . $name, $html);

        return $name;
    }

    private function createDir($bundle,$carpeta){
        $filename = $this->kernel_project_dir . $bundle . '/'.$carpeta.'/';
        if (!file_exists($filename)) {
            mkdir($filename, 0777, true);
        }
        return $filename;
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