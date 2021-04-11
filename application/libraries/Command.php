<?php
require_once(__DIR__ . '../../helper/CommandExec.php');

class CommandRun
{
    private $_library            = null;
    protected $commandMain       = '/Mute "Realtek High Definition Audio\Application\System Sounds"';
    protected $commandAdditional = '/Mute "Sound Blaster Omni Surround 5.1\Application\System Sounds"';

	public function __construct(string $library) {
        $this->_library = $library;
    }

    public function soundMute(): void {
        $command = new CommandExec("{$this->_library} {$this->commandMain}");
        if (!$command->execute()) {
           throw new Error($command->getError(), $command->getExitCode());
        }

        $command = new CommandExec("{$this->_library} {$this->commandAdditional}");
        if (!$command->execute()) {
           throw new Error($command->getError(), $command->getExitCode());
        }
    }
}
