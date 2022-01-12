<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected function configure()
    {
        $this->setName("setup")
                ->setHelp("setup")
                ->setDescription('used for install AWS CLI and ROOT crontab.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        #install cron
        system("apt -y install cron");
        system("systemctl enable cron");
        system("touch /var/spool/cron/crontabs/root");
        system("chmod 600 /var/spool/cron/crontabs/root");
        #install aws cli
        system("snap install aws-cli --classic");
        system("mkdir ~/.aws");
        system("touch ~/.aws/config");
        system("touch ~/.aws/credentials");
        system("chmod -R 777 ~/.aws");
        #install s3-pit-restore
        system("apt -y install python3-pip");
        system("pip3 install s3-pit-restore");
        return 0;
    }

}