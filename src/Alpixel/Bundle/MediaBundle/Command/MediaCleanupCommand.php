<?php

namespace Alpixel\Bundle\MediaBundle\Command;

/**
 * @CronJob("PT1D")
 */
class MediaCleanupCommande extends Command
{
    public function configure()
    {
        // Must have a name configured
        // ...
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Your code here
    }
}
