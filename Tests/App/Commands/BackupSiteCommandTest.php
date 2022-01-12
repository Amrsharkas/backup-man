<?php

namespace Tests\App\Commands;

use Console\App\Commands\BackupSiteCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BackupSiteCommandTest extends TestCase
{
    public function testExecute(){
        $application = new Application();
        $application->add(new BackupSiteCommand());

        $command = $application->find("site");
        $commandTester = new CommandTester($command);
        $command->execute([
            'site_id'=>1,
            'site_root'=>"/var/www/testProject",
            'profile'=>"aws_backup_user",
            'bucket_name'=>"sprint-erp-test"
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains("asdf",$output);
    }

}
