<?php

class File
{
    private $_extractedFiles    = [];
    protected $url              = 'https://www.nirsoft.net/utils/soundvolumeview-x64.zip';
    protected $fileName         = null;
    protected $userAgent        = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
    protected $saveFileLocation = null;

    public function __construct() {
        $this->fileName         = \basename($this->url);
        $this->saveFileLocation = \sys_get_temp_dir() . $this->fileName;
    }

    public function isFileExists(): bool {
        if (\file_exists($this->saveFileLocation)) {
            return true;
        }
        
        return false;
    }

    public function downloadFile(): void {
        $file = \fopen($this->saveFileLocation, 'wb');
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_URL, $this->url);
        \curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        \curl_setopt($curl, CURLOPT_FAILONERROR, true);
        \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        \curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        \curl_setopt($curl, CURLOPT_FILE, $file);
        \curl_setopt($curl, CURLOPT_HEADER, 0);
        \curl_exec($curl);
        \curl_close($curl);
        \fclose($file);
    }

    public function unzipFile(): string {
         $zip = new ZipArchive;
         if ($zip->open($this->saveFileLocation)) {
             $library = null;
             for ($i = 0; $i < $zip->numFiles; $i += 1) {
                 \array_push($this->_extractedFiles, $zip->getNameIndex($i));
                 
                 if (strpos($zip->getNameIndex($i), '.exe')) {
                    $library = $zip->getNameIndex($i);
                 }
             }

             $zip->extractTo('./');
             $zip->close();

             if(!empty($library)) {
                return $library;
             } else {
                throw new Error('Library file is missing.', 1);
             }
         } else {
             throw new Error('Failed to extract the library files.', 1);
         }
    }

    public function cleaningFiles(): void {
         foreach ($this->_extractedFiles as $file) {
             \unlink("./{$file}");
         }
    }
}
