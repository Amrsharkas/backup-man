<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class BackupDatabaseCommand extends Command
{
    protected function configure()
    {
        $this->setName("backup-database")
                ->setHelp("database backup_type mysql_user mysql_password bucket_name profile
                                    backup_type=1 per user
                                               =2 all databases
                                               =3 both
                                    user=mysql user name if type [1,3]
                                    password=mysql user password if type [1,3]
                                    profile=aws user profile
                                    bucket_name=aws bucket which used for backup")
                ->setDescription('used for backup databases per user or all daabases in "//var/lib/" or for both')
                ->addArgument("backup_type",InputArgument::REQUIRED,"Select backup type")
                ->addArgument("mysql_user",InputArgument::REQUIRED,"write mysql user")
                ->addArgument("mysql_password",InputArgument::REQUIRED,"write mysql password")
                ->addArgument("bucket_name",InputArgument::REQUIRED,"write your aws bucket name")
                ->addArgument("profile",InputArgument::REQUIRED,"write your aws profile");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $databasesBackupOption=$input->getArgument("backup_type");
        $databases_storage_local_path_root="/backup_database";
        $aws_config_file_profile=$input->getArgument("profile");
        $bucket_name=$input->getArgument("bucket_name");
        $backup_logs_directory = "/backup_logs";
        $backup_logs_file = $backup_logs_directory."/backup_log_database_".date("Y_m_d__H_i_s").".txt";
        ##################################################################################################################
        $output->writeln('<comment>Create logs directory "'.$backup_logs_directory.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($backup_logs_directory) ){
            system("mkdir ".$backup_logs_directory);
            system("chmod 777 -R ".$backup_logs_directory);
        }
        ##################################################################################################################
        $output->writeln('<comment>Create staging directories "'.$databases_storage_local_path_root.'"...'.date("Y/m/d H:i:s").'</comment>');
        if(! is_dir($databases_storage_local_path_root) ){
            system("mkdir ".$databases_storage_local_path_root." >> ".$backup_logs_file);
            system("chmod 777 -R ".$databases_storage_local_path_root." >> ".$backup_logs_file);
        }
        ##################################################################################################################
        $output->writeln('<comment>Database backup locally...'.date("Y/m/d H:i:s").'</comment>');
        if( ($databasesBackupOption == 2) || ($databasesBackupOption == 3) ){
            system("chmod 777 -R /var/lib/mysql"." >> ".$backup_logs_file);
            system("rsync -av --delete /var/lib/mysql ".$databases_storage_local_path_root." >> ".$backup_logs_file." &");
        }else{
            if(is_dir($databases_storage_local_path_root."/mysql") ){
                system("rm -rf ".$databases_storage_local_path_root."/mysql >> ".$backup_logs_file);
                system("chmod 777 -R ".$databases_storage_local_path_root." >> ".$backup_logs_file);
            }
        }

        if( ($databasesBackupOption == 1) || ($databasesBackupOption == 3) ){
            $user=$input->getArgument("mysql_user");
            $password=$input->getArgument("mysql_password");

            $databases=array();
            exec("mysql -u ".$user." -p".$password." -e 'SHOW DATABASES;' | tr -d '| ' | grep -v Database",$databases);

            foreach($databases as $db){
                if( (($db != "information_schema")) && (($db != "performance_schema")) && (($db != "mysql")) && (($db != "_*" )) && (($db != "phpmyadmin")) && (($db != "sys")) ){
                    system("mysqldump -u ".$user." -p".$password." --databases ".$db." > ".$databases_storage_local_path_root."/".$db.".sql");
                }
            }
        }
        ##################################################################################################################
        system("chmod 777 -R ".$databases_storage_local_path_root." >> ".$backup_logs_file);
        ##################################################################################################################
        $output->writeln('<comment>Database backup on S3...'.date("Y/m/d H:i:s").'</comment>');
//        system("/snap/bin/aws s3 sync --delete ".$databases_storage_local_path_root." s3://".$bucket_name."/database_backup/ --profile ".$aws_config_file_profile." >> ".$backup_logs_file);
        system("/snap/bin/aws s3 sync --delete ".$databases_storage_local_path_root." s3://".$bucket_name."/database_backup/ >> ".$backup_logs_file);
        ##################################################################################################################
        $output->writeln("Database backup done successfully.".date("Y/m/d H:i:s")."\n\n");

        return 0;
    }
}

