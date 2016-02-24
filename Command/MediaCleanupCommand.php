<?php

namespace Alpixel\Bundle\MediaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @CronJob("PT1D")
 */
class MediaCleanupCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('assetic:yoloqsdq')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Your code here
    }
}
