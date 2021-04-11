<?php

class CommandExec
{
    public $escapeCommand   = false;
    public $captureStdErr   = true;
    public $procEnv         = null;
    public $procOptions     = null;
    public $nonBlockingMode = null;
    public $timeout         = null;
    protected $_command     = null;
    protected $_args        = [];
    protected $_stdOut      = '';
    protected $_stdErr      = '';
    protected $_exitCode    = null;
    protected $_error       = '';
    protected $_executed    = false;

    public function __construct($options = null) {
        if (\is_array($options)) {
            $this->setOptions($options);
        } elseif (\is_string($options)) {
            $this->setCommand($options);
        }
    }

    public function setOptions(array $options): object {
        foreach ($options as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $method = 'set' . \ucfirst($key);
                if (\method_exists($this, $method)) {
                    \call_user_func(array($this,$method), $value);
                } else {
                    throw new \Exception("Unknown configuration option '{$key}'");
                }
            }
        }
        return $this;
    }

    public function setCommand(string $command): object {
        if ($this->escapeCommand) {
            $command = \escapeshellcmd($command);
        }

        if ($this->getIsWindows()) {
            if (isset($command[1]) && $command[1] === ':') {
                $position = 1;
            } elseif (isset($command[2]) && $command[2] === ':') {
                $position = 2;
            } else {
                $position = false;
            }

            if ($position) {
                $command = \sprintf(
                    "{$command[$position - 1]}: && cd %s && %s",
                    \escapeshellarg(\dirname($command)),
                    \escapeshellarg(\basename($command))
                );
            }
        }
        $this->_command = $command;
        return $this;
    }

    public function getCommand(): string {
        return $this->_command;
    }

    public function getExecCommand(): string {
        $command = $this->getCommand();
        if (!$command) {
            $this->_error = 'Could not locate any executable command';
            return false;
        }

        $args = $this->getArgs();
        return $args ? "{$command} {$args}" : $command;
    }

    public function setArgs(array $args): object {
        $this->_args = $args;
        return $this;
    }

    public function getArgs(): string {
        return \implode(' ', $this->_args);
    }

    public function getOutput(bool $trim = true): string {
        return $trim ? \trim($this->_stdOut) : $this->_stdOut;
    }

    public function getError(bool $trim = true): string {
        return $trim ? \trim($this->_error) : $this->_error;
    }

    public function getStdErr(bool $trim = true): string {
        return $trim ? \trim($this->_stdErr) : $this->_stdErr;
    }

    public function getExitCode(): int {
        return $this->_exitCode;
    }

    public function getExecuted(): bool {
        return $this->_executed;
    }

    public function execute(): bool {
        $command = $this->getExecCommand();

        if (!$command) {
            return false;
        }

        if ($this->useExec) {
            $execCommand = $this->captureStdErr ? "{$command} 2>&1" : $command;
            \exec($execCommand, $output, $this->_exitCode);
            $this->_stdOut = \implode("\n", $output);
            if ($this->_exitCode !== 0) {
                $this->_stdErr = $this->_stdOut;
                $this->_error = empty($this->_stdErr) ? 'Command failed' : $this->_stdErr;
                return false;
            }
        } else {
            $hasTimeout = $this->timeout !== null && $this->timeout > 0;

            $descriptors = [
                1   => ['pipe','w'],
                2   => ['pipe', $this->getIsWindows() ? 'a' : 'w'],
            ];

            $nonBlocking = $this->nonBlockingMode === null ?
                !$this->getIsWindows() : $this->nonBlockingMode;

            $startTime = $hasTimeout ? time() : 0;
            $process = \proc_open($command, $descriptors, $pipes, $this->procCwd, $this->procEnv, $this->procOptions);

            if (\is_resource($process)) {
                if ($nonBlocking) {
                    \stream_set_blocking($pipes[1], false);
                    \stream_set_blocking($pipes[2], false);

                    $isRunning = true;
                    while ($isRunning) {
                        $status = \proc_get_status($process);
                        $isRunning = $status['running'];

                        while (($out = fgets($pipes[1])) !== false) {
                            $this->_stdOut .= $out;
                        }
                        while (($err = fgets($pipes[2])) !== false) {
                            $this->_stdErr .= $err;
                        }

                        $runTime = $hasTimeout ? time() - $startTime : 0;
                        if ($isRunning && $hasTimeout && $runTime >= $this->timeout) {
                            \proc_terminate($process);
                        }

                        if (!$isRunning) {
                            $this->_exitCode = $status['exitcode'];
                            if ($this->_exitCode !== 0 && empty($this->_stdErr)) {
                                if ($status['stopped']) {
                                    $signal = $status['stopsig'];
                                    $this->_stdErr = "Command stopped by signal {$signal}";
                                } elseif ($status['signaled']) {
                                    $signal = $status['termsig'];
                                    $this->_stdErr = "Command terminated by signal {$signal}";
                                } else {
                                    $this->_stdErr = 'Command unexpectedly terminated without error message';
                                }
                            }
                            \fclose($pipes[1]);
                            \fclose($pipes[2]);
                            \proc_close($process);
                        } else {
                            \usleep(10000);
                        }
                    }
                } else {
                    $this->_stdOut = \stream_get_contents($pipes[1]);
                    $this->_stdErr = \stream_get_contents($pipes[2]);
                    \fclose($pipes[1]);
                    \fclose($pipes[2]);
                    $this->_exitCode = \proc_close($process);
                }

                if ($this->_exitCode !== 0) {
                    $this->_error = $this->_stdErr ?
                        $this->_stdErr :
                        "Failed without error message: {$command} (Exit code: {$this->_exitCode})";
                    return false;
                }
            } else {
                $this->_error = "Could not run command {$command}";
                return false;
            }
        }

        $this->_executed = true;

        return true;
    }

    public function getIsWindows(): bool {
        return \strtoupper(\substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function __toString(): string {
        return $this->getExecCommand();
    }
}
