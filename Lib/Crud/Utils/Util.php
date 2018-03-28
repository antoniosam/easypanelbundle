<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */

namespace Ast\EasyPanelBundle\Lib\Crud\Utils;


class Util
{
    public static function createDir($dir){
        $filename = str_replace('//','/',$dir.'/');
        if (!file_exists($filename)) {
            mkdir($filename, 0777, true);
        }
        return $filename;
    }

    public static function guardar($dir,$nombre,$info){
        $path = self::createDir($dir);
        file_put_contents($path . $nombre, $info);
        return $nombre;
    }

    public static function getFileNamespace($namespace){
        $entity = self::fixNamespace($namespace);
        return substr($entity,strripos($entity,'\\')+1);
    }

    public static function fixNamespace($namespace)
    {
        $temp = str_replace('\\', '/', $namespace);
        $temp = str_replace('//', '/', $temp);
        return str_replace('/', '\\', $temp);
    }

    /**
     * @param $ignore
     * @return array
     */
    public static function getArray($value){
        if($value==null || $value ==''){
            return [];
        }elseif (is_array($value)){
            return $value;
        }elseif(strpos($value,',')!==false){
            return explode(',',$value);
        }else{
            return [$value];
        }

    }
}