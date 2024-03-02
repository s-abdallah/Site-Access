<?php
/*
 * PHP: Recursively Backup Files & Folders to ZIP-File
 * MIT-License - Copyright (c) 2012-2017 Marvin Menzerath
 */
// Make sure the script can handle large folders/files
ini_set('max_execution_time', 600);
ini_set('memory_limit', '1024M');
date_default_timezone_set('America/New_York');
// Start the backup!
// zipData('/path/to/folder', '/path/to/backup.zip');
// echo 'Finished.';
// Here the magic happens :)

class BACKUP
{

    public $snapPath = '';
    public $uploadPath = "../_uploads/"; // can be tweaked for custom installs, otherwise it expects the _uploads dir to be at the root of the site the CMS is being used on

    public $dataPath = '';
    public $securityPath = '';
    public $mediaPath = '';

    // class constructor that gets called by default along with instantiation of class
    public function __construct($a)
    {
        //echo "hello world!";
        //echo $a;
        $this->snapPath = $a;
        $this->dataPath = $a . '_temp/_data';
        $this->securityPath = $a . '_temp/_security';
        $this->mediaPath = $a . '_temp/_uploads';
    }

    public function setZip()
    {
        #create backup first from current data.
        $this->duplicateData();
        #get filename depends on the current data/time
        $today = date("Y-m-d_H-i-s");
        $zip_file = $this->snapPath . 'snapshots-' . $today . '.zip';
        $result = $this->zipData($this->snapPath . '_temp', $zip_file);
        if ($result) {
            // snapshot created Successfully!
            $this->dirDelete();
            $this->dirCreate();
        }
    }

    public function cpyData($source, $destination)
    {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!is_dir($destination . "/" . $file)) {
                            mkdir($destination . "/" . $file);
                        }
                        $this->cpyData($source . "/" . $file, $destination . "/" . $file);
                    } else {
                        copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $destination);
        }
    }

    public function zipData($source, $destination)
    {
        if (extension_loaded('zip')) {
            if (file_exists($source)) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                    $source = realpath($source);
                    if (is_dir($source)) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file)) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            } else if (is_file($file)) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                    } else if (is_file($source)) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }

    public function delData($target)
    {

        if (is_dir($target)) {
            $objects = scandir($target);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($target . "/" . $object) == "dir") {
                        $this->delData($target . "/" . $object);
                    } else {
                        unlink($target . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($target);
        }

    }

    public function duplicateData()
    {
        $this->cpyData('data', $this->dataPath);
        $this->cpyData('security', $this->securityPath);
        $this->cpyData($this->uploadPath, $this->mediaPath);
        // copy the tools and config
        copy('config/config.php', $this->snapPath . '_temp/config.php');
        copy('config/tools.json', $this->snapPath . '_temp/tools.json');
    }

    public function dirDelete()
    {
        $this->delData($this->dataPath);
        $this->delData($this->securityPath);
        $this->delData($this->mediaPath);
        unlink($this->snapPath . '_temp/config.php');
        unlink($this->snapPath . '_temp/tools.json');
    }

    public function dirCreate()
    {
        mkdir($this->dataPath, 0777);
        mkdir($this->securityPath, 0777);
        mkdir($this->mediaPath, 0777);
    }

    public function installData($file)
    {
        $zip = new ZipArchive;
        $installPath = $this->snapPath . '_install/';
        mkdir($installPath, 0777);
        if ($zip->open($file) === true) {
            $zip->extractTo($installPath);
            $zip->close();
            $return = true;
            // copy the tools and config
            copy($installPath . 'config.php', 'config/config.php');
            copy($installPath . 'tools.json', 'config/tools.json');
            // install data
            if (file_exists($installPath . '_data')) {
                $this->delData('data');
                mkdir('data', 0777);
                $this->cpyData($installPath . '_data', 'data');
            }
            // install media
            if (file_exists($installPath . '_uploads')) {
                $this->delData($this->uploadPath);
                mkdir($this->uploadPath, 0777);
                $this->cpyData($installPath . '_uploads', $this->uploadPath);
            }
            // install media
            if (file_exists($installPath . '_security')) {
                $this->delData('security');
                mkdir('security', 0777);
                $this->cpyData($installPath . '_security', 'security');
            }
        } else {
            $return = false;
        }
        $this->delData($installPath);
        return $return;
    }

    public function deleteData($file)
    {
        if (file_exists($file)) {
            if (unlink($file)) {
                $return = true;
            } else {
                $return = false;
            }
            return $return;
        }
    }

}
