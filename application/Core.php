<?php
require_once(__DIR__ . '/libraries/Notification.php');
require_once(__DIR__ . '/libraries/File.php');
require_once(__DIR__ . '/libraries/Command.php');

class Core
{
    private $_notification = null;
    private $_file         = null;
    private $_command      = null;

	public function __construct() {
        $this->_notification = new Notification();
        $this->_file         = new File();
    }

    public function init(): void {
        $this->_notification->init();
        try {
            if (!$this->_file->isFileExists()) {
                $this->_notification->drawInformNotification('Downloading the library you need...');
                $this->_file->downloadFile();
                if (!$this->_file->isFileExists()) {
                     throw new Error('Library download error.', 1);
                }
                $this->_notification->drawSuccessNotification('Library files were successfully downloaded.');
            } else {
                $this->_notification->drawSuccessNotification('Library file found.');
            }

            $this->_notification->drawInformNotification('Extracting the library...');
            $library = $this->_file->unzipFile();
            $this->_notification->drawSuccessNotification('The library was unpacked.');

            $this->_notification->drawInformNotification('Mute system sounds...');
            $this->_command = new CommandRun($library);
            $this->_command->soundMute();
            $this->_notification->drawSuccessNotification('System sounds have been muted.');

            $this->_notification->drawInformNotification('Clean up library files...');
            $this->_file->cleaningFiles();
            $this->_notification->drawSuccessNotification('The files have been cleaned.');

        } catch(Error $error) {
            $this->_notification->drawErrorNotification($error->getMessage());
        }
    }
}
