{{--
    The user's page background. Rendered in the page header and swapped out of band
    (oob = true) when the user changes their background in their settings.

    Variables: $themeBg (image URL), $themeBgCss (the URL as CSS string, Header::toCssString),
    $themeOpacity, $themeType ('gradient' | 'image').
--}}
<style id="backgroundImageSetter" @if(!empty($oob)) hx-swap-oob="true" @endif>
    {{-- Painted on the shell's background layer (.rightpanel:before spans the whole row below the
         header), so the image also sits behind the left menu. The selector outranks the themes'
         opacity rules for that layer. --}}
    @if(!empty($themeBg))
        .mainwrapper .overlay .rightpanel:before {
            background-image: url({!! $themeBgCss ?? '""' !!});
            opacity: {{ $themeOpacity }};
            mix-blend-mode: {{ $themeType == 'image' ? 'normal' : 'multiply' }};
            background-size: var(--background-size, cover);
        }
    @endif
</style>
