# Leantime DTS branch

The `dts` branch of the **Leantime&trade;** project implements views and canvas to allow implementing the *Design
Thinking for Strategy* process from the book with the same name [https://inov.at/dts-sn](https://inov.at/dts-sn).


## Major changes

- New canvas:
  - *SWOT Analysis*
  - *Environment Analysis*
  - *Risk Analysis*
  - *Empathy Map*
  - *Observe / Learn - Insights*
  - *Strategy Brief*
  - *Lightweight Business Model*, *Osterwalder's Business Model*, and *Detailed Business Model*
  - *Porter's Strategy Questions*
  - *Competitive Positioning Canvas*
  - *Strategy Messaging*
  - Note: Due to copyright restrictions, the *Value Proposition Canvas* cannot be included:
    https://strategyzer.uservoice.com/knowledgebase/articles/506842-can-i-use-the-business-model-canvas-or-value-propo 
- Revised canvas:
  - *Lean Canvas* (full version only)
  - *Retrospective* (called `retroscanvas`)
- Generate PDF files from any canvas
- Support for easily configurable multi-layer menu structure (support for project-specific menu structures can be enabled
  via configuration)
- Refactored generic canvas code in `src/domain/canvas` allowing to create a new canvas by simply extending/including
  code
- Engine to translate language string using AI/machine learning from https://www.deepl.com (registration needed, 500'000
  characters per month are free)


## Version

Leantime DTS Branch 0.0.20


## Author

Dr. Claude Diderich (diderich@yahoo.com)


## Basic principles underlying design decision
1. The UX/UI design is key for software user buy-in. A poor layout will never make up for great content!
2. The definition and layout of canvases is part of the configuration of the system and not part of the day-to-day
   activity of using the system
3. A well-defined and supported process is preferred over an overwhelming choice of options


## Some details of changes

### New canvas implementation
- Refactored canvas code into a generic library of extendable classes and includable templates. Generic code is in
  `src/domain/canvas` (with a template for a new canvas in `src/domain/canvas/NEWcanvas`)
- Canvas details are defined in `domain/repository/XXcanvas`, rather than in templates (allowing specification to be
  used for screen layout and PDF generation)
- Added optional second drop-down called `relates`allowing to relate an element/box to a specific concept (e.g.,
  relating a strength in a SWOT analysis to the firms' Capabilities)
- Added separate access to comments from element/box (without the full dialogue)
- Added option to clone/copy existing canvas
- Added selectors that allow to show sub-sets of elements/boxes in a canvas, based on drop-down values (e.g., only
  showing elements/boxes that have been validated)
- Added icons and colour (both are optional) to elements and removed option to change titles by the user

### Ideas Kanban
The following changes have been applied to the *Ideas* Kanban to make the user experience similar to that of canvas:
- Menu naming for creating new/editing idea Kanbans. Moving the delete menu next to create
- Number of comments per element is shown in the same way as in canvas (but cannot be clicked to add/edit comments)

### Generating PDF files from canvas
- Added `yetiforce/yetiforcepdf` library in composer for rendering PDF files (available via *MIT License*)
- Added print-ready `Roboto` and `RobotoCondensed` from (https://fonts.google.com/specimen/Roboto) (available via
  *Apache2* license)
- Added functionality to generate PDF reports from templates (visual canvas report containing summary information and
  detailed list report)

### Menu structure
- Menu structure has been separated from menu rending/layout. Menu structure is defined in `domain/repositories/menu`
- Menu structure supports two layers, second layer can be shown/hidden by the user
- Support for project specific menu structures added. It can be enabled through configuration (`$config->enableMenuType`)

### Translation
- Added script in `resources/language/mltranslate` to translate messages using DeepL.com AI algorithm
- DeeplL is preferred over Google Translate because of the translation quality
- Use requires a (free) API key.
- Only not yet translated text strings are translated 

### System related changes
- Added `Makefile` to minify/compile `js` and `css` files on a need to do basis
- Added this file `CHANGELOG-DTS-BRANCH.md`
- Adjusted `createReleasePackage.sh` to remove AI translation engine
  

## Open issues
- Due to the lack of support for lists (`<ul>`, ..) in *YetiForcePDF*, rendering lists in PDF is limited
- Complex tables may render poorly due to *YetiForcePDF* incorrectly handing some table borders and line breaks in nested tables
- If an image file/URL cannot be accessed during PDF report generating, the resulting PDF report is compromised
- If the menu structure is larger than the browser's height, some menu items will not be displayed (and thus not
  accessible) #1061
- Print and clone functionality are not available for `ideas` Kanban
- Filtering on status is not available for `ideas` Kanban
- Documentation of step-by-step how to create a new canvas and add it to the system missing
- New language tags have been generated for most languages, but need validation before being installed (they can be
  found in `resources/language/mltranslate`)


## Change log

