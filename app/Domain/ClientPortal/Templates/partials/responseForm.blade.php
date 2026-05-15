<div class="tw-p-s tw-rounded tw-mt-s"
     style="border:1px solid var(--accent1); background:var(--layered-background);">
    <h6 class="tw-font-semibold tw-mb-s">
        <i class="fa fa-reply tw-mr-xs" style="color:var(--accent1);"></i>
        {{ __('clientportal.headlines.add_response') }}
    </h6>

    <form hx-post="{{ BASE_URL }}/hx/clientportal/requests/saveResponse"
          hx-target="{{ isset($fromAdmin) && $fromAdmin ? '#response-area-'.$requestId : '#request-list-wrapper' }}"
          hx-swap="innerHTML"
          hx-encoding="multipart/form-data">

        <input type="hidden" name="requestId" value="{{ $requestId }}">
        @if(isset($fromAdmin) && $fromAdmin)
            <input type="hidden" name="fromAdmin" value="1">
        @endif

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.notes') }}</label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="{{ __('clientportal.placeholders.response_notes') }}"></textarea>
        </div>

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.drive_link') }}</label>
            <input type="url" name="driveLink" class="form-control"
                   placeholder="{{ __('clientportal.placeholders.drive_link') }}">
            <small style="color:var(--grey);">{{ __('clientportal.text.drive_link_hint') }}</small>
        </div>

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.upload_document') }}</label>
            <input type="file" name="responseFile" class="form-control"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/*">
        </div>

        <div class="tw-flex tw-gap-s">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-save tw-mr-xs"></i>{{ __('clientportal.buttons.save_response') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    onclick="this.closest('div.tw-p-s').remove()">
                {{ __('buttons.cancel') }}
            </button>
        </div>

    </form>
</div>
