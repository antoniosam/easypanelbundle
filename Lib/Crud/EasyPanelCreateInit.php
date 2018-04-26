<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;

use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;

class EasyPanelCreateInit extends EasyPanelCreate
{
    function __construct(\Doctrine\ORM\EntityManager $entityManager, \Twig_Environment $templating, $kernel_project_dir, $proyecto, $panelbundle,  $prefix)
    {
        parent::__construct($entityManager, $templating, $kernel_project_dir, $proyecto, $panelbundle, $panelbundle. '/Entity/', $prefix,null);
        $parametros = array('bundle' => $this->panelbundle);
        $ruta = $this->kernel_project_dir . $this->panelbundle . '/Entity/';
        $html = $this->templating->render('@EasyPanel/Create/Entitys/Config.php.twig', $parametros);
        Util::guardar($ruta,"Config.php",$html);
        $html = $this->templating->render('@EasyPanel/Create/Entitys/Contacto.php.twig', $parametros);
        Util::guardar($ruta,"Contacto.php",$html);
        $html = $this->templating->render('@EasyPanel/Create/Entitys/Foto.php.twig', $parametros);
        Util::guardar($ruta,"Foto.php",$html);
        $html = $this->templating->render('@EasyPanel/Create/Entitys/Pagina.php.twig', $parametros);
        Util::guardar($ruta,"Pagina.php",$html);
        $html = $this->templating->render('@EasyPanel/Create/Entitys/Producto.php.twig', $parametros);
        Util::guardar($ruta,"Producto.php",$html);
    }



}