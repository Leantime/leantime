<div class="tw-p-s tw-rounded tw-mb-s"
     style="border:1px solid var(--accent1); background:var(--layered-background);">
    <h5 class="tw-font-semibold tw-mb-s">
        <i class="fa fa-paper-plane tw-mr-xs" style="color:var(--accent1);"></i>
        {{ __('clientportal.headlines.new_request') }}
    </h5>

    <form hx-post="{{ BASE_URL }}/hx/clientportal/requests/submit"
          hx-target="#request-list-wrapper"
          hx-swap="innerHTML"
          hx-encoding="multipart/form-data"
          hx-on::after-request="document.getElementById('request-form-container').innerHTML=''">

        <input type="hidden" name="projectId" value="{{ $projectId }}">

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.request_title') }} <span style="color:var(--accent2);">*</span></label>
            <input type="text" name="title" class="form-control"
                   placeholder="{{ __('clientportal.placeholders.request_title') }}" required>
        </div>

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.description') }}</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="{{ __('clientportal.placeholders.request_description') }}"></textarea>
        </div>

        <div class="tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('clientportal.labels.attach_file') }}</label>
            <input type="file" name="requestFile" class="form-control"
                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
            <small style="color:var(--grey);">{{ __('clientportal.text.file_hint') }}</small>
        </div>

        <div class="tw-flex tw-gap-s">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-paper-plane tw-mr-xs"></i>{{ __('clientportal.buttons.submit_request') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    onclick="document.getElementById('request-form-container').innerHTML=''">
                {{ __('buttons.cancel') }}
            </button>
        </div>

    </form>
</div>
