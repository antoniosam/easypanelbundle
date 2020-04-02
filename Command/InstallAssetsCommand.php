<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Command;

use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateInit;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
// Add the Container
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use ZipArchive;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class InstallAssetsCommand extends  Command
{
    protected static $defaultName = 'easypanel:install:assets';

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Exporta Assets predefinidos para el layout')
            ->addOption('tipo',null,InputOption::VALUE_REQUIRED,'Tipo de assets (material, sb-admin)','material')
            ->addOption('dir', null,InputOption::VALUE_REQUIRED, 'Directorio de salida','admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tiempo_inicio = microtime(true);
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $dir = $input->getOption('dir');
        $tipo = $input->getOption('tipo');
        $destino = $this->params->get('kernel.project_dir').DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'assets';




        $output->writeln([
            'Export Assets to EasyPanel',// A line
            '========================================',// Another line
            '',// Empty line
        ]);

        // outputs a message followed by a "\n"

        $zipname = ($tipo=='material')?'zip-material.zip':'zip-sb_admin.zip';
        $assetinternal = $tipo;


        $path = Util::createDir($destino.DIRECTORY_SEPARATOR.$assetinternal);
        $filezip = $path.DIRECTORY_SEPARATOR.$zipname;

        $output->writeln('Tipo Assets: '.$tipo);
        $output->writeln('Destino: '.$path);
        $output->writeln('');

        if(copy (  __DIR__.'/'.$zipname ,  $filezip )){
            $zip = new ZipArchive();
            $res = $zip->open($filezip);
            if ($res === TRUE) {
                $zip->extractTo($path);
                $zip->close();
                unlink($filezip);
                $output->writeln('Assets export ');
            }else{
                $output->writeln('Zip copiado en '.$filezip.' extraer manualmente');
            }
        }else{
            $output->writeln('No se puede copiar el archivo zip');
        }

        // outputs a message without adding a "\n" at the end of the line
        $output->writeln(['','Comando Terminado, '.$this->timecommand($tiempo_inicio).' :)']);
        return 0;
    }

    private function timecommand($tiempo_inicio){
        $tiempo_fin = microtime(true);
        $seconds = round($tiempo_fin - $tiempo_inicio, 0);
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        if($secs == 0){
            $secs = round($tiempo_fin - $tiempo_inicio, 3);
        }

        return ($hours>0? $hours.'h ':'').($mins>0? $mins.'m ':''). $secs.'s';
    }
}