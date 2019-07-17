<?php

namespace ExtranetBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Numerologie;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PersonalYearCommand extends ContainerAwareCommand
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var Numerologie
     */
    private $numerologie;

    public function __construct(ManagerRegistry $registry, Numerologie $numerologie)
    {
        $this->registry = $registry;

        parent::__construct();
        $this->numerologie = $numerologie;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('extranet:update:personal_year')
            ->setDescription('Update all personal year values when year changes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Updating all analysis\' personal year',
            '============',
            '',
        ]);
        $all = $this->registry->getRepository(Analysis::class)->findAll();

        /** @var Analysis $one */
        foreach ($all as $one) {
            $data = $one->getData();
            $data['lifePath']['personalYearNowNumber'] = $this->numerologie->getPersonnalYearNowNumber($one);

            $output->write($one->__toString() . ' is going from ' . $one->getData()['lifePath']['personalYearNowNumber'] . ' to ' . $data['lifePath']['personalYearNowNumber']);

            $one->setData($data);
            $this->registry->getManager()->persist($one);
            $this->registry->getManager()->flush();

            $output->writeln('');
        }
    }
}
