<?php

namespace Alpixel\Bundle\MediaBundle\Command;

use Alpixel\Bundle\CronBundle\Annotation\CronJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @CronJob("P1D")
 */
class MediaCleanupCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('alpixel:media:cleanup')
             ->setDescription('Cleaning unused media');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $container->get('doctrine.orm.entity_manager')
                  ->getRepository('AlpixelMediaBundle:Media')
                  ->findExpiredMedias();

        $medias = $container->get('alpixel_media.manager')->cleanup();
        $output->writeln(sprintf('%s medias deleted', $medias));
    }
}