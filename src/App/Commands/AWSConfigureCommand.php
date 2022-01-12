<?php
namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Console\App\Commands\Helper;
class AWSConfigureCommand extends Command
{
    protected function configure()
    {
        $this->setName("aws:configure")
                ->setHelp("aws:configure key secret region output profile")
                ->setDescription('used for configure aws cli creadintailas')
                ->addArgument("key",
                            InputArgument::REQUIRED,
                            "write aws key"
                            )
                ->addArgument("secret",
                            InputArgument::REQUIRED,
                            "write aws secret"
                            )
                ->addArgument("region",
                            InputArgument::REQUIRED,
                            "write aws region"
                            )
                ->addArgument("output",
                            InputArgument::REQUIRED,
                            "write aws output format"
                            )
                ->addArgument("profile",
                            InputArgument::REQUIRED,
                            "write aws profile"
                        );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        exec("cd ~/ && pwd",$output)[0];
        $HOME = $output[0];

        $aws_dir_path = $HOME."/.aws";
        $aws_config_path = $aws_dir_path."/config";
        $aws_creadentials_path = $aws_dir_path."/credentials";
        $aws_profile=$input->getArgument("profile");
        $aws_region=$input->getArgument("region");
        $aws_output=$input->getArgument("output");
        $aws_key=$input->getArgument("key");
        $aws_secret=$input->getArgument("secret");


        if(! is_dir($aws_dir_path) ){
            system("mkdir ".$aws_dir_path);
            system("chmod 777 -R ".$aws_dir_path);
        }else{
            system("chmod 777 -R ".$aws_dir_path);
        }

        if(! file_exists($aws_config_path) ){
            system("touch ".$aws_config_path);
            system("chmod 777 -R ".$aws_dir_path);
        }else{
            system("chmod 777 -R ".$aws_dir_path);
        }

        $line_number = Helper::getLineNumber($aws_config_path,$aws_profile);
        if($line_number != -1 ){
            Helper::deleteContentOccuranceByLineNumber($aws_config_path, $line_number);
            Helper::deleteContentOccuranceByLineNumber($aws_config_path, $line_number);
            Helper::deleteContentOccuranceByLineNumber($aws_config_path, $line_number);
        }
        
        Helper::contentToFile("[".$aws_profile."]\n",$aws_config_path,"a");
        Helper::contentToFile("region = ".$aws_region."\n",$aws_config_path,"a");
        Helper::contentToFile("output = ".$aws_output."\n",$aws_config_path,"a");


        if(! is_dir($aws_creadentials_path) ){
            system("touch ".$aws_creadentials_path);
            system("chmod 777 -R ".$aws_dir_path);
        }else{
            system("chmod 777 -R ".$aws_dir_path);
        }

        $line_number = Helper::getLineNumber($aws_creadentials_path,$aws_profile);
        if($line_number != -1 ){
            Helper::deleteContentOccuranceByLineNumber($aws_creadentials_path, $line_number);
            Helper::deleteContentOccuranceByLineNumber($aws_creadentials_path, $line_number);
            Helper::deleteContentOccuranceByLineNumber($aws_creadentials_path, $line_number);
        }
        
        Helper::contentToFile("[".$aws_profile."]\n",$aws_creadentials_path,"a");
        Helper::contentToFile("aws_secret_access_key = ".$aws_secret."\n",$aws_creadentials_path,"a");
        Helper::contentToFile("aws_access_key_id = ".$aws_key."\n",$aws_creadentials_path,"a");

        return 0;
    }
}
