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

class EasyPanelController
{
    private $em;
    private $templating;
    private $kernel_project_dir;
    private $panelType;
    private $folder;
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
    private $serviceAutowire;
    private $serviceInController;

    private $namespacedircontroller;
    private $namespacedirform;
    private $namespacedirsecurity;
    private $formnamespace;
    private $pathpublicuploads;

    const DR = DIRECTORY_SEPARATOR;


    public function __construct(
        EntityManager $entityManager,
        Environment $templating,
        $kernel_project_dir,
        $panelType,
        $route,
        $folder
    )
    {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->kernel_project_dir = $kernel_project_dir;
        $this->panelType = $panelType;
        $this->route = $route;
        $this->folder = $folder;

        $this->pathpublicuploads = '/../public/uploads';
        $this->serviceAutowire = ', EasyPanelService $easypanel';
        $this->serviceInController = '$easypanel';

    }


    public function createOptions($namespaceEntity,$campos)
    {
        $entity = Util::getFileNamespace($namespaceEntity);
        $seccion = $entity;

        $indexexclude = ['creado', 'actualizado', 'contenido', 'descripcion', 'slug', 'folio', 'pass', 'salt', 'master', 'rol'];
        $indexlist = $this->getColumnas($this->ignoreFields($campos, $indexexclude));

        $showexclude = ['creado', 'actualizado', 'slug', 'salt'];
        $showlist = $this->getColumnas($this->ignoreFields($campos, $showexclude));

        $formexclude = ['id', 'creado', 'actualizado', 'slug'];
        $formlist = $this->ignoreFields($campos, $formexclude);

        $indexlistLabel = [];
        foreach ($indexlist as $value) {
            $indexlistLabel[] = ucfirst($value);
        }

        $showlistLabel = [];
        foreach ($showlist as $value) {
            $showlistLabel[] = ucfirst($value);
        }

        return [
            'panelTypeHtml' => Panel::PANEL_HTML,
            'panelTypeApi' => Panel::PANEL_API,
            'panelType' => $this->panelType,
            'seccion' => $seccion,
            'prefix_route_controller' => ($this->route ? '/'.$this->route.'/'.strtolower($entity) : '/'.strtolower($entity)),
            'prefix_route_name' => ($this->route ? $this->route.'_'.strtolower($entity) : strtolower($entity)),
            'entity' => $entity,
            'entityLower' => strtolower($entity),
            'entityNamespace' => $namespaceEntity,
            'controllerNamespaceDir' => 'App\\Controller'.($this->folder?'\\'.$this->folder:'' ),
            'repoNamespaceDir' => 'App\\Repository'.($this->folder?'\\'.$this->folder:'' ),
            'repoName' => $entity . 'Repository',
            'repoNamespace' => 'App\\Repository\\'.($this->folder?$this->folder.'\\':'' ).$entity . 'Repository',
            'formNamespaceDir' => 'App\\Form'.($this->folder?'\\'.$this->folder:'' ),
            'formName' => $entity . 'Type',
            'formNamespace' => 'App\\Form\\'.($this->folder?$this->folder.'\\':'' ).$entity . 'Type',
            'propertysEntity' => $this->getColumnas($campos),
            'infoPropertysEntity' => $campos,
            'indexlist' => $indexlist,
            'indexlistLabel' => $indexlistLabel,
            'showlist' => $showlist,
            'showlistLabel' => $showlistLabel,
            'formlist' => $formlist,
            'pathPublicUploads' => $this->pathpublicuploads,
            'serviceautowire' => $this->serviceAutowire,
            'serviceincontroller' => $this->serviceInController,
        ];
    }


    public function createDefaultController()
    {
        $namespace = 'App\\Controller' . ($this->folder ? '\\' . $this->folder : '');
        $path = '/' . ($this->route ? $this->route : '');
        $routeName = ($this->route ? $this->route.'_' : '');

        $parametros = array(
            'prefix_route_controller' => $path,
            'prefix_route_name' => $routeName,
            'namespace' => $namespace,
            'serviceautowire' => str_replace(',','',$this->serviceAutowire),
            'serviceincontroller' => str_replace(',','',$this->serviceInController),
        );

        $html = $this->templating->render('@EasyPanel/Crud/controller.default.html.twig', $parametros);
        $fileName = $this->kernel_project_dir . self::DR . 'Controller' .self::DR . ($this->folder ?  $this->folder . self::DR : '') . 'DefaultController.php';
        file_put_contents($fileName, $html);
        return $fileName;
    }

    public function createController($namespaceEntity)
    {

        //Obtener la ruta completa de la clase que se creara
        $a = new \ReflectionClass($namespaceEntity);
        $filenameEntity = $a->getFileName();

        if (file_exists($filenameEntity)) {
            //lista de campos excluyendos los campos a ignorar
            $campos = $this->fieldsEntity($namespaceEntity, Util::getArray($this->ignore));

            $params = $this->createOptions($namespaceEntity, $campos);

            $entity = Util::getFileNamespace($namespaceEntity);

            $data = $this->templating->render('@EasyPanel/Crud/controller.easy.html.twig', $params);
            $dirName = $this->kernel_project_dir . self::DR . 'Controller' .self::DR . ($this->folder ?  $this->folder . self::DR : '') ;
            if(!file_exists($dirName)){
                mkdir($dirName,0777,true);
            }
            file_put_contents($dirName. $entity.'Controller.php', $data);

            $data = $this->templating->render('@EasyPanel/Crud/repository.easy.html.twig', $params);
            $dirName = $this->kernel_project_dir . self::DR . 'Repository' .self::DR . ($this->folder ?  $this->folder . self::DR : '') ;
            if(!file_exists($dirName)){
                mkdir($dirName,0777,true);
            }
            file_put_contents($dirName. $entity.'Repository.php', $data);

            $dataForm = $this->templating->render('@EasyPanel/Crud/form.html.twig', $params);
            $dirNameForm = $this->kernel_project_dir . self::DR . 'Form' .self::DR . ($this->folder ?  $this->folder . self::DR : '') ;
            if(!file_exists($dirNameForm)){
                mkdir($dirNameForm,0777,true);
            }
            file_put_contents($dirNameForm. $entity.'Type.php', $dataForm);

            return 'Crud ' . $namespaceEntity . ' ' . $entity.'Controller.php' . ' ' . $entity.'Type.php' . ' ' . PHP_EOL;
        } else {
            return 'Error  ' . $namespaceEntity . PHP_EOL;

        }
    }



