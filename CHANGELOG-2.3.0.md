# Leantime 2.3.0

This file documents the changes made between version 2.2 and 2.3 of of the **Leantime&trade;** project.


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
- Customization support for languages, templates, and class repositories
- XML import and export of canvas
- Re-organized the directory structure to support `themes` and `plugins`


## Basic principles underlying design decision
1. The UX/UI design is key for software user buy-in. A poor layout will never make up for great content!
2. The definition and layout of canvases is part of the configuration of the system and not part of the day-to-day
   activity of using the system
3. A well-defined and supported process is preferred over an overwhelming choice of options


## Some details of changes
*Numbering shown is used to didentify specifc commits on github (diderich/leantime, branch dts)*

### New canvas implementation
- Refactored canvas code into a generic library of extendable classes and includable templates. Generic code is in
  `src/domain/canvas` (with a template for a new canvas in `src/domain/canvas/NEWcanvas`)
- Canvas details are defined in `domain/repository/XXcanvas`, rather than in templates (allowing specification to be
  used for screen layout and PDF generation)
- Added optional second drop-down called `relates`allowing to relate an element/box to a specific concept (e.g.,
  relating a strength in a SWOT analysis to the firms' Capabilities)
- Added separate access to comments from element/box (without the full dialogue)
- Added option to clone/copy existing canvas
- Added option to merge an existing canvas into the current canvas, allowing to consolidate work from multiple users
- Added selectors that allow to show sub-sets of elements/boxes in a canvas, based on drop-down values (e.g., only
  showing elements/boxes that have been validated)
- Added icons and colour (both are optional) to elements and removed option to change titles by the user
- Added system independent XML import and export functionality

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
- Added script in `tools/mltranslate` to translate messages using DeepL.com AI algorithm
- DeeplL is preferred over Google Translate because of the translation quality
- Use requires a (free) API key.
- Only not yet translated text strings are translated 

### Customization
The system, up to now, allowed custoization at two layers
- Through the user interface: `Company Setting`
- Through editing the configuration file: `config/configuration.php`

Any other change to the system is potentially overwritten when installing a new release. Sometimes, it may be sound to
configure the level at a deeper layer. Therefore, the possibility to customize languages, templates, and classes in the
`src` directory has been added. The system will first look under `custom`, if it can find the respective files, before
loading them from the original location. When updating the system, no files in the subdirectories of `custom` will be
overwritten, allowing to retain customizations made (subject to the disclaimer below.

Although it is highly disrecommended to customize *Leantime* using this new options, there exist some scenarios where
such a customization may b sound:
- An organization may use different wordings, e.g., *deliverables* instead of *milestone*, *activity item* instead of
  *to-do*, *engagement* instead of *project*, etc. By copying the original language file `xx-XX.ini` into the directory
  `custom/language` and editing it there, the system will honor any changes made.
- An organization wants to have a specific menu structure (e.g. only showing a subset of canvas or using a different
  ordering or adding an additional menu structure). Ths can be achieved by copying the file
  `src/domain/menu/repositories/class.menu.php` to `custom//domain/menu/repositories/` and making the appropriate changes.
- An organization may want to use different layouts of the canvas, e.g., have *Customers* to the right in the
  *Lightweight Business Model*, consistent with the *Original Business Model Canvas*. Copying the file
  `src/domain/templates/showCanvas.tpl.php` into the directory `custom/domain/templates/` and editing it there, allows
  the system to honor any changes made.
- Ad organization may want to disable some of the status/relates to items or add new ones is specific templaces. Again,
  copying the file `src/domain/XXXcanvas/repositories/class.XXXcanvas.php` to `custom/domain/XXXcanvas/repositories/`
  and making the change there allows the system to honor any chages made.

**DISCLAIMER**: *NEW RELEASES MAY (AND PROBABLY WILL) BREAK CUSTOMIZATIONS. THE USER IS THERFORE RESPONSIBLE FOR BACK-PORTING
ANY CHANGE MADE IN THE OFFICIAL DISTRIBUTION TO THE CUSTOMIZED FILES.*

### System related changes
- Added `Makefile` to minify/compile `js` and `css` files on a need to do basis
- Added this file `CHANGELOG-2.3.0H.md`
- Added `ISSUES.md` listing major open issues
- Adjusted `createReleasePackage.sh` to remove AI translation engine
  

## Change log (incomplete)

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

## 0.0.20 2022-10-21
- Add: Machine learning driven translation of stored as `resources/languages/mltranstlate/*.tra` for the languages de,
  es, fr, it, ja, nl, pt-BR, pt-PT, ru, tr, and zh-CH
- Update: Reviewed french translations and installed them as `fr-FR.ini`

## 0.0.21 2022-10-22
- Update: Made API controller of canvas classes extended/generic
- Update: Removed tabs in files

## 0.0.22 2022-10-22
- Update: Allow `pdf.php` to handle non-canvas boards (through passing its `type` when calling the URL
- Add: Put confidential disclaimers on all PDFs by default


# 0.1 - Customization option added

## 0.1.23 2022-10-22
- Add: Add the option to customize/override any language file, template, or class by putting it into `config/language`,
  `config/domain/*/template`, or `config/domain/*/*` respectively [see note on customization]

## 0.1.24 2022-10-22
- Update: Added telemetry data

## 0.1.25 2022-10-24
- Update: Changed icon from `caret-down` to `angle-down` in current project menu. Convention: Use `angle` for items that
  toggle open/clode and use `caret` for items that pop-up. Changed direction of `caret` for current project menu pop-up
  pointing to where it appears.
- Bug: Corrected icon used next to New menu in To-Dos.

## 0.1.26 2022-10-25
- Update: Move PDF generation from standalone function `/pdf.php` into controller function.


# 0.2 - Import / Export functionality added

## 0.2.27 2022-10-25
- Add: Added functionality to import and export canvas as XML files

## 0.2.28 2022-10-26
- Update/Add: Allow stylesheet and JavaScript configuration by including `public/config/custom.css` and
  `public/config/custom.js` if they exist. These files will not be overwritten by a new installation
  
## 0.2.29 2022-10-27
- Add: Added `slimselect` JavaScript library to style selectors and make them searchable. Replace default selector to
  select canvas/kanban (https://github.com/brianvoe/slim-select) *MIT License*

## 0.2.30 2022-10-28
- Update: Show status and relates icon on `canvasDialog` screen using `innerHTML` option from SlimSelect (Note: Status
  and relates could even be colord as on the `showCanvas` page, but that does not look good).
  
## 0.2.31 2022-10-29
- Add: Add support for US Legal paper size (see `vendor/yetiforce/yetiforcepdf/lib/Page.php` for supported page sizes)

## 0.2.32 2022-10-29
- Add: Added option to merge an selected canvas into an existing one, allowing to consolidate work from multiple users

## 0.2.33 2022-10-29
- Bug: Applied upstream change to correctly handle URLs associates with `download.php`
  (`template::convertRelativePaths`) in `elements.inc.php` and `class.pdf.php`
- Bug: Return error image in `download.php`, if user is not authenticated (`/images/leantime-no-access.jpg`)

# 0.3.34 2022-10-30
- Add: Added skeleton for handling themes (functionality required to implement new directory structure)

# 0.3.35 2022-10-30
- Update: Updated directory structure for handling themes
- Updated: Changed code to always use default theme for login in either the default language or the language of the
  browser
- Update: Languages are now cached theme specific, as each theme can overwrite language strings

# 0.3.36 2022-11-01
- Update: Re-enabled option to use previous theme/language for login screen (can be set in configuration: $keepTheme)
  using cookies
- Bug: Set cookie to path in which the application is installed, rather than the root path (to avoid conflicts with
  other applications installed on the same server)
