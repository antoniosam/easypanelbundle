<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Command;

use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateAuto;
use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreateSato;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
// Add the Container
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class EasyPanelAutoCommand extends  ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('easypanel:create:auto')
            ->setDescription('Crea un panel con las entidades que estan dentro del propio bundle.')
            ->addArgument('proyecto', InputArgument::REQUIRED, 'Nombre del proyecto')
            ->addArgument('panelbundle', InputArgument::REQUIRED, 'Bundle donde estara el panel')
            ->addArgument('prefix', InputArgument::REQUIRED, 'Prefijo para las rutas')
            ->addOption('type_crud',null,InputOption::VALUE_REQUIRED,'Typo de controller(easy,min,normal)','easy')
            ->addOption('menu_collapse',null,InputOption::VALUE_REQUIRED,'Menu collapsado (1,0)','1')
            ->addOption('ignore',null,InputOption::VALUE_REQUIRED,'Campos que se ignorarn al crear los archivos','')
            ->addOption('exclude',null,InputOption::VALUE_REQUIRED,'Entidades que se ignorarn para la creacion del panel','')


        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)

        $em = $this->getContainer()->get('doctrine')->getManager();
        $twig = $this->getContainer()->get('twig');
        $dir = $this->getContainer()->getParameter("kernel.root_dir").'/../src/';
        $proyecto = $input->getArgument('proyecto');
        $panelbundle = $input->getArgument('panelbundle');
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
        if($input->getOption('menu_collapse')=='1'){
            $menu= EasyPanelCreate::MENU_COLLAPSE;
        }elseif($input->getOption('menu_collapse')=='0'){
            $menu= EasyPanelCreate::MENU_EXPAND;
        }else{
            $menu= EasyPanelCreate::MENU_COLLAPSE;
        }
        $ignore = $input->getOption('ignore');
        $exclude = $input->getOption('exclude');
        $output->writeln([
            'Create EasyPanel Auto  ',// A line
            '========================================',// Another line
            '',// Empty line
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Proyecto: '.$proyecto);
        $output->writeln('PanelBundle: '.$panelbundle);
        $output->writeln('Prefix: '.$prefix);
        $output->writeln('Tipo Crud: '.$type_crud);
        $output->writeln('Tipo Menu: '.$menu);
        $output->writeln('Exluir Entidades: '.$exclude);
        $output->writeln('Ignorar Campos: '.$ignore);
        $output->writeln('');

        $panel = new EasyPanelCreateAuto($em,$twig,$dir,$proyecto,$panelbundle,$prefix,$exclude);
        $resultado = $panel->create($menu,$type_crud,$ignore);
        $output->writeln('Resultado:'.$resultado);

        // outputs a message without adding a "\n" at the end of the line
        $output->writeln(['','Comando Terminado, :)']);
    }
}