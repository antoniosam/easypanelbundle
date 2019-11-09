<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\InfoEntityImport;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use Ast\EasyPanelBundle\Lib\Easy\Panel;

class EasyPanelController
{
    private $em;
    private $templating;
    private $kernel_project_dir;
    private $panelType;
    private $panelbundle;
    private $entitybundle;
    private $entity;
    private $prefix;
    private $ruta;
    private $seccion;
    private $campos;
    private $ignore;
    private $columnas;
    private $rutacontroller;
    private $rutasecurity;
    private $rutaform;
    private $rutatemplates;

    private $prefixRouteController;
    private $serviceautowire;
    private $serviceincontroller;

    private $namespacedircontroller ;
    private $namespacedirform ;
    private $namespacedirsecurity ;
    private $formnamespace;
    private $pathpublicuploads;


    public function __construct(
        \Doctrine\ORM\EntityManager $entityManager,
        \Twig_Environment $templating,
        $kernel_project_dir,
        $panelType,
        $panelbundle,
        $entityNamespace,
        $prefix,
        $ruta,
        $seccion,
        $ignore
    ) {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir;
        $this->panelType = $panelType;
        $this->panelbundle = $panelbundle;
        $this->entityNamespace = $entityNamespace;
        $this->entity = Util::getFileNamespace($this->entityNamespace);
        $this->ruta = $ruta;
        $this->prefix = $prefix;
        $this->seccion = $seccion;
        $this->ignore = $ignore;
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $this->rutacontroller = $this->kernel_project_dir.'Controller/'.$this->panelbundle;
            $this->rutaform = $this->kernel_project_dir.'Form/'.$this->panelbundle;
            $this->rutatemplates = $this->kernel_project_dir.'../templates/'.$this->panelbundle;
            $this->rutasecurity= $this->kernel_project_dir.'Security/'.$this->panelbundle;

            $this->namespacedircontroller = 'App\\Controller\\'.$this->panelbundle;
            $this->namespacedirform = 'App\\Form\\'.$this->panelbundle;
            $this->formnamespace = 'App\\Form\\'.$this->panelbundle.'\\'.$this->entity.'Type';
            $this->pathpublicuploads = '/../public/uploads';
            
            $this->prefixRouteController = '/'.$this->prefix;
            $this->serviceautowire = ', EasyPanelService $easypanel';
            $this->serviceincontroller = '$easypanel';
        }else{
            $this->rutacontroller =  $this->kernel_project_dir . $this->panelbundle . '/Controller/';
            $this->rutaform = $this->kernel_project_dir.$this->panelbundle .'/Form/';
            $this->rutatemplates = $this->kernel_project_dir.$this->panelbundle .'/Resources/views/';
            $this->rutasecurity= $this->kernel_project_dir.$this->panelbundle .'/Security/';

            $this->namespacedircontroller = $this->panelbundle.'\\Controller';
            $this->namespacedirform = $this->panelbundle.'\\Form';
            $this->formnamespace = $this->panelbundle.'\\Form\\'.$this->entity.'Type';
            $this->pathpublicuploads = '/../web/uploads';

            $this->prefixRouteController = '';
            $this->serviceautowire = '';
            $this->serviceincontroller = '$this->get(EasyPanelService::class)';
        }
        Util::createDir($this->rutacontroller);
        Util::createDir($this->rutaform);
        Util::createDir($this->rutatemplates);
        Util::createDir($this->rutasecurity);
    }

    public function createOptions(){

        $indexexclude = ['creado', 'actualizado', 'contenido', 'descripcion', 'slug', 'folio','pass', 'salt','master','rol'];
        $indexlist = $this->getColumnas($this->ignoreFields($this->campos,$indexexclude));

        $showexclude = ['creado', 'actualizado', 'slug', 'salt'];
        $showlist =  $this->getColumnas($this->ignoreFields($this->campos,$showexclude));

        $formexclude = ['id','creado','actualizado', 'slug'];
        $formlist =  $this->ignoreFields($this->campos,$formexclude);
        
        $indexlistLabel = [];
        foreach( $indexlist as  $value ){
        $indexlistLabel[] = ucfirst($value);
        }

        $showlistLabel = [];
        foreach( $showlist as  $value ){
        $showlistLabel[] = ucfirst($value);
        }

        return [
            'panelTypeHtml' => Panel::PANEL_HTML,
            'panelTypeApi' => Panel::PANEL_API,
            'panelType' => $this->panelType,
            'seccion' => $this->seccion,
            'prefixRoute' => $this->ruta,
            'entity' => $this->entity,
            'entityLower' => strtolower($this->entity),
            'entityNamespace' => $this->entityNamespace,
            'formName' => $this->entity . 'Type',
            'formNamespace' => $this->formnamespace,
            'formDirNamespace' => $this->namespacedirform,
            'bundle' => $this->panelbundle,
            'propertysEntity' => $this->getColumnas($this->campos),
            'infoPropertysEntity' => $this->campos,
            'indexlist' => $indexlist,
            'indexlistLabel' => $indexlistLabel,
            'showlist' => $showlist,
            'showlistLabel' => $showlistLabel,
            'controllerNamespace' => $this->namespacedircontroller,
            'prefixRouteController' => $this->prefixRouteController,
            'pathPublicUploads' => $this->pathpublicuploads,
            'serviceautowire'=> $this->serviceautowire,
            'serviceincontroller'=> $this->serviceincontroller,
        ];
    }
    
    public function createController()
    {
        //Obtener la ruta completa de la clase que se creara
        $a = new \ReflectionClass($this->entityNamespace);
        $filenameEntity = $a->getFileName();

        if(file_exists($filenameEntity)){
            //lista de campos excluyendos los campos a ignorar
            $this->campos = $this->fieldsEntity($this->entityNamespace,Util::getArray($this->ignore));

            $params = $this->createOptions();
            $controller = $this->createFileController($this->entity, $params, $this->rutacontroller);
            $form = $this->createForm($this->entity, $params, $this->rutaform);

            $tempdir = Util::fixFilename($this->kernel_project_dir);
            return 'Crud ' .$this->entityNamespace.' '. str_replace($tempdir,'',$controller) . ' ' . str_replace($tempdir,'',$form).' '.PHP_EOL;
        }else{
            return 'Error  ' .$this->entityNamespace.PHP_EOL;
        }
    }

    private function createFileController($entity, $params, $ruta) {
        
        $html = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $params);
        $name = $entity . "Controller.php";

        file_put_contents($ruta .'/'. $name, $html);
        return $name;
    }

    private function createForm($entity, $params, $ruta )
    {
        $html = $this->templating->render('@EasyPanel/Crud/form.html.twig', $params);
        $name = $entity . "Type.php";

        file_put_contents($ruta .'/'. $name, $html);
        return $name;
    }

    public function createDefaultController(){
        $this->createDefault($this->campos, $this->panelbundle, $this->namespaceentity, $this->entity, $this->ruta, $this->seccion, '');
    }

    private function createDefault($campos,
        $panelbundle,
        $entitybundle,
        $entity,
        $ruta,
        $seccion,
        $type_crud){


        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $this->namespacedircontroller = 'App\\Controller\\'.$this->panelbundle;
            $this->namespacedirform = 'App\\Form\\'.$this->panelbundle;
            $this->formnamespace = 'App\\Form\\'.$this->panelbundle.'\\'.$this->entity.'Type';
        }else{
            $this->namespacedircontroller = $this->panelbundle.'\\Controller';
            $this->namespacedirform = $this->panelbundle.'\\Form';
            $this->formnamespace = $this->panelbundle.'\\Form\\'.$this->entity.'Type';
        }
        $parametros = array(
            'seccion' => $seccion,
            'ruta' => $ruta,
            'entity' => $entity,
            'entitybundle' => $entitybundle,
            'form' => $entity . 'Type',
            'bundle' => $panelbundle,
            'indexlist' => '',
            'showlist' => '',
            'namespace' => $this->namespacedircontroller,
            'formnamespace' => $this->formnamespace,
            'prefix_controller_route' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'/'.$this->prefix:'',
            'serviceautowire'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'EasyPanelService $easypanel':'',
            'serviceincontroller'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'$easypanel':'$this->get(EasyPanelService::class)',
        );


        $html = $this->templating->render('@EasyPanel/Crud/controller.default.html.twig', $parametros);
        $name = "DefaultController.php";
        file_put_contents($this->rutacontroller .'/'. $name, $html);


    }

    public function createLoginController(){
        $this->createLogin($this->campos, $this->panelbundle, $this->namespaceentity, $this->entity, $this->ruta, $this->seccion, '');
    }

    private function createLogin($campos,
        $panelbundle,
        $entitybundle,
        $entity,
        $ruta,
        $seccion,
        $type_crud){


        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $this->namespacedircontroller = 'App\\Controller\\'.$this->panelbundle;
            $this->namespacedirform = 'App\\Form\\'.$this->panelbundle;
            $this->namespacedirsecurity = 'App\\Security\\'.$this->panelbundle;
            $this->formnamespace = 'App\\Form\\'.$this->panelbundle.'\\'.$this->entity.'Type';
        }else{
            $this->namespacedircontroller = $this->panelbundle.'\\Controller';
            $this->namespacedirform = $this->panelbundle.'\\Form';
            $this->namespacedirsecurity = $this->panelbundle.'\\Security';
            $this->formnamespace = $this->panelbundle.'\\Form\\'.$this->entity.'Type';
        }

        $parametros = array(
            'seccion' => $seccion,
            'ruta' => $ruta,
            'entity' => $entity,
            'entitybundle' => $entitybundle,
            'form' => $entity . 'Type',
            'bundle' => $panelbundle,
            'indexlist' => '',
            'showlist' => '',
            'namespace' => $this->namespacedircontroller,
            'formnamespace' => $this->formnamespace,
            'prefix_controller_route' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'/'.str_replace('administrador','login',str_replace('/','_',$ruta)):'/login',
            'serviceautowire'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?', EasyPanelService $easypanel':'',
            'serviceincontroller'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'$easypanel':'$this->get(EasyPanelService::class)',
            'authenticationautowire'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'AuthenticationUtils $authenticationUtils':'',
            'authenticationincontroller'=> (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4)?'':'$authenticationUtils = $this->get(\'security.authentication_utils\');'
        );

        $parametros['ruta'] = str_replace('administrador','login',$ruta);
        $html = $this->templating->render('@EasyPanel/Crud/controller.login.html.twig', $parametros);
        $name = "LoginController.php";
        file_put_contents($this->rutacontroller .'/'. $name, $html);

        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $params = [];
            $params['namespaceadmin'] = $entitybundle;
            $params['routelogin'] = $parametros['ruta'].'_index';
            $params['routeredirect'] = $ruta.'_index';
            $params['namespace'] = $this->namespacedirsecurity;

            $html = $this->templating->render('@EasyPanel/Crud/loginform.authenticator.html.twig', $params);
            $name = "EasyPanelLoginFormAuthenticator.php";
            file_put_contents($this->rutasecurity .'/'. $name, $html);
        }

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

    /**
     * @deprecated deprecated since version 2.5
     */
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


}