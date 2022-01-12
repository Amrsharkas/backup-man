<?php
namespace Console\App\Commands;
class Helper{

    public static function contentToFile($content, $path,$mode="w")
    {
        $file = $path;
        $myfile = fopen($file, $mode) or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
        return $file;
    }
    
    public static function getLineNumber($file, $searchable)
    {
        $handle = fopen($file, "r");
        if ($handle) {
            $line = 0;
            while (!feof($handle)) {
                $buffer = fgets($handle);
                if (strpos($buffer, $searchable) !== FALSE) {
                    fclose($handle);
                    return $line;
                }
                $line++;
            }
        }

        return -1;
    }

    public static function deleteContentOccuranceByLineNumber($file_path, $line_number)
    {
        $file_out = file($file_path);

        unset($file_out[$line_number]);

        file_put_contents($file_path, $file_out);

    }

    public static function deleteAllContentOccurance($file_path, $content)
    {
        do{
            $line_number = $this->getLineNumber($file_path,$content);
            if($line_number != -1){
                $this->deleteContentOccuranceByLineNumber($file_path,$line_number);
            }
        }while($line_number != -1);

    }

    public static function deleteDirectory($path)
    {
        if(is_dir($path) ){
            system("rm -rf ".$path);
        }
    }

    public static function deleteFile($path)
    {
        if(file_exists($path) ){
            system("rm ".$path);
        }
    }

    public static function copyDirectory($from,$to)
    {
        system("cp -rp ".$from." ".$to);
    }

    public static function moveDirectory($from,$to)
    {
        system("mv ".$from." ".$to);
    }

    public static function deleteAllFilesExceptOne($path,$except)
    {
        system("cd ".$path." && find . \! -name ".$except." -delete");

    }

}

