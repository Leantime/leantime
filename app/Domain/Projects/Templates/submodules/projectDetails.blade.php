@php
    use Leantime\Domain\Menu\Repositories\Menu;

    $project = $tpl->get('project');
    $menuTypes = $tpl->get('menuTypes');
@endphp

<form action="" method="post" class="stdform">

    <div class="row">

        <div class="col-md-8">
            <div class="form-group">
                <x-globals::forms.input :bare="true" type="text" name="name" id="name" class="main-title-input" style="width:99%" value="{{ e($project['name']) }}" placeholder="{{ __('input.placeholders.enter_title_of_project') }}" />
            </div>

            <p>
                {{ __('label.accomplish') }}
                <br /><br />
            </p>
            <textarea name="details" id="details" class="tiptapComplex" rows="5" cols="50">{!! htmlentities($project['details']) !!}</textarea>

            <div class="padding-top">
                <x-globals::forms.button submit type="primary" name="save" id="save">{{ __('buttons.save') }}</x-globals::forms.button>
            </div>
        </div>
        <div class="col-md-4">

            <div class="marginBottom">

                @if($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1)
                <div class="text-center">
                    <h4 class="widgettitle title-light"><i class="fa-regular fa-rectangle-list"></i> Project Type</h4>
                    <p>The type of the project. This will determine which features are available.</p>
                    <x-globals::forms.select name="type">
                        @foreach($tpl->get('projectTypes') as $key => $type)
                            <option value="{{ e($key) }}"
                                {{ $project['type'] == $key ? "selected='selected'" : '' }}>{{ __( e($type)) }}</option>
                        @endforeach
                    </x-globals::forms.select>
                    <br /><br />
                </div>
                @endif

            </div>
            <div class="marginBottom">

                <div class="text-center">

                    <h4 class="widgettitle title-light"><span
                            class="fa fa-picture-o"></span>{{ __('label.project_avatar') }}</h4>

                    <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project['id'] }}&v={{ format($project['modified'])->timestamp() }}' class='profileImg' alt='Profile Picture' id="previousImage"/>
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
                                    <span class="fileupload-new">{{ __('buttons.select_file') }}</span>
                                    <span class='fileupload-exists'>{{ __('buttons.change') }}</span>
                                    <x-globals::forms.file :bare="true" name="file" accept=".jpg,.png,.gif,.webp" onchange="leantime.projectsController.readURL(this)" />
                                </span>

                                <x-globals::forms.button link="#" type="secondary" class="fileupload-exists" data-dismiss="fileupload" onclick="leantime.projectsController.clearCroppie()">{{ __('buttons.remove') }}</x-globals::forms.button>
                            </div>

                            <x-globals::forms.button tag="button" type="primary" id="save-picture" class="fileupload-exists" onclick="leantime.projectsController.saveCroppie()">{{ __('buttons.save') }}</x-globals::forms.button>
                        <input type="hidden" name="profileImage" value="1" />
                        <input id="picSubmit" type="submit" name="savePic" class="hidden"
                               value="{{ __('buttons.upload') }}"/>

                        </div>
                    </div>
                </div>

            </div>

            @dispatchEvent('afterProjectAvatar', $project)

            <div class="marginBottom" style="margin-bottom: 30px;">
                <h4 class="widgettitle title-light"><span
                        class="fa fa-calendar"></span>{{ __('label.project_dates') }}</h4>

                <label class="control-label">{{ __('label.project_start') }}</label>
                <div class="">
                    <x-globals::forms.date name="start" value="{{ format($project['start'])->date() }}" placeholder="{{ __('language.dateformat') }}" style="width:100px;" />
                </div>
                <label class="control-label">{{ __('label.project_end') }}</label>
                <div class="">
                    <x-globals::forms.date name="end" value="{{ format($project['end'])->date() }}" placeholder="{{ __('language.dateformat') }}" style="width:100px;" />
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h4 class="widgettitle title-light"><span
                        class="fa fa-building"></span>{{ __('label.client_product') }}</h4>
                <x-globals::forms.select name="clientId" id="clientId">
                    @foreach($tpl->get('clients') as $row)
                        <option value="{{ $row['id'] }}"
                            {{ $project['clientId'] == $row['id'] ? 'selected=selected' : '' }}>{{ e($row['name']) }}</option>
                    @endforeach
                </x-globals::forms.select>
                @if($login::userIsAtLeast('manager'))
                    <br /><a href="{{ BASE_URL }}/clients/newClient" target="_blank">{{ __('label.client_not_listed') }}</a>
                @endif
            </div>

            <div class="marginBottom" style="margin-bottom: 30px;">
                <h4 class="widgettitle title-light"><span
                        class="fa fa-wrench"></span>{{ __('label.settings') }}</h4>

                <input type="hidden" name="menuType" id="menuType"
                       value="{{ Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU }}">

                <div class="form-group">
                    <label class="control-label" for="projectState">{{ __('label.project_state') }}</label>
                    <div>
                        <x-globals::forms.select name="projectState" id="projectState">
                            <option value="0" {{ $project['state'] == 0 ? 'selected=selected' : '' }}>{{ __('label.open') }}</option>
                            <option value="-1" {{ $project['state'] == -1 ? 'selected=selected' : '' }}>{{ __('label.closed') }}</option>
                        </x-globals::forms.select>
                    </div>
                </div>

            </div>

            <div class="marginBottom" style="margin-bottom: 30px;">
                <h4 class="widgettitle title-light"><span
                            class="fa fa-lock-open"></span>{{ __('labels.defaultaccess') }}</h4>
                {{ __('text.who_can_access') }}
                <br /><br />

                <x-globals::forms.select name="globalProjectUserAccess" style="max-width:300px;">
                    <option value="restricted" {{ $project['psettings'] == 'restricted' ? "selected='selected'" : '' }}>{{ __('labels.only_chose') }}</option>
                    <option value="clients" {{ $project['psettings'] == 'clients' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_client') }}</option>
                    <option value="all" {{ $project['psettings'] == 'all' ? "selected='selected'" : '' }}>{{ __('labels.everyone_in_org') }}</option>
                </x-globals::forms.select>

            </div>

            <div style="margin-bottom: 30px;">
                <h4 class="widgettitle title-light"><span
                            class="fa fa-money-bill-alt"></span>{{ __('label.budgets') }}</h4>
                <div class="form-group">
                    <label class="control-label" for="hourBudget">{{ __('label.hourly_budget') }}</label>
                    <div>
                        <x-globals::forms.input name="hourBudget" id="hourBudget" value="{{ e($project['hourBudget']) }}" :bare="true" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="dollarBudget">{{ __('label.budget_cost') }}</label>
                    <div>
                        <x-globals::forms.input name="dollarBudget" id="dollarBudget" value="{{ e($project['dollarBudget']) }}" :bare="true" />
                    </div>
                </div>

            </div>

        </div>

    </div>

</form>
