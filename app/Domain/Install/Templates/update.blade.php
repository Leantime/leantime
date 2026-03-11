<x-globals::layout.page-header headline="{{ $tpl->language->__('headlines.update_database') }}" />
{!! $tpl->displayInlineNotification() !!}
<div class="regcontent" id="login">
    <p>{!! $tpl->language->__('text.new_db_version') !!}</p>
    <form action="{{ BASE_URL }}/install/update" method="post" class="registrationForm">
        <input type="hidden" name="updateDB" value="1" />
        <p><x-globals::forms.button :submit="true" contentRole="primary" name="updateAction" onClick="this.form.submit(); this.disabled=true; this.value='Updating…';">{{ $tpl->language->__('buttons.update_now') }}</x-globals::forms.button></p>
    </form>
</div>
