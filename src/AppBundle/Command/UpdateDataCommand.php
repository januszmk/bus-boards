<?php


namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDataCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName("update:data")
            ->setDescription("Updates bus stops entries");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $busService = $this->getContainer()->get('bus_service');

        $busService->updateEntries();
    }

}