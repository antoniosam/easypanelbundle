<?php

/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud;


class EasyPanelCreateAuto extends EasyPanelCreate
{
    function __construct(\Doctrine\ORM\EntityManager $entityManager, \Twig_Environment $templating, $kernel_project_dir, $proyecto, $panelbundle,  $prefix,$exclude)
    {
        parent::__construct($entityManager, $templating, $kernel_project_dir, $proyecto, $panelbundle, $panelbundle, $prefix,$exclude);
    }

}