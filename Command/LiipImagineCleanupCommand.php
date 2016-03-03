<?php

namespace Alpixel\Bundle\MediaBundle\Command;

use Alpixel\Bundle\CronBundle\Annotation\CronJob;
use Liip\ImagineBundle\Command\RemoveCacheCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @CronJob("P7D")
 */
class LiipImagineCleanupCommand extends RemoveCacheCommand
{
    public function configure()
    {
        parent::configure();
        $this->setName('alpixel:media:liip_cleanup')
             ->setDescription('Cleaning liip cache folders');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }
}
