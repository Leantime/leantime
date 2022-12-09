# Known open issues

## Menu structure
- If the menu structure is larger than the browser's height, some menu items will not be displayed (and thus not
  accessible) #1061
  
## Themes
- The current logo is independent of the theme used. This may lead to issues if the theme uses as background color, the
  color of the logo
- Keeping the same theme/language from previous login at login screen is not working!

## Language translations
- language::getInstance tags have been generated for most languages, but need validation before being installed (they can be
  found in `/tools/mltranslate` and ar market with MTL., prefix which must be removed before installing the language file)
  
## PDF generation
- Due to the lack of support for lists (`<ul>`, ..) in the *YetiForcePDF* library, rendering lists in PDF is limited
- Complex tables may render poorly due to *YetiForcePDF* incorrectly handing some table borders and line breaks in nested tables
- If an image file/URL cannot be accessed during PDF report generating, the resulting PDF report is compromised
- The PDF library *YetiForcePDF* fails when loading images from web serves with self-signed certificates, as the certificate is
  validated against an official list

## Missing functionality
- Print and clone functionality are not available for `ideas` Kanban
- Filtering on status is not available for `ideas` Kanban
