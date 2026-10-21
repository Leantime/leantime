<form action="" method="post" class="stdform">

    <div class="row">

        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <x-global::forms.text-input width="full" variant="headline" name="name" id="name" value="{{ $project['name'] }}" placeholder="{{ __('input.placeholders.enter_title_of_project') }}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p>
                        {!! __('label.accomplish') !!}
                        <br /><br />
                    </p>
                    <textarea name="details" id="details" class="tiptapComplex" rows="5" cols="50">{{ $project['details'] }}</textarea>
                </div>
            </div>
            <div class="row padding-top">
                <div class="col-md-12">

                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" id="save" />
                </div>

            </div>
        </div>
        <div class="col-md-4">

            <div class="row marginBottom">



                @if ($projectTypes && count($projectTypes) > 1)
                <div class="col-md-12 center">
                    <x-global::elements.section-header icon="fa-regular fa-rectangle-list">{{ __('label.project_type') }}</x-global::elements.section-header>
                    <p>{{ __('text.project_type_help') }}</p>
                    <x-global::forms.select enhanced name="type">
                        @foreach ($projectTypes as $key => $type)
                            <option value="{{ $tpl->escape($key) }}"
                            @if ($project['type'] == $key)
                                selected='selected'
                            @endif
                            >{!! __($tpl->escape($type)) !!}</option>
                        @endforeach
                    </x-global::forms.select>
                    <br /><br />
                </div>
                @endif


            </div>
            <div class="row marginBottom">

                <div class="col-md-12 center">

                    <x-global::elements.section-header icon="fa fa-picture-o">{!! __('label.project_avatar') !!}</x-global::elements.section-header>

                    <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project['id'] }}&v={{ format($project['modified'])->timestamp() }}'  class='profileImg' alt='{{ __('headlines.profile_picture') }}' id="previousImage"/>
                    <div id="projectAvatar">
                    </div>

                    <div class="par">

                        <div class='fileupload fileupload-new' data-provides='fileupload'>
                            <input type="hidden"/>
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="fa-file fileupload-exists"></i>
                                    <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                        <span class="fileupload-new">{!! __('buttons.select_file') !!}</span>
                                        <span class='fileupload-exists'>{!! __('buttons.change') !!}</span>
                                        <input type='file' name='file' onchange="leantime.projectsController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.projectsController.clearCroppie()">{!! __('buttons.remove') !!}</a>
                            </div>

                            <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                <span onclick="leantime.projectsController.saveCroppie()">{!! __('buttons.save') !!}</span>
                                <span class="ld ld-ring ld-spin"></span>
                            </span>
                        <input type="hidden" name="profileImage" value="1" />
                        <input id="picSubmit" type="submit" name="savePic" class="hidden"
                               value="{{ __('buttons.upload') }}"/>

                        </div>
                    </div>
                </div>

            </div>

                @dispatchEvent('afterProjectAvatar', $project)

                <div class="row marginBottom" style="margin-bottom: 30px;">
                    <div class="col-md-12">
                        <x-global::elements.section-header icon="fa fa-calendar">{!! __('label.project_dates') !!}</x-global::elements.section-header>


                        <x-global::forms.label>{!! __('label.project_start') !!}</x-global::forms.label>
                        <div class="">
                            <input type="text" class="field-width-sm dates" name="start" autocomplete="off"
                                   value="{{ format($project['start'])->date() }}" placeholder="{{ __('language.dateformat') }}"/>

                        </div>
                        <x-global::forms.label>{!! __('label.project_end') !!}</x-global::forms.label>
                        <div class="">
                            <input type="text" class="field-width-sm dates" name="end" autocomplete="off"
                                   value="{{ format($project['end'])->date() }}" placeholder="{{ __('language.dateformat') }}"/>

                        </div>
                    </div>

                </div>


                <div class="row" style="margin-bottom: 30px;">

                    <div class="col-md-12 " style="margin-bottom: 30px;">
                    <x-global::elements.section-header icon="fa fa-building">{!! __('label.client_product') !!}</x-global::elements.section-header>
                    <x-global::forms.select enhanced name="clientId" id="clientId" aria-label="{{ __('label.client_product') }}">

                        @foreach ($clients as $row)
                            <option value="{{ $row['id'] }}"
                                @if ($project['clientId'] == $row['id'])
                                    selected=selected
                                @endif
                            >{{ $row['name'] }}</option>
                        @endforeach

                    </x-global::forms.select>
                    @if ($login::userIsAtLeast('manager'))
                        <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank">{!! __('label.client_not_listed') !!}</a>
                    @endif


                </div>
                </div>



            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12">
                    <x-global::elements.section-header icon="fa fa-wrench">{!! __('label.settings') !!}</x-global::elements.section-header>

                    <div class="form-group">

                <x-global::forms.label class="col-md-4 control-label" for="projectState">{!! __('label.project_state') !!}</x-global::forms.label>
                <div class="col-md-6">
                    <x-global::forms.select enhanced name="projectState" id="projectState">
                        <option value="0" @if ($project['state'] == 0) selected=selected @endif>{!! __('label.open') !!}</option>

                        <option value="-1" @if ($project['state'] == -1) selected=selected @endif>{!! __('label.closed') !!}</option>

                    </x-global::forms.select>
                </div>
            </div>

                </div>
            </div>

            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
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

            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
                    <x-global::elements.section-header icon="fa fa-money-bill-alt">{!! __('label.budgets') !!}</x-global::elements.section-header>
                    <div class="form-group">
                        <x-global::forms.label class="col-md-4 control-label" for="hourBudget">{!! __('label.hourly_budget') !!}</x-global::forms.label>
                        <div class="col-md-6">
                            <x-global::forms.text-input name="hourBudget" id="hourBudget" value="{{ $project['hourBudget'] }}" />

                        </div>
                    </div>

                    <div class="form-group">
                        <x-global::forms.label class="col-md-4 control-label" for="dollarBudget">{!! __('label.budget_cost') !!}</x-global::forms.label>
                        <div class="col-md-6">
                            <x-global::forms.text-input name="dollarBudget" id="dollarBudget" value="{{ $project['dollarBudget'] }}" />

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>


</form>
