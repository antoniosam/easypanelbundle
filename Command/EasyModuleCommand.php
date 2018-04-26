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

class EasyModuleCommand extends  ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('easypanel:create:module')
            ->setDescription('Crea un controlador y un form basandose en un entidad ')
            ->addArgument('seccion', InputArgument::REQUIRED, 'Seccion del Modulo')
            ->addArgument('panelbundle', InputArgument::REQUIRED, 'Bundle donde estara el panel')
            ->addArgument('entity', InputArgument::REQUIRED, 'Namespace de la Entidad que se usara')
            ->addArgument('prefix', InputArgument::REQUIRED, 'Prefijo para las rutas')
            ->addOption('type_crud',null,InputOption::VALUE_REQUIRED,'Typo de controller(easy,min)','easy')
            ->addOption('ignore',null,InputOption::VALUE_REQUIRED,'Campos que se ignorarn al crear los archivos','')



        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)

        $em = $this->getContainer()->get('doctrine')->getManager();
        $twig = $this->getContainer()->get('twig');
        $dir = $this->getContainer()->getParameter("kernel.root_dir").'/../src/';
        $seccion = $input->getArgument('seccion');
        $panelbundle = $input->getArgument('panelbundle');
        $entity = $input->getArgument('entity');
        $prefix = $input->getArgument('prefix');
        if($input->getOption('type_crud') == 'easy'){
            $type_crud = EasyPanelCreate::TYPE_EASY;
        }elseif($input->getOption('type_crud') == 'min'){
            $type_crud = EasyPanelCreate::TYPE_EASY_MIN;
        }elseif($input->getOption('type_crud') == 'normal'){
            $type_crud = EasyPanelCreate::TYPE_NORMAL;
        }else{
            $type_crud = EasyPanelCreate::TYPE_EASY;
        }
        $ignore = $input->getOption('ignore');

        $output->writeln([
            'Create EasyPanel Type Sato  ',// A line
            '========================================',// Another line
            '',// Empty line
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Seccion: '.$seccion);
        $output->writeln('PanelBundle: '.$panelbundle);
        $output->writeln('Namespace Entity: '.$entity);
        $output->writeln('Prefix: '.$prefix);
        $output->writeln('Tipo Crud: '.$type_crud);
        $output->writeln('Ignorar: '.$ignore);
        $output->writeln('');

        $panel = new EasyPanelController($em,$twig,$dir,$panelbundle,$entity,$prefix,$seccion);
        $resultado = $panel->create($type_crud,$ignore);
        $output->writeln('Resultado:'.$resultado);


        // outputs a message without adding a "\n" at the end of the line
        $output->writeln(['','Comando Terminado, :)']);
    }
}