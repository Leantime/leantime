<?php

namespace Leantime\Domain\CsvImport\Listeners;

use Leantime\Domain\CsvImport\Services;

/**
 * Class AddCSVImportProvider.
 *
 * The AddCSVImportProvider class is responsible for adding a CSV import provider to the given payload.
 */
class AddCSVImportProvider
{
    /**
     * @param mixed $payload
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return mixed
     */
    public function handle(mixed $payload): mixed
    {
        $provider = app()->make(Services\CsvImport::class);
        $payload[$provider->id] = $provider;

        return $payload;
    }
}
