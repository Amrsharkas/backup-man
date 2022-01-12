<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class BackupSiteCommand extends Command
{
    protected function configure()
    {
        $this->setName("backup-site")
                ->setHelp("backup-site site_id site_root bucket_name profile
                                    site_id=if you have many sites
                                    site_root=site path
                                    profile=aws user profile
                                    bucket_name=aws bucket which used for backup")
                ->setDescription('used for backup sites')
                ->addArgument("site_id",InputArgument::REQUIRED,"write your site id")
                ->addArgument("site_root",InputArgument::REQUIRED,"write site path")
                ->addArgument("bucket_name",InputArgument::REQUIRED,"write your aws bucket name")
                ->addArgument("profile",InputArgument::REQUIRED,"write your aws profile");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $site_id=$input->getArgument("site_id");
        $sites_storage_local_path_root="/backup_sites";
        $site_root = $input->getArgument("site_root");
        $backup_dir_local_path = $sites_storage_local_path_root."/".$site_id;
        $aws_config_file_profile = $input->getArgument("profile");
        $bucket_name = $input->getArgument("bucket_name");
        $backup_logs_directory = "/backup_logs";
        $backup_logs_file = $backup_logs_directory."/backup_log_site_".$site_id."_".date("Y_m_d__H_i_s").".txt";
        ##################################################################################################################
        $output->writeln('<comment>Create logs directory "'.$backup_logs_directory.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($backup_logs_directory) ){
            system("mkdir ".$backup_logs_directory);
            system("chmod 777 -R ".$backup_logs_directory);
        }
        ##################################################################################################################
        $output->writeln('<comment>Create staging directories "'.$sites_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($sites_storage_local_path_root) ){
            system("mkdir ".$sites_storage_local_path_root." >> ".$backup_logs_file);
            system("chmod 777 -R ".$sites_storage_local_path_root." >> ".$backup_logs_file);
        }
        if(! is_dir($backup_dir_local_path) ){
            system("mkdir ".$backup_dir_local_path." >> ".$backup_logs_file);
            system("chmod 777 -R ".$sites_storage_local_path_root." >> ".$backup_logs_file);
        }
        ##################################################################################################################
        $output->writeln('<comment>Site backup localy...</comment>');
        system("rsync -av --delete ".$site_root." ".$backup_dir_local_path." >> ".$backup_logs_file);
        system("chmod -R 777 ".$sites_storage_local_path_root." >> ".$backup_logs_file);
        ##################################################################################################################
        $output->writeln('<comment>Site backup on S3...</comment>');
//        system("/snap/bin/aws s3api put-bucket-versioning --bucket ".$bucket_name." --versioning-configuration Status=Enabled --profile ".$aws_config_file_profile." >> ".$backup_logs_file);
        system("/snap/bin/aws s3api put-bucket-versioning --bucket ".$bucket_name." --versioning-configuration Status=Enabled >> ".$backup_logs_file);
//        system("/snap/bin/aws s3 sync --delete ".$backup_dir_local_path." s3://".$bucket_name."/sites_backup/".$site_id." --profile ".$aws_config_file_profile." >> ".$backup_logs_file);
        system("/snap/bin/aws s3 sync --delete ".$backup_dir_local_path." s3://".$bucket_name."/sites_backup/".$site_id." >> ".$backup_logs_file);
        ##################################################################################################################
        $output->writeln("Site backup done successfully.".date("Y/m/d H:i:s")."\n\n");

        return 0;
    }
}