## 0.0.1 - 2022-10-12
- New: Documented changes in `CHANGELOG_DTS_BRANCH.md` (commits are numbered with version)
- New: Added `Makefile` to only compile/minify `js` and `css` files when changed
- New: Added `yetiforce/yetiforcepdf` library in composer
- New: Added print-ready `Roboto` and `RobotoCondensed` fonts in `public/fonts/roboto/`from (https://fonts.google.com/specimen/Roboto)

## 0.0.2 - 2022-10-12
- New: Added column `type MEDIUMTEXT` to table `zp_projects`. Column allows storing which process is associated with a
  given project. Supported values are `NULL` (Generic), `lean` (Lean), and `dts` (DTS).
- New: Added functionality to associated a process type to a project when creating a new or modifying an existing
  project. Currently *Generic*, *Lean Startup*, and *Design Thinking for Strategy* are supported.

## 0.0.3 - 2022-10-12
- Update: Update menu structure, adding new canvas and boards
- New: Library for extendable classes and includable templates for `canvas` and `pdf` as `src/library`
- New: Engine to generate PDF files from templates (`public.pdf`)
- New: Repositoriy `dts` for creating default templates and milestones recommended by DTS process (section 5.1.)

## 0.0.4 - 2022-10-13
- Update: Prepared `api` and `helper` files for new templates
- New: Added *Strategy Brief* canvas and pdf functionality

## 0.0.5 - 2022-10-13
- New: Added *Business Model Canvas* canvas and pdf functionality

## 0.0.6 - 2022-10-13
- New: Added *Porter's Five Strategic Questions* and *Strategy Message* canvas and pdf functionality

## 0.0.7 - 2022-10-15
- Update: Make use of project type configurable (`$config->enableProjectType`)
- New: Added `relates` field to database table `zp_canvas_items` to allow relating an element
- New: Added `relatesLabels` to canvas repository class
- Updated: Moved library of extendable classes from `src/library` to `src/domain/canvas` and sib-directories
- Updated: Made canvas and label definition variables in canvas repository class ony accessible through functions

## 0.0.8 - 2022-10-15
- Added: Template to be used for creating new canvas as `src/domain/canvas/NEwcanvas/...`
- Update: Only the *Strategy Brief* canvas has been updated for the new structure

## 0.0.9 - 2022-10-15
- New: Added configurable menu structure in `repositories/menu` based on menu type selectable on a project by project
  basis
  
## 0.0.10 - 2022-10-16
- Updated: Added sub-menus to menu structure and allow them be toggled. Added access control to menu structure.
- Bug: Corrected modal related bug in `xxCanvasController`
- Check: Checked `canvas` code using PHPMD and removed unused variables
- Add: Minor adjustments for adding additional templates

## 0.0.11 - 2022-10-16
- Add: *Observe / Learn - Insights* canvas added

## 0.0.12 - 2022-10-16
- Add: *Risk Analysis* canvas added

## 0.0.13 - 2022-10-17
- Add: Make submenus open/close persistent

## 0.0.14 - 2022-10-17
- Add: *Empathy Map*
- Add: *SWOT Analysis*
- Update: Make create/edit/delete board menu option in `insights` consistent with `canvas` boards (but missing `clone`
  and `print`)

## 0.0.15 2022-10-18
- Add: *Environmental Analysis* canvas
- Add: *Lightweight Business Model* canvas from Design Thinking for Strategy
- Update: Minor improvements of genric `domain/canvas`

## 0.0.16 2022-10-19
- Add: *Original Business Model* canvas from Osterwalder
- Add: *Detailed Business Model* canvas from Design Thinking for Strategy
- Add: *Competitive Positioning Canvas* from Design Thinking for Strategy
- Update: Remove dropdown icon from status and relates if the user has read-only access

## 0.0.17 2022-10-20
- Add: Added new composer module `deeplcom/deepl-php` to translate messages (removed from production version)
- Add: New script `resources/languages/mltranslate/mltranslate.php` which uses machine learning from DeepL.com to
  tanslate messages
- Update: *Lean Canvas* and added data migration
- Add: *Porter's Strategy Questions* canvas from Design Thinking for Strategy
- Add: *Strategy Message* canvas from Design Thinking for Strategy
- Add: *Value Proposition Canvas* from Osterwalder
- Update: Show number of comments consistent with canvas in ideas Kanban
- Update: Re-implemented *Retrospectives* and added data migration under new name `retrocanvas`

## 0.0.18 2022-10-21
- Update: Updated database update to reflect change of *Refletion* canvas name from `reflection` to `reflectscanvas`
  (this new name is required to allow using the generic `canvas`engine)
- Update: Moved PDF rendering from canvas specific PDF class to generic PDF class `domain/canvas/pdf/class.pdf.php`
- Update: Use colors when generating PDF file only when user has configured them

## 0.0.19 2022-10-21
- Update: Updated `grunt.js` by adding targets that allow minifying selectively
- Update: Added rules to `Makefile` to selectively minify js/css libraries
- Update: Added rules to automatically translate files

## 0.0.20 1011-10-21
- Add: Machine learning driven translation of stored as `resources/languages/mltranstlate/*.tra` for the languages de,
  es, fr, it, ja, nl, pt-BR, pt-PT, ru, tr, and zh-CH
- Update: Reviewed french translations and installed them as `fr-FR.ini`
