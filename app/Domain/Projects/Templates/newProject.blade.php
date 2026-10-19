@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-suitcase" :eyebrow="__('label.administration')">
    {!! __('headline.new_project') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="fa fa-leaf"></span> {!! __('tabs.projectdetails') !!}</a></li>
            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="">

                    <div class="row">

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <x-global::forms.text-input width="full" variant="headline" name="name" id="name" value="{{ $project['name'] }}" placeholder="{{ __('input.placeholders.enter_title_of_project') }}" />
                                    </div>
                                    <input type="hidden" name="projectState"  id="projectState" value="0" />

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <br />
                                    <p>
                                        {!! __('label.accomplish') !!}
                                        {!! __('label.describe_outcome') !!}
                                        <br /><br />
                                    </p>
                                    <textarea name="details" id="details" class="tiptapComplex" rows="5" cols="50">{{ $project['details'] }}</textarea>

                                </div>
                            </div>
                            <div class="padding-top">
                                @if (isset($project['id']) && $project['id'] != '')
                                    <div class="pull-right padding-top">
                                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" class="delete" state="danger" variant="outline"><i class="fa fa-trash"></i> {!! __('buttons.delete') !!}</x-global::forms.button>
                                    </div>
                                @endif
                                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />

                            </div>
                        </div>

                        <div class="col-md-4">

                            @if ($projectTypes && count($projectTypes) > 1)
                                <x-global::elements.section-header icon="fa-regular fa-rectangle-list">{{ __('label.project_type') }}</x-global::elements.section-header>
                                <p>{{ __('text.project_type_help') }}</p>
                                <x-global::forms.select enhanced name="type">
                                    @foreach ($projectTypes as $key => $type)
                                        <option value="{{ $key }}"
                                            @if ($project['type'] == $key) selected='selected' @endif
                                        >{!! __($key) !!}</option>
                                    @endforeach
                                </x-global::forms.select>
                                <br /><br />
                            @endif

                            @dispatchEvent('beforeClientPicker', $project)

                            <div style="margin-bottom: 30px;">
                                <x-global::elements.section-header icon="fa fa-calendar">{!! __('label.project_dates') !!}</x-global::elements.section-header>
                                <div>
                                    <x-global::forms.label>{!! __('label.project_start') !!}</x-global::forms.label>
                                    <div class="">
                                        <x-global::forms.date-picker name="start" range="from" :value="$project['start']" class="dates dateFrom" />

                                    </div>
                                    <x-global::forms.label>{!! __('label.project_end') !!}</x-global::forms.label>
                                    <div class="">
                                        <x-global::forms.date-picker name="end" range="to" :value="$project['end']" class="dates dateTo" />

                                    </div>
                                </div>

                            </div>

                            <div style="margin-bottom: 30px;">

                                <div class="">
                                    <x-global::elements.section-header icon="fa fa-building">{!! __('label.client_product') !!}</x-global::elements.section-header>
                                    <x-global::forms.select enhanced name="clientId" id="clientId" aria-label="{{ __('label.client_product') }}">

                                        @foreach ($clients as $row)
                                            <option value="{{ $row['id'] }}"
                                                @if ($project['clientId'] == $row['id']) selected=selected @endif
                                            >{{ $row['name'] }}</option>
                                        @endforeach

                                    </x-global::forms.select>
                                    @if ($login::userIsAtLeast('manager'))
                                        <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank">{!! __('label.client_not_listed') !!}</a>
                                    @endif


                                </div>
                            </div>

                            <div style="margin-bottom: 30px;">
                                <div class="">
                                    <x-global::elements.section-header icon="fa fa-lock-open">{!! __('labels.defaultaccess') !!}</x-global::elements.section-header>
                                    {!! __('text.who_can_access') !!}
                                    <br /><br />

                                    <x-global::forms.select enhanced name="globalProjectUserAccess" aria-label="{{ __('labels.defaultaccess') }}">
                                        <option value="restricted" {{ $project['psettings'] == 'restricted' ? "selected='selected'" : '' }}>{!! __('labels.only_chose') !!}</option>
                                        <option value="clients" {{ $project['psettings'] == 'clients' ? "selected='selected'" : '' }}>{!! __('labels.everyone_in_client') !!}</option>
                                        <option value="all" {{ $project['psettings'] == 'all' ? "selected='selected'" : '' }}>{!! __('labels.everyone_in_org') !!}</option>
                                    </x-global::forms.select>

                                </div>
                            </div>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </x-global::layout.content-box>
</div>

@once @push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {


        leantime.projectsController.initProjectTabs();
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        }
    );

</script>
@endpush @endonce

@endsection
