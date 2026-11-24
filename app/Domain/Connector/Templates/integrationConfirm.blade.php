@extends($layout)

@section('content')

@php
    $provider = $provider ?? null;

    /** @var \Leantime\Domain\Connector\Models\ImportResult|null $importResult */
    $importResult = $importResult ?? null;
    $imported = $importResult?->imported ?? 0;
    $failed = $importResult?->failedCount() ?? 0;
    $importedEntity = $importedEntity ?? '';
    $importedProjects = $importedProjects ?? [];
    $succeeded = $importResult === null || $importResult->isSuccessful();

    // "3 to-dos imported into Website Redesign"; other imports say how many rows came in.
    $kind = in_array($importedEntity, ['tickets', 'milestones'], true) ? $importedEntity : 'rows';
    $title = count($importedProjects) === 1
        ? sprintf(__('text.import_done_'.$kind.($imported === 1 ? '_one' : '').'_into'), $imported, reset($importedProjects))
        : sprintf(__('text.import_done_'.$kind.($imported === 1 ? '_one' : '')), $imported);
@endphp

<x-global::layout.page-header icon="fa-solid fa-circle-nodes">
    {{ __('headlines.integrations') }} / {{ $provider->name }}
</x-global::layout.page-header>

{!! $tpl->displayNotification() !!}

<div class="maincontent">
    <x-global::layout.content-box>
        @include('connector::submodules.importProgress', ['currentStep' => 'import', 'allStepsDone' => $succeeded])

        @if ($succeeded)
            <x-global::elements.empty-state icon="fa fa-circle-check" titleAs="h3" :title="$importResult !== null ? $title : __('headlines.integration_success')" class="lt-import-done">
                @if (count($importedProjects) > 1)
                    {{ sprintf(__('text.import_done_projects'), implode(', ', $importedProjects)) }}
                @endif
                <x-slot:actions>
                    @if (count($importedProjects) === 1)
                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/changeCurrentProject/{{ array_key_first($importedProjects) }}" contentRole="primary">{{ sprintf(__('buttons.import_open_project'), reset($importedProjects)) }}</x-global::forms.button>
                    @endif
                    <x-global::forms.button tag="a" :link="$provider->stepUrl('connect')" contentRole="secondary">{{ __('buttons.import_another_file') }}</x-global::forms.button>
                </x-slot:actions>
            </x-global::elements.empty-state>
        @else
            {{-- Some rows came in (warning) or none did (danger); each failed row with its sheet row number. --}}
            <x-global::feedback.alert
                :tone="$imported > 0 ? 'warning' : 'danger'"
                :title="$imported > 0 ? __('headlines.integration_partial') : __('headlines.integration_failed')"
            >
                <p>{{ $imported > 0 ? sprintf(__('text.integration_partial'), $imported, $failed) : __('text.integration_failed') }}</p>
                <ul>
                    @foreach ($importResult->failures as $failure)
                        <li>{{ sprintf(__('notification.import_row_failed'), $failure['row'], $failure['message']) }}</li>
                    @endforeach
                </ul>
            </x-global::feedback.alert>

            <div class="lt-form-actions">
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/connector/show" contentRole="tertiary">{{ __('links.back_to_integrations') }}</x-global::forms.button>
                <x-global::forms.button tag="a" :link="$provider->stepUrl('connect')" contentRole="secondary">{{ __('links.integration_import_again') }}</x-global::forms.button>
            </div>
        @endif
    </x-global::layout.content-box>
</div>

@endsection
