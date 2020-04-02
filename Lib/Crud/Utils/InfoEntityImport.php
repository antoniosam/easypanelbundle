<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud\Utils;

use \Doctrine\ORM\EntityManager;
class InfoEntityImport
{

    public static function folder(EntityManager $em,$directory)
    {

        $listaposibles = \Ast\EasyPanelBundle\Lib\Crud\Utils\ClassMapGenerator::createMap($directory);

        $listaclases = [];
        foreach ($listaposibles as $class => $clase):
            if (!$em->getMetadataFactory()->isTransient($class)) {
                $listaclases[] = $class;
            }
        endforeach;
        return $listaclases;
    }
    /**
     * @param $em
     * @param $entitybundle
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function fieldsEntity(EntityManager $em, $entitybundle){
        $meta = $em->getClassMetadata($entitybundle);
        $columnas = [];
        foreach ($meta->getColumnNames() as $name):
            $columnas[] = $meta->getFieldMapping($name);
        endforeach;

        return $columnas;
    }
}