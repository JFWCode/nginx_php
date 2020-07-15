<?php
define('ROOT','/');
echo "User is " . exec('whoami');
// timezone setting 'shanghai'
//date_default_timezone_get('Asia/Shanghai');
echo "date_default_timezone" . date_default_timezone_get() . " <br>";
print Date("Y/m/d H/i/s") . "</br>";

echo "upload_max_filesize: " . ini_get('upload_max_filesize') . " <br>";
echo "post_max_size: " . ini_get('post_max_size') . " <br>";
echo "memory_limit: " . ini_get('memory_limit') . " <br>";
echo "max_execution_time: " . ini_get('max_execution_time') . " <br>";

/**
 * save zip file to httpd server
 * @param  [string] $tmp_report_dir   upload file storage dir
 * @return [string]                   success return file name, failed return empty string
 */
function uploadFile($tmp_report_dir){
    // allowed file type
    $tmp_report_dir = ROOT . $tmp_report_dir;
    $allowedExts = array("zip");
    $temp = explode(".", $_FILES["file"]["name"]);
    echo $_FILES["file"]["size"] . "<br>";
    $extension = end($temp) ;     // File extension
    echo end($temp) . "<br>";

    $return_value = '';
    if (in_array($extension, $allowedExts)){
        if ($_FILES["file"]["error"] > 0){
            $file_error = $_FILES["file"]["error"];
            if (1 == $file_error) {
                echo "error: upload file size is exceed the upload_max_filesize" . "<br>";
            } elseif (2 == $file_error) {
                echo "error: upload file size is exceed the HTML MAX_FILE_SIZE" . "<br>";
            } else {
                echo "error: " . $file_error . "<br>";
            }
        } else {
            echo "filename: " . $_FILES["file"]["name"] . "<br>";
            echo "file type: " . $_FILES["file"]["type"] . "<br>";
            echo "file size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
            echo "file temporary storage location: " . $_FILES["file"]["tmp_name"] . "<br>";
            
            echo $tmp_report_dir . "<br>";
            if (file_exists($tmp_report_dir)){
                echo "Dir " . $tmp_report_dir ." dir is exist" . "<br>";
            } else {
                $variable = mkdir($tmp_report_dir);
            }
            
            if (file_exists($tmp_report_dir)){
                // 判断当期目录下的 tmp_report_dir 目录是否存在该文件
                // 如果没有 tmp_report_dir 目录，你需要创建它，tmp_report_dir 目录权限为 777
                $complete_file_path = ROOT . $tmp_report_dir . $_FILES["file"]["name"];
                echo "complete file path is " . $complete_file_path . "<br>";
                if (file_exists($complete_file_path)){
                    echo $complete_file_path . " file already exist." . "<br>";
                } else {
                    // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $complete_file_path)) {
                       echo "error";
                    }
                    echo "File save at: " . $complete_file_path . "<br>";
                }
                $return_value = $tmp_report_dir . $_FILES["file"]["name"];
            }
        }
    } else {
        echo "Illegal file, only allowed zip extension file";
    }
    
    return $return_value;
}

/**
 * unzip a zip file
 * @param  [string] $toName   解压到哪个目录下
 * @param  [string] $fromName 被解压的文件名
 * @return [bool]             成功返回TRUE, 失败返回FALSE
 */
function unzip($fromName, $toName)
{
    if(!file_exists($fromName)){
        return FALSE;
    }

    $zipArc = new ZipArchive();
    if(!$zipArc->open($fromName)){
        return FALSE;
    }

    if(!$zipArc->extractTo($toName)){
            $zipArc->close();
            return FALSE;
    }

    return $zipArc->close();
}

class Site{
    static $tmp_report_dir = "tmp_report_dir/";

    public static function main(){
        $filename = uploadFile(Site::$tmp_report_dir);
        if ('' != $filename) {
            $report_resource = join('/', array(rtrim(ROOT, '/'), trim('report_resource', '/')));
            echo $report_resource . '<br>';
            $unzipResult = unzip($filename, $report_resource);
            if ($unzipResult) {
                echo 'Unzip Success.' . '<br>';
            } else {
                http_response_code(500);
                echo 'Unzip Failed.' . '<br>';
            }
            if (!unlink($filename)) {
                http_response_code(500);
                echo 'Remove File Failed.' . '<br>';
            }
        } else {
            http_response_code(500);
        }
    }
}

Site::main();

?>
