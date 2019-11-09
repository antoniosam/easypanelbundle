<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Command;

use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateInit;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
// Add the Container
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CreateModuleCommand extends  ContainerAwareCommand
{
    protected static $defaultName = 'easypanel:create:module';

    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Crea un controlador y un form basandose en un entidad ')
            ->addArgument('entity', InputArgument::REQUIRED, 'Namespace de la Entidad que se usara')
            ->addArgument('proyecto', InputArgument::REQUIRED, 'Nombre del proyecto')
            ->addArgument('directorio_bundle', InputArgument::REQUIRED, 'Carpeta o Bundle donde se creara el panel')
            ->addArgument('directorio_entitys', InputArgument::REQUIRED, 'Carpeta donde se ubican las entidades')
            ->addArgument('tipo_panel',null,InputOption::VALUE_REQUIRED,'Tipo de panel (html, api)','html')
            ->addOption('prefix',null,InputOption::VALUE_REQUIRED,'Prefijo para las rutas','admin')
            ->addOption('ignore',null,InputOption::VALUE_REQUIRED,'Campos que se ignorarn al crear los archivos','')
            ->addOption('exclude',null,InputOption::VALUE_REQUIRED,'Entidades que se ignorarn para la creacion del panel','')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tiempo_inicio = microtime(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $twig = $this->getContainer()->get('twig');
        
        $entity = $input->getArgument('entity');
        $proyecto = $input->getArgument('proyecto');
        $carpetaobundle = $input->getArgument('directorio_bundle');
        $directorio_entitys = $input->getArgument('directorio_entitys');
        $tipo_panel = $input->getArgument('tipo_panel');
        
        $prefix = $input->getOption('prefix');
        $ignore = $input->getOption('ignore');
        $exclude = $input->getOption('exclude');

        $dir = $this->getKernelDir();
        $carpetaobundle = $this->fixCarpetaoBundle($carpetaobundle);

        $output->writeln([
            'Create EasyPanel Module',
            '========================================',
            '',
        ]);

        $output->writeln('Entity: '.$entity);
        $output->writeln('Proyecto: '.$proyecto);
        $output->writeln('Directorio o Bundle de destino: '.$carpetaobundle);
        $output->writeln('Directorio Entidades: '.$directorio_entitys);
        $output->writeln('Tipo Panel : '.$tipo_panel);
        $output->writeln('Prefix: '.$prefix);
        $output->writeln('Exluir Entidades: '.$exclude);
        $output->writeln('Ignorar Campos: '.$ignore);
        $output->writeln('');

        $crud = new EasyPanelController($em, $twig, $dir, $tipo_panel, $carpetaobundle, $directorio_entitys, $prefix, $prefix.'_'.strtolower($entity), ucfirst($entity) ,$ignore);
        $resultado = $crud->createController();
        $output->writeln('Resultado:'.$resultado);


        // outputs a message without adding a "\n" at the end of the line
        $output->writeln(['','Comando Terminado, '.$this->timecommand($tiempo_inicio).' :)']);
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

    private function getKernelDir(){
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $dir = $this->getContainer()->getParameter("kernel.root_dir").'/';
        }else{
            $dir = $this->getContainer()->getParameter("kernel.root_dir").'/../src/';
        }
        return $dir;
    }

    private function fixCarpetaoBundle($carpetaobundle){
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $carpetaobundle = ucfirst($carpetaobundle);
        }else{
            Util::createDir($dir.$carpetaobundle);
        }
        return $carpetaobundle;
    }
}