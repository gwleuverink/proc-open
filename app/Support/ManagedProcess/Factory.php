<?php

namespace App\Support\ManagedProcess;

use Illuminate\Process\Factory as ProcessFactory;

class Factory
{
    public function __construct(
        protected ProcessFactory $process = new ProcessFactory
    ) {}

    /**
     * @throws \Illuminate\Process\Exceptions\ProcessTimedOutException
     * @throws \RuntimeException
     */
    public function start(string $alias, array|string|null $command = null, ?callable $output = null): InvokedProcess
    {
        /** @var InvokedProcess $process */
        $process = $this->process->start($command, $output);

        return $this->register($alias, $process->id(), $command);
    }

    public function get(string $alias): ?InvokedProcess
    {
        if (! $stored = session()->get("managed-process.$alias")) {
            return new InvokedProcess($alias, null, null);
        }

        return new InvokedProcess($alias, $stored->pid, $stored->command);
    }

    private function register(string $alias, int $pid, array|string|null $command = null): InvokedProcess
    {
        // Not definitive!
        session()->put("managed-process.$alias", (object) [
            'pid' => $pid,
            'command' => $command,
        ]);

        return new InvokedProcess($alias, $pid, $command);
    }
}