    public function createLoginController($namespaceEntity,$routeRedirectSuccess = 'home_index')
    {
        //lista de campos excluyendos los campos a ignorar
        $campos = $this->fieldsEntity($namespaceEntity, Util::getArray($this->ignore));

        $params = $this->createOptions($namespaceEntity, $campos);
        $params['prefix_route_controller'] = ($this->route ? '/'.$this->route.'/login' : '/login');
        $params['prefix_route_name']= ($this->route ? $this->route.'_login' : 'login');

        $entity = Util::getFileNamespace($namespaceEntity);

        $data = $this->templating->render('@EasyPanel/Crud/controller.login.html.twig', $params);
        $dirName = $this->kernel_project_dir . self::DR . 'Controller' .self::DR . ($this->folder ?  $this->folder . self::DR : '') ;
        if(!file_exists($dirName)){
            mkdir($dirName,0777,true);
        }
        file_put_contents($dirName. 'LoginController.php', $data);

        $params = [];
        $params['namespaceadmin'] = $namespaceEntity;
        $params['routelogin'] = ($this->route ? $this->route.'_'.strtolower($entity) : strtolower($entity)) . '_index';
        $params['routeredirect'] = $routeRedirectSuccess;
        $params['namespaceAuthenticatorDir'] = 'App\\Security'.($this->folder?'\\'.$this->folder:'' );
        $params['entity'] = $entity;

        $html = $this->templating->render('@EasyPanel/Crud/loginform.authenticator.html.twig', $params);
        $dirName = $this->kernel_project_dir . self::DR . 'Security' .self::DR . ($this->folder ?  $this->folder . self::DR : '') ;
        if(!file_exists($dirName)){
            mkdir($dirName,0777,true);
        }
        file_put_contents($dirName. 'EasyPanelLoginFormAuthenticator.php', $html);
    }

    private function createLogin($campos,
                                 $panelbundle,
                                 $entitybundle,
                                 $entity,
                                 $ruta,
                                 $seccion,
                                 $type_crud)
    {


        if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) {
            $this->namespacedircontroller = 'App\\Controller\\' . $this->panelbundle;
            $this->namespacedirform = 'App\\Form\\' . $this->panelbundle;
            $this->namespacedirsecurity = 'App\\Security\\' . $this->panelbundle;
            $this->formnamespace = 'App\\Form\\' . $this->panelbundle . '\\' . $this->entity . 'Type';
        } else {
            $this->namespacedircontroller = $this->panelbundle . '\\Controller';
            $this->namespacedirform = $this->panelbundle . '\\Form';
            $this->namespacedirsecurity = $this->panelbundle . '\\Security';
            $this->formnamespace = $this->panelbundle . '\\Form\\' . $this->entity . 'Type';
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
            'prefix_controller_route' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) ? '/' . str_replace('administrador', 'login', str_replace('/', '_', $ruta)) : '/login',
            'serviceautowire' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) ? ', EasyPanelService $easypanel' : '',
            'serviceincontroller' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) ? '$easypanel' : '$this->get(EasyPanelService::class)',
            'authenticationautowire' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) ? 'AuthenticationUtils $authenticationUtils' : '',
            'authenticationincontroller' => (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) ? '' : '$authenticationUtils = $this->get(\'security.authentication_utils\');'
        );

        $parametros['ruta'] = str_replace('administrador', 'login', $ruta);
        $html = $this->templating->render('@EasyPanel/Crud/controller.login.html.twig', $parametros);
        $name = "LoginController.php";
        file_put_contents($this->rutacontroller . '/' . $name, $html);

        if (\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4) {
            $params = [];
            $params['namespaceadmin'] = $entitybundle;
            $params['routelogin'] = $parametros['ruta'] . '_index';
            $params['routeredirect'] = $ruta . '_index';
            $params['namespace'] = $this->namespacedirsecurity;

            $html = $this->templating->render('@EasyPanel/Crud/loginform.authenticator.html.twig', $params);
            $name = "EasyPanelLoginFormAuthenticator.php";
            file_put_contents($this->rutasecurity . '/' . $name, $html);
        }

    }

    private function fieldsEntity($entitybundle, array $excluir = [])
    {
        $columnas = InfoEntityImport::fieldsEntity($this->em, $entitybundle);
        return (count($excluir) > 0) ? $this->ignoreFields($columnas, $excluir) : $columnas;
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
    protected function createBundleEntity($entity, $panelbunle)
    {
        $temp = str_replace("\\", "/", $entity);
        if (strpos($temp, "/") !== false) {
            return $entity;
        } elseif (strpos($temp, "\\") !== false) {
            return $entity;
        } else {
            return $panelbunle . "\\Entity\\" . $entity;
        }
    }


}