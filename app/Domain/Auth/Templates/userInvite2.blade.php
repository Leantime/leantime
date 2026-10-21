@extends($layout)

@section('content')

    <x-auth::onboardingProgress current="theme" />

<h2>{{ __('titles.determine_visual_experience') }}</h2>
<p>{{ __('text.choose_a_theme_and_font_easy_to_read') }}</p>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="2" />

        {{  $tpl->displayInlineNotification() }}

        <div class="row-fluid">
            <div class="form-group">
                <x-global::forms.label for="themeSelect">{{ __('label.optimal_stimulation') }}</x-global::forms.label>
                <span class='field tw-flex'>

                     <?php
                     $themeAll = $themeCore->getAll();
                     foreach ($themeAll as $key => $theme) { ?>
                         <x-global::selectable selected="{{ ($userTheme == $key ? 'true' : 'false') }}" :id="''" :name="'theme'" :value="$key" :label="''" class="tw-w-1/2" onclick="leantime.snippets.toggleBg('{{ $key }}')">
                            <img src="{{ BASE_URL }}/dist/images/background-{{$key}}.png" style="margin:0; border-radius:10px;" />
                                 <br /><?= $tpl->__($theme['name']) ?>
                         </x-global::selectable>

                    <?php } ?>
                </span>
            </div>
            <br />
            <div class="form-group">
                <x-global::forms.label>{{ __('label.readability') }}</x-global::forms.label>
                <div class="tw-flex">
                    @foreach($availableFonts as $key => $font)

                        <x-global::selectable  data-tippy-content="{{ $fontTooltips[$key] }}" :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                            <x-global::forms.label for="selectable-{{ $key }}" class="font tw-w-[150px]"
                                   style="font-family:'{{ $font }}'; font-size:16px;">
                                {{ __('text.font_preview_sentence') }}
                            </x-global::forms.label>
                        </x-global::selectable>

                    @endforeach
                </div>

            </div>

        </div>
        <br />
        <div class="tw-text-right">
            <x-global::forms.button tag="a" link="{{BASE_URL}}/auth/userInvite/{{$inviteId}}" contentRole="tertiary" style="width:auto; margin-right:10px">{{ __('buttons.back') }}</x-global::forms.button>
            <input type="submit" name="createAccount" class="tw-w-auto" style="width:auto" value="<?php echo $tpl->language->__("buttons.next"); ?>" />
        </div>


    </form>

</div>

@endsection
