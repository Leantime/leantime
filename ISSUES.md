# Known open issues

## Menu structure
- If the menu structure is larger than the browser's height, some menu items will not be displayed (and thus not
  accessible) #1061

## Language translations
- New language tags have been generated for most languages, but need validation before being installed (they can be
  found in `resources/language/mltranslate`)
  
## PDF generation
- Due to the lack of support for lists (`<ul>`, ..) in *YetiForcePDF*, rendering lists in PDF is limited
- Complex tables may render poorly due to *YetiForcePDF* incorrectly handing some table borders and line breaks in nested tables
- If an image file/URL cannot be accessed during PDF report generating, the resulting PDF report is compromised
- The PDF library *YetiForcePDF* fails when loading images from web serves with self-signed certificates, as the certificate is
  validated against an official list.

## Missing functionality
- Print and clone functionality are not available for `ideas` Kanban
- Filtering on status is not available for `ideas` Kanban

