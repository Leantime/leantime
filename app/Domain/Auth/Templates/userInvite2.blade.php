@extends($layout)

@section('content')

    @include("auth::partials.onboardingProgress", ['percentComplete' => 37, 'current' => 'theme', 'completed' => ['account']])

<h2>{{ __('titles.determine_visual_experience') }}</h2>
<p>{{ __('text.choose_a_theme_and_font_easy_to_read ') }}</p>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">
        <input type="hidden" name="step" value="2" />

        {{  $tpl->displayInlineNotification() }}

        <div class="row-fluid">
            <div class="form-group">
                <label for="themeSelect">Optimal Stimulation</label>
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
                <label>Readability</label>
                <div class="tw-flex">
                    @foreach($availableFonts as $key => $font)

                        <x-global::selectable  data-tippy-content="{{ $fontTooltips[$key] }}" :selected="($themeFont == $font) ? 'true' : ''" :id="$key" :name="'themeFont'" :value="$font" :label="$font" onclick="leantime.snippets.toggleFont('{{ $font }}')">
                            <label for="selectable-{{ $key }}" class="font tw-w-[150px]"
                                   style="font-family:'{{ $font }}'; font-size:16px;">
                                The quick brown fox jumps over the lazy dog
                            </label>
                        </x-global::selectable>

                    @endforeach
                </div>

            </div>

        </div>
        <br />
        <div class="tw-text-right">
            <a href="{{BASE_URL}}/auth/userInvite/{{$inviteId}}" class="btn btn-secondary" style="width:auto; margin-right:10px">Back</a>
            <input type="submit" name="createAccount" class="tw-w-auto" style="width:auto" value="<?php echo $tpl->language->__("buttons.next"); ?>" />
        </div>


    </form>

</div>

@endsection
