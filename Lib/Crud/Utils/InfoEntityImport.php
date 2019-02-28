<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud\Utils;


use Ast\EasyPanelBundle\Lib\Crud\Utils\ClassMapGenerator;

class InfoEntityImport
{

    public static function folder(\Doctrine\ORM\EntityManager $em,$directory)
    {
        $listaposibles = ClassMapGenerator::createMap($directory);

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
    public static function fieldsEntity(\Doctrine\ORM\EntityManager $em, $entitybundle){
        $meta = $em->getClassMetadata($entitybundle);
        $columnas = [];
        foreach ($meta->getColumnNames() as $name):
            $columnas[] = $meta->getFieldMapping($name);
        endforeach;

        return $columnas;
    }
}