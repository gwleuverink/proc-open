<?php

namespace App\Support\ManagedProcess;

class Factory
{
    /**
     * @throws \RuntimeException
     */
    public function start(string $alias, array|string|null $command = null, array $env = []): InvokedProcess
    {
        // TODO: Consider changing the io streams?
        // we might be able to use fwrite & fgets to communicate?
        // Might be useless since we lose the reference to the pipes on the next request? unless we can retreive them later by pid?
        $descriptors = [];
        $pipes = [];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            base_path(),
            $env,
        );

        if (! is_resource($process)) {
            throw new \RuntimeException("Unable to execute '{$command}'");
        }

        $status = proc_get_status($process);

        return $this->register($alias, $status['pid'] ?? null, $command);
    }

    public function get(string $alias): ?InvokedProcess
    {
        if (! $stored = cache()->get("managed-process.$alias")) {
            return new InvokedProcess($alias, null, null);
        }

        if (! $stored->running()) {
            return $this->register($alias, null, $stored->command());
        }

        return $stored;
    }

    private function register(string $alias, ?int $pid, ?string $command = null): InvokedProcess
    {
        cache()->forget("managed-process.$alias");

        // Not definitive!
        $process = cache()->rememberForever(
            "managed-process.$alias",
            fn () => new InvokedProcess($alias, $pid, $command)
        );

        return $process;
    }
}
