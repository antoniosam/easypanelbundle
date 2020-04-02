<?php
/**
 * Created by marcosamano.
 * Date: 24/03/18
 */
namespace Ast\EasyPanelBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\ORM\EntityManager;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Ast\EasyPanelBundle\Lib\Crud\EasyPanelCreate;

class CreatePanelCommand extends  Command
{
    protected static $defaultName = 'easypanel:create:panel';

    private $twigExtension;
    private $em;
    private $params;

    public function __construct(Environment $twigExtension, EntityManager $entityManager,ParameterBagInterface $params)
    {
        $this->twigExtension = $twigExtension;
        $this->em = $entityManager;
        $this->params = $params;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Crea un panel con las entidades que estan dentro del propio bundle.')
            ->addArgument('proyecto', InputArgument::REQUIRED, 'Nombre del proyecto')
            ->addArgument('directorio_entitys', InputArgument::REQUIRED, 'Carpeta donde se ubican las entidades des src')
            ->addArgument('tipo_panel',InputOption::VALUE_REQUIRED,'Tipo de panel (html, api)','html')
            ->addOption('prefix',null,InputOption::VALUE_REQUIRED,'Prefijo para las rutas')
            ->addOption('folder', null,InputOption::VALUE_REQUIRED, 'Carpeta donde se generan los archivos dentro de la estructura de Symfony')
            ->addOption('clase_login', null,InputOption::VALUE_REQUIRED, 'Clase que se usara para el login',null)
            ->addOption('ignorar_entitys',null,InputOption::VALUE_REQUIRED,'Entidades que se ignorarn para la creacion del panel','')
            ->addOption('ignorar_campos',null,InputOption::VALUE_REQUIRED,'Campos que se ignorarn al crear los archivos','')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tiempo_inicio = microtime(true);
        
        $proyecto = $input->getArgument('proyecto');
        $directorio_entitys = $input->getArgument('directorio_entitys');
        $tipo_panel = $input->getArgument('tipo_panel');

        $folder = $input->getOption('folder');
        $prefix = $input->getOption('prefix');
        $ignore = $input->getOption('ignorar_entitys');
        $exclude = $input->getOption('ignorar_campos');

        $rootDir = $this->params->get('kernel.project_dir').DIRECTORY_SEPARATOR.'src';
        $folder = ucfirst($folder);
        
        $output->writeln([
            'Create EasyPanel',
            '========================================',
            '',
        ]);

        $output->writeln('Proyecto: '.$proyecto);
        $output->writeln('Directorio Entidades: '.$directorio_entitys);
        $output->writeln('Tipo Panel : '.$tipo_panel);
        $output->writeln('SubFolder: '.$folder);
        $output->writeln('Prefix: '.$prefix);
        $output->writeln('Exluir Entidades: '.$ignore);
        $output->writeln('Ignorar Campos: '.$exclude);
        $output->writeln('');



        $panel = new EasyPanelCreate($this->em, $this->twigExtension, $rootDir, $tipo_panel, $proyecto, $directorio_entitys, $prefix , $folder, $exclude);
        if($input->getOption('clase_login')){
            $panel->setClaseLogin($input->getOption('clase_login'));
        }
        $resultado = $panel->create($ignore);
        $output->writeln('Resultado:');
        $output->writeln($resultado);

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