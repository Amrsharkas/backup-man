<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class RestoreSiteCommand extends Command
{
    protected function configure()
    {
        $this->setName("restore-site")
                ->setHelp("restore-site site_id at bucket_name profile
                                    site_id=if you have many sites
                                    bucket_name=aws bucket which used for backup
                                    at=versioning time
                                    profile=aws user profile")
                ->setDescription('used for restore sites')
                ->addArgument("site_id",InputArgument::REQUIRED,"write your site id")
                ->addArgument("at",InputArgument::REQUIRED,"write versioning time")
                ->addArgument("bucket_name",InputArgument::REQUIRED,"write your aws bucket name")
                ->addArgument("profile",InputArgument::REQUIRED,"write your aws profile");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $site_id=$input->getArgument("site_id");
        $sites_storage_local_path_root="/restored_sites";
        $restore_dir_local_path = $sites_storage_local_path_root."/".$site_id;
        $bucket_name = $input->getArgument("bucket_name");
        $at=$input->getArgument("at");
        $aws_config_file_profile = $input->getArgument("profile");
        $restore_logs_directory = "/restore_logs";
        $restore_logs_file = $restore_logs_directory."/restore_log_site_".$site_id."_".date("Y_m_d__H_i_s").".txt";
        ##################################################################################################################
        $output->writeln('<comment>Create logs directory "'.$restore_logs_directory.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($restore_logs_directory) ){
            system("mkdir ".$restore_logs_directory);
            system("chmod 777 -R ".$restore_logs_directory);
        }
        ##################################################################################################################
        $output->writeln('<comment>Create staging directory "'.$sites_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($sites_storage_local_path_root) ){
            system("mkdir ".$sites_storage_local_path_root." >> ".$restore_logs_file);
            system("chmod 777 -R ".$sites_storage_local_path_root." >> ".$restore_logs_file);
        }
        if(is_dir($restore_dir_local_path) ){
            system("rm -rf ".$restore_dir_local_path." >> ".$restore_logs_file);
        }
        ##################################################################################################################
        $output->writeln('<comment>restore site from S3 "'.$sites_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(is_dir($sites_storage_local_path_root."/sites_backup/".$site_id) ){
            system("rm -rf ".$sites_storage_local_path_root."/sites_backup/".$site_id." >> ".$restore_logs_file);
        }
        system("/usr/local/bin/s3-pit-restore -b ".$bucket_name." -d ".$sites_storage_local_path_root." -p sites_backup/".$site_id." -t '".$at."' --max-workers 100 >> ".$restore_logs_file);
        ##################################################################################################################
        $output->writeln('<comment>Site restored locally...'.date("Y/m/d H:i:s").'</comment>');
        ##################################################################################################################

        return 0;
    }
}
