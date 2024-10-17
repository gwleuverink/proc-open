<?php

namespace App\Support\ManagedProcess;

use Illuminate\Support\Facades\Process;

class InvokedProcess
{
    public function __construct(
        protected string $alias,
        protected ?int $pid,
        protected ?string $command,
    ) {}

    public function alias(): string
    {
        return $this->alias;
    }

    public function id(): ?int
    {
        return $this->pid;
    }

    public function command(): ?string
    {
        return $this->command;
    }

    public function running()
    {
        if (! $this->id()) {
            return false;
        }

        $determineRunning = match (PHP_OS_FAMILY) {
            'Windows' => function () {
                // TODO: needs checking - might need to check output
                $process = Process::run("tasklist /FI \"PID eq {$this->pid}\"");

                return str($process->output())->contains($this->pid);
            },
            default => function () {
                $process = Process::run("ps -p {$this->pid}");
                $output = str($process->output());

                return $output->contains($this->pid);
            }
        };

        return $determineRunning();
    }

    public function restart(): self
    {
        $this->stop();

        // TODO: Add original env argument
        return (new Factory)->start(
            $this->alias(),
            $this->command(),
        );
    }

    public function stop(): self
    {
        match (PHP_OS_FAMILY) {
            'Windows' => Process::run("taskkill /F /PID {$this->id()}"),
            default => Process::run("kill {$this->id()}")
        };

        $this->pid = null;

        return $this;
    }

    // public function throw(): self
    // {
    //     //
    // }
}
