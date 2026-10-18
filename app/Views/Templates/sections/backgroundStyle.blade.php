{{--
    The user's page background. Rendered in the page header and swapped out of band
    (oob = true) when the user changes their background in their settings.

    Variables: $themeBg (image URL), $themeBgCss (the URL as CSS string, Header::toCssString),
    $themeOpacity, $themeType ('gradient' | 'image').
--}}
<style id="backgroundImageSetter" @if(!empty($oob)) hx-swap-oob="true" @endif>
    @if(!empty($themeBg))
            .rightpanel {
                background-image: url({!! $themeBgCss ?? '""' !!});
                opacity: {{ $themeOpacity }};
                mix-blend-mode: {{ $themeType == 'image' ? 'normal' : 'multiply' }};
                background-size: var(--background-size, cover);
                background-position: center;
                background-attachment: fixed;
            }

    @if($themeType === 'image')
        .rightpanel:before {
            background: none;
        }
    @endif
    @endif
</style>
