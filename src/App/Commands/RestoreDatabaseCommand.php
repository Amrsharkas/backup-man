<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class RestoreDatabaseCommand extends Command
{
    protected function configure()
    {
        $this->setName("restore-database")
                ->setHelp("restore-database database_name at bucket_name profile
                                    server_id=if you have many servers
                                    user=mysql user name if type [1,3]
                                    password=mysql user password if type [1,3]
                                    bucket_name=aws bucket which used for backup
                                    at=versioning time
                                    profile=aws user profile")
                ->setDescription('used for backup databases per user or all databases in "/var/lib/" or for both')
                ->addArgument("database_name",InputArgument::REQUIRED,"write database name to restore")
                ->addArgument("at",InputArgument::REQUIRED,"write versioning time")
                ->addArgument("bucket_name",InputArgument::REQUIRED,"write your aws bucket name")
                ->addArgument("profile",InputArgument::REQUIRED,"write your aws profile");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $databases_storage_local_path_root="/restored_database";
        $database_name=$input->getArgument("database_name");
        $aws_config_file_profile=$input->getArgument("profile");
        $bucket_name=$input->getArgument("bucket_name");
        $at=$input->getArgument("at");
        $restore_logs_directory = "/restore_logs";
        $restore_logs_file = $restore_logs_directory."/restore_log_database_".date("Y_m_d__H_i_s").".txt";
        ##################################################################################################################
        $output->writeln('<comment>Create logs directory "'.$restore_logs_directory.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($restore_logs_directory) ){
            system("mkdir ".$restore_logs_directory);
            system("chmod 777 -R ".$restore_logs_directory);
        }
        ##################################################################################################################
        $output->writeln('<comment>Create staging directory "'.$databases_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(is_dir($databases_storage_local_path_root) ){
            system("rm -rf ".$databases_storage_local_path_root." >> ".$restore_logs_file);

        }else {
            system("mkdir ".$databases_storage_local_path_root." >> ".$restore_logs_file);
            system("chmod 777 -R ".$databases_storage_local_path_root." >> ".$restore_logs_file);
        }
        ##################################################################################################################
        $output->writeln('<comment>restore database from S3 ...."'.$databases_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        system("/usr/local/bin/s3-pit-restore -b ".$bucket_name." -d ".$databases_storage_local_path_root." -p database_backup/mysql/".$database_name." -t '".$at."' --max-workers 100 >> ".$restore_logs_file);
        system("find ".$databases_storage_local_path_root."/database_backup/mysql -mindepth 1 ! -regex '^".$databases_storage_local_path_root."/database_backup/mysql/".$database_name."$' -delete >> ".$restore_logs_file);
        system("/usr/local/bin/s3-pit-restore -b ".$bucket_name." -d ".$databases_storage_local_path_root." -p database_backup/".$database_name.".sql -t '".$at."' --max-workers 100 >> ".$restore_logs_file);
        ##################################################################################################################
        $output->writeln("Database restore done successfully.".date("Y/m/d H:i:s")."\n\n");

        return 0;
    }
}
