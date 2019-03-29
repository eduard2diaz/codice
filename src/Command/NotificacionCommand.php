<?php

namespace App\Command;

use App\Entity\Autor;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificacionCommand extends Command
{
    protected static $defaultName = 'notificacion:delete-old';

    protected $manager;

    /**
     * NotificacionCommand constructor.
     * @param $manager
     */
    public function __construct(ObjectManager $manager = null)
    {
        $this->manager = $manager;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Elimina las notificaciones con 2 o más días antes del último login')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $db = $this->manager->getConnection();
        $query = 'Delete from notificacion n where n.id in (
              SELECT n.id from notificacion as n join autor as d on(n.destinatario=d.id) where DATE(d.ultimo_login) > DATE(n.fecha)
        )';
        $stmt = $db->prepare($query);
        $stmt->execute();

        $io->success('La consulta finalizó exitosamente');
    }
}
