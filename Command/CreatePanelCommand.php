<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Command;

use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateAuto;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateInit;
use Ast\EasyPanelBundle\Lib\Crud\Utils\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
// Add the Container
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CreatePanelCommand extends  ContainerAwareCommand
{
    protected static $defaultName = 'easypanel:create:panel';

    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Crea un panel con las entidades que estan dentro del propio bundle.')
            ->addArgument('proyecto', InputArgument::REQUIRED, 'Nombre del proyecto')
            ->addArgument('directorio_bundle', InputArgument::REQUIRED, 'Carpeta o Bundle donde se creara el panel')
            ->addArgument('directorio_entitys', InputArgument::REQUIRED, 'Carpeta donde se ubican las entidades')
            ->addArgument('prefix', InputArgument::REQUIRED, 'Prefijo para las rutas')
            //->addOption('type_crud',null,InputOption::VALUE_REQUIRED,'Typo de controller(easy,min,normal)','easy')
            //->addOption('menu_collapse',null,InputOption::VALUE_REQUIRED,'Menu collapsado (1,0)','1')
            ->addOption('ignore',null,InputOption::VALUE_REQUIRED,'Campos que se ignorarn al crear los archivos','')
            ->addOption('exclude',null,InputOption::VALUE_REQUIRED,'Entidades que se ignorarn para la creacion del panel','')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tiempo_inicio = microtime(true);
        // outputs multiple lines to the console (adding "\n" at the end of each line)

        $em = $this->getContainer()->get('doctrine')->getManager();
        $twig = $this->getContainer()->get('twig');
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $dir = $this->getContainer()->getParameter("kernel.root_dir").'/';
        }else{
            $dir = $this->getContainer()->getParameter("kernel.root_dir").'/../src/';
        }
        $proyecto = $input->getArgument('proyecto');
        $carpetaobundle = $input->getArgument('directorio_bundle');
        $directorio_entitys = $input->getArgument('directorio_entitys');
        $prefix = $input->getArgument('prefix');
        if(\Symfony\Component\HttpKernel\Kernel::MAJOR_VERSION == 4){
            $carpetaobundle = ucfirst($carpetaobundle);
        }

        //$type_crud = EasyPanelCreate::TYPE_EASY;
        //$menu= EasyPanelCreate::MENU_EXPAND;

        $ignore = $input->getOption('ignore');
        $exclude = $input->getOption('exclude');
        $output->writeln([
            'Create EasyPanel  ',// A line
            '========================================',// Another line
            '',// Empty line
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Proyecto: '.$proyecto);
        $output->writeln('Directorio o Bundle de destino: '.$carpetaobundle);
        $output->writeln('Directorio Entidades: '.$directorio_entitys);
        $output->writeln('Prefix: '.$prefix);
        //$output->writeln('Tipo Crud: '.$type_crud);
        //$output->writeln('Tipo Menu: '.$menu);
        $output->writeln('Exluir Entidades: '.$exclude);
        $output->writeln('Ignorar Campos: '.$ignore);
        $output->writeln('');

        Util::createDir($dir.$carpetaobundle);

        $panel = new EasyPanelCreate($em,$twig,$dir,$proyecto,$carpetaobundle,$directorio_entitys,$prefix,$exclude);
        $resultado = $panel->create($ignore);
        $output->writeln('Resultado:');
        $output->writeln($resultado);

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
}