# Generic Content Template System — Design

Draft for Marcel's review. Operationalizing his feedback on the StrategyPro sector-templates work.

## Problem

We have three parallel "template" notions in the codebase today, with different storage, different lifecycles, no shared infrastructure:

1. **Wiki templates** — hardcoded PHP arrays in a Blade view. One HTML blob per template (Meeting Notes, PRD). Inserted into the TipTap editor.
2. **Blueprints' `TemplateRegistry`** — YAML definitions for canvas types (SWOT, Lean, OBM). These declare the SCHEMA of a canvas type — which boxes exist, their icons, their titles.
3. **StrategyPro Logic Model sector fixtures** — JSON files. Pre-populate an LM board with example items by sector. Built in the StrategyPro plugin.

The first two predate the LM work. The third is new and currently plugin-side. Marcel's read on the recent sector-templates work: don't bake this into Blueprints' registry, but build a **generic file-based content-template system** that handles "canvas-typed" content (LM, goals, lean canvas, etc.) AND wikis, with Blueprints optionally referencing content templates rather than owning them. Vision: standardized format enables a future marketplace where users export their boards/wikis as importable templates.

## Marcel's vision (verbatim)

> "Would this be a concept we would repeat in other blueprints? Technically wiki templates could follow the same infrastructure. I don't think that I would make that part of the primary blueprint template but probably a file based template system that can be used for 'canvas typed' content. (even cross applying to goals for example: defining quarterly goals for startup or so or wikis with predefined content and multi page wikis). So I would think it's somewhere in between A & B? 'A' (the blueprint templates) could still have a reference to the start content template and load it with it. Or it's a secondary step in the process. think it's actually a good idea. What if we start offering a template marketplace? If we have a standardized format users can export their stuff as templates and put them on this marketplace. Could be lean canvas examples or so. But also wikis/wiki structures with pre-pared articles etc."

## Scope

### In scope
- A core domain for content templates (probably `app/Domain/ContentTemplates/`).
- File-based storage with a registry service.
- A schema that handles two consumer categories today: **canvas-typed items** (LM, goals, lean canvas, etc.) and **wiki content** (single article or multi-page tree).
- A plugin extension API so plugins can register their own templates without touching core (StrategyPro's LM sector templates being the first user).
- Migration path for the existing systems.

### Out of scope
- Marketplace UI itself (the schema is designed to enable it; building it is a separate effort).
- Versioning of applied content (if a template's definition changes after a user applied it, the user's data is untouched — they're snapshots at apply time).
- Project-scoped templates (per-org, per-user, etc.). v1 is system-wide. Per-scope filtering can layer on later.
- Replacing Blueprints' canvas-type definitions. Those stay as-is and describe SCHEMA. Content templates describe SEED CONTENT.

## Shape

### Storage

YAML files on disk, mirroring `Blueprints/Templates/definitions/*.yaml` so the pattern is familiar. Directory layout:

```
app/Domain/ContentTemplates/Library/
├── logicmodel/
│   ├── education-k12.yaml
│   ├── community-health.yaml
│   ├── workforce-development.yaml
│   └── ...
├── goal/
│   ├── startup-quarterly.yaml
│   └── ...
├── leancanvas/
│   └── ...
└── wiki/
    ├── meeting-notes.yaml
    ├── prd.yaml
    └── ...
```

Plugins can register additional library roots. StrategyPro's existing JSON fixtures get migrated to YAML and placed in `app/Plugins/StrategyPro/ContentTemplates/logicmodel/`, registered via the plugin's `register.php`.

### Schema

Single base shape with consumer-specific payload. The `appliesTo` field tells the registry which applier to use.

```yaml
key: "education-k12"                       # unique within (appliesTo, key)
title: "K-12 Education Program"
description: "After-school and tutoring programs improving student outcomes."

appliesTo: "logicmodel"                    # canvas type or "wiki"
sector: "education"                        # optional tag for grouping
icon: "fa-graduation-cap"                  # optional, for UI selectors
author: "Leantime"                         # for marketplace attribution
version: "1.0.0"                           # for marketplace versioning
license: "CC0"                             # for marketplace licensing

# ── For canvas-typed content (LM, goal, lean, etc.) ──
canvas:
  items:
    - box: "lm_inputs"
      title: "Federal Title I funding"
      description: "Annual allocation for supplemental educational services in high-need schools."
      status: "status_draft"
    # ...

# ── For wiki content ──
wiki:
  articles:
    - title: "Project Overview"
      content: "<h1>...</h1>"
      children:
        - title: "Subtopic A"
          content: "..."
```

Only ONE of `canvas:` or `wiki:` is present per template (determined by `appliesTo`).

### Registry

`ContentTemplateRegistry` service in core, mirroring `Blueprints/Services/TemplateRegistry`:

```php
namespace Leantime\Domain\ContentTemplates\Services;

class ContentTemplateRegistry
{
    /** Get a single template by appliesTo + key */
    public function get(string $appliesTo, string $key): ?ContentTemplate;

    /** All templates for a canvas type or "wiki" */
    public function forAppliesTo(string $appliesTo): array;

    /** All templates across all appliesTo */
    public function all(): array;

    /** Register an additional library root (plugin extension point) */
    public function registerLibraryRoot(string $absolutePath): void;
}
```

### Appliers

The registry holds DEFINITIONS. Applying a template to a target is a separate concern, one applier per target type:

- `CanvasItemsApplier::apply(int $canvasId, ContentTemplate $tpl, array $opts): int`
  - Inserts canvas_items rows. Handles add/replace/cancel like StrategyPro's existing `loadFixture` flow.
- `WikiApplier::apply(int $wikiId, ContentTemplate $tpl, array $opts): int`
  - Inserts article rows, handles multi-page nesting.

Appliers are registered by their domain (Canvas domain registers the canvas applier, Wiki domain registers the wiki applier). The registry routes by `appliesTo`.

### Plugin extension API

In a plugin's `register.php`:

```php
app(\Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry::class)
    ->registerLibraryRoot(__DIR__ . '/ContentTemplates');
```

Plugin's library directory mirrors the core layout (e.g., `StrategyPro/ContentTemplates/logicmodel/*.yaml`). The registry scans all registered roots at boot and merges.

## Migration path

Phased so nothing breaks at once:

| Phase | Move | Risk |
|---|---|---|
| 1 | Build `ContentTemplates` core domain (registry + canvas applier + wiki applier + base library directory). No consumers yet. | Low — pure addition. |
| 2 | Migrate StrategyPro's 5 sector JSON fixtures to YAML in the plugin's `ContentTemplates/logicmodel/`. Plugin's `register.php` registers the library root. Delete `BoardTemplate` HxController's bespoke loader; call the core applier instead. Selector partial reads from the registry. | Medium — touches a working flow but it's plugin-scoped. |
| 3 | Migrate Wiki templates from hardcoded PHP arrays in `Wiki/Templates/templates.blade.php` to YAML files in `app/Domain/ContentTemplates/Library/wiki/`. Wiki controller reads from the registry. Drop the hardcoded arrays. | Medium — touches user-facing wiki feature. Translator-touched content; rename keys carefully. |
| 4 | Optional: Blueprints' YAML definitions get a `startContent:` field that references a content-template key. When a user creates a new SWOT or Lean Canvas, the registered start content (if any) auto-applies on creation. | Low — Blueprints registry stays as-is, just gains a hook. |

Each phase is shippable independently.

## Marketplace alignment

Standardized format unlocks export/import without much additional design:

- **Export**: any user's board or wiki can serialize to the same YAML shape. UI button on the board/wiki: "Save as template" → produces a downloadable .yaml or .zip of yamls.
- **Import**: drop a .yaml in a library directory (manual today, marketplace UI later) and it's discoverable.
- **Marketplace plugins**: bundle template packs as plugin assets. Plugin's `ContentTemplates/` directory is auto-registered. Buying a "Nonprofit Strategy Bundle" plugin gets you 20 LM templates + 10 wiki templates + 5 goal templates.
- **Attribution + licensing**: `author`, `version`, `license` fields in the YAML carry through, surface in the selector UI.

## Open questions for Marcel

1. **YAML vs JSON**: Blueprints uses YAML, StrategyPro uses JSON. Picking YAML for the new system aligns with the existing core precedent and reads cleaner for content. Agreed?
2. **Library directory naming**: `ContentTemplates/Library/` vs `ContentTemplates/Definitions/` (matching Blueprints' `Templates/definitions/`)? Slight preference for `Library` since "definitions" reads as schema/structure to me.
3. **`appliesTo` taxonomy**: should it be free-form strings ("logicmodel", "wiki", "leancanvas", "goal") or an enum? Free-form is easier for plugins to add canvas types, enum is safer. Recommend free-form for v1.
4. **Add/Replace/Cancel default behavior on existing content**: StrategyPro's current flow surfaces a 3-option confirmation when the target has items. Carry this forward as the applier's default? Or apply-time option?
5. **Per-language content**: should templates themselves be translatable, or is the user expected to translate after applying? Recommend latter — templates are starter content, user customizes anyway.
6. **Versioning of applied content**: if a template definition changes after a user applied it, the user's data is untouched (no auto-update). Confirming this is the right call for v1 — it matches the generator pattern (Rails scaffold, etc.).
7. **Permissions**: who can apply a template — editor? owner? Same as creating a board today? Default: anyone who can edit the target canvas/wiki can apply.

## What this changes about the in-flight work

- **StrategyPro PR #46 doesn't need to wait**. The sector-template work that landed there is plugin-scoped and uses internal interfaces — it remains the v1 user-facing experience. Phase 2 of this design (migrate StrategyPro's fixtures to the core registry) ships AFTER this design is approved and Phase 1 of the core domain is built.
- The per-sector icon polish I had uncommitted gets re-applied as part of Phase 2 cleanup, in the new directory layout.
- No core PR opens until this design is approved.

## Recommendation

Approve the broad shape, then sequence:

- Phase 1 (~2-3 days): core `ContentTemplates` domain + registry + canvas applier + wiki applier + plumbing tests.
- Phase 2 (~1-2 days): StrategyPro migration. Stops at parity with what we have today.
- Phase 3 (~1-2 days): Wiki migration. Adds genuine new UX since wiki templates today are inflexible.
- Phase 4 (~0.5 day): Blueprints' optional `startContent:` reference.
- Marketplace surfaces: separate roadmap entry, not part of this design.

Total: roughly a week of focused work, ships as four independent PRs.

What's the read?
