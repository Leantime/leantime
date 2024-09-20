import jQuery from 'jquery';

export const companyColor = jQuery('meta[name=theme-color]').attr('content');
export const colorScheme = jQuery('meta[name=color-scheme]').attr('content');
export const theme = jQuery('meta[name=theme]').attr('content');
export const appUrl = jQuery('meta[name=identifier-URL]').attr('content');
export const version = jQuery('meta[name=leantime-version]').attr('content');

export default {
    companyColor: companyColor,
    colorScheme: colorScheme,
    theme: theme,
    appUrl: appUrl,
    version: version
};
