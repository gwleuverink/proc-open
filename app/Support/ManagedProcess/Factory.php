<?php

namespace App\Support\ManagedProcess;

class Factory
{
    /**
     * @throws \RuntimeException
     */
    public function start(string $alias, ?string $command = null, array $env = []): InvokedProcess
    {
        // TODO: Consider changing the io streams?
        // we might be able to use fwrite & fgets to communicate?
        // Might be useless since we lose the reference to the pipes on the next request? unless we can retreive them later by pid?

        $pipes = [];
        $descriptors = [
            ['pipe', 'r'], // stdin
            ['pipe', 'w'], // stout
            ['pipe', 'w'], // sterr
        ];

        $process = proc_open(
            $this->fork($command),
            $descriptors,
            $pipes,
            base_path(),
            $env,
        );

        if (! is_resource($process)) {
            throw new \RuntimeException("Unable to execute '{$command}'");
        }

        // Read the PID from the output.
        // Relies on the double fork to output the child pid
        $pid = (int) fgets($pipes[1]);

        // Close pipes
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        // Close the process handle
        proc_close($process);

        return $this->register($alias, $pid ?? null, $command);
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

    /*
    |--------------------------------------------------------------------------
    | Register the process in the process store
    |--------------------------------------------------------------------------
    | Should use some other mechanism for keeping track of process data
    */
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

    /*
    |--------------------------------------------------------------------------
    | Wrap the given command in a double fork
    |--------------------------------------------------------------------------
    | If we run the original command through proc_open A zombie hang around
    | Normally we would proc_close in the parent when the child exits
    | By double-forking it the forked process closes itself
    */
    private function fork(string $command): string
    {
        // TODO: This only works on Unix right now. Needs a poweshell alternative
        return <<< BASH
            (
                (
                    $command &
                    echo $!  # This outputs the PID of the last background process

                    # Wait for the process to finish
                    wait $!
                    exit 0
                ) &
            ) &

            exit 0
        BASH;
    }

    /*
    |--------------------------------------------------------------------------
    | The lab - experimental
    |--------------------------------------------------------------------------
    */

    // TODO: Consider artisan shorthand
    // public function artisan(string $command): InvokedProcess {}

    // TODO: Consider fluent api (and make alias an optional argument in methods that require it)
    // Process::alias('foo')->start('sleep 10s');
    // public function alias(string $alias): self {}
}
