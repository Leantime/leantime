<x-globals::layout.page-header headline="Component Preview" />

<div style="max-width: 960px; margin: 0 auto;">

    <p style="margin-bottom: 20px; color: var(--primary-font-color);">
        DaisyUI 5 component reference. All DaisyUI classes use the <code>tw:</code> prefix (same as Tailwind utilities) to avoid conflicts with Bootstrap.
        For example: <code>class="tw:btn tw:btn-primary"</code>.
    </p>

    {{-- ============================================================ --}}
    {{-- BUTTONS --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Buttons</h3>

        <h4 style="margin: 15px 0 10px;">DaisyUI Buttons</h4>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
            <button class="tw:btn">Default</button>
            <button class="tw:btn tw:btn-primary">Primary</button>
            <button class="tw:btn tw:btn-secondary">Secondary</button>
            <button class="tw:btn tw:btn-accent">Accent</button>
            <button class="tw:btn tw:btn-info">Info</button>
            <button class="tw:btn tw:btn-success">Success</button>
            <button class="tw:btn tw:btn-warning">Warning</button>
            <button class="tw:btn tw:btn-error">Error</button>
            <button class="tw:btn tw:btn-ghost">Ghost</button>
            <button class="tw:btn tw:btn-link">Link</button>
            <button class="tw:btn tw:btn-outline">Outline</button>
            <button class="tw:btn" disabled>Disabled</button>
        </div>

        <h4 style="margin: 15px 0 10px;">Button Sizes</h4>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <button class="tw:btn tw:btn-primary tw:btn-xs">Extra Small</button>
            <button class="tw:btn tw:btn-primary tw:btn-sm">Small</button>
            <button class="tw:btn tw:btn-primary">Normal</button>
            <button class="tw:btn tw:btn-primary tw:btn-lg">Large</button>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- BADGES --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Badges</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <span class="tw:badge">Default</span>
            <span class="tw:badge tw:badge-primary">Primary</span>
            <span class="tw:badge tw:badge-secondary">Secondary</span>
            <span class="tw:badge tw:badge-accent">Accent</span>
            <span class="tw:badge tw:badge-info">Info</span>
            <span class="tw:badge tw:badge-success">Success</span>
            <span class="tw:badge tw:badge-warning">Warning</span>
            <span class="tw:badge tw:badge-error">Error</span>
            <span class="tw:badge tw:badge-outline">Outline</span>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- ALERTS --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Alerts</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div role="alert" class="tw:alert">
                <span>Default alert — check it out!</span>
            </div>
            <div role="alert" class="tw:alert tw:alert-info">
                <span>Info: New software update available.</span>
            </div>
            <div role="alert" class="tw:alert tw:alert-success">
                <span>Success: Item has been saved.</span>
            </div>
            <div role="alert" class="tw:alert tw:alert-warning">
                <span>Warning: Invalid email address.</span>
            </div>
            <div role="alert" class="tw:alert tw:alert-error">
                <span>Error: Something went wrong.</span>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- CARDS --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Card</h3>
        <div class="tw:card tw:bg-base-100 tw:shadow-sm" style="max-width: 400px;">
            <div class="tw:card-body">
                <h2 class="tw:card-title">Card Title</h2>
                <p>A card component using DaisyUI classes. Cards can hold any content.</p>
                <div class="tw:card-actions tw:justify-end">
                    <button class="tw:btn tw:btn-primary tw:btn-sm">Action</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- MODAL (x-globals::actions.modal component) --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Modal Component</h3>

        <h4 style="margin: 15px 0 10px;">Basic Modal</h4>
        <button class="tw:btn tw:btn-primary" onclick="document.getElementById('basic-modal').showModal()">Open Basic Modal</button>

        <x-globals::actions.modal id="basic-modal" title="Basic Modal">
            <p>This modal uses the <code>&lt;x-globals::actions.modal&gt;</code> component with native <code>&lt;dialog&gt;</code> and DaisyUI styling.</p>
            <x-slot:actions>
                <form method="dialog">
                    <button class="tw:btn">Close</button>
                </form>
            </x-slot:actions>
        </x-globals::actions.modal>

        <h4 style="margin: 15px 0 10px;">Large Modal with HTMX</h4>
        <button class="tw:btn tw:btn-secondary" onclick="document.getElementById('large-modal').showModal()">Open Large Modal</button>

        <x-globals::actions.modal id="large-modal" title="Large Modal" size="lg">
            <p>A larger modal (<code>size="lg"</code>) suitable for forms and detailed content.</p>
            <p style="margin-top: 10px;">Supports: <code>sm</code>, <code>md</code> (default), <code>lg</code>, <code>xl</code></p>
            <x-slot:actions>
                <form method="dialog">
                    <button class="tw:btn tw:btn-ghost">Cancel</button>
                    <button class="tw:btn tw:btn-primary">Save</button>
                </form>
            </x-slot:actions>
        </x-globals::actions.modal>

        <h4 style="margin: 15px 0 10px;">Non-closeable Modal</h4>
        <button class="tw:btn tw:btn-accent" onclick="document.getElementById('locked-modal').showModal()">Open Locked Modal</button>

        <x-globals::actions.modal id="locked-modal" title="Confirmation Required" :closeable="false">
            <p>This modal has <code>:closeable="false"</code> — no X button and no backdrop dismiss. User must interact with the action buttons.</p>
            <x-slot:actions>
                <button class="tw:btn tw:btn-ghost" onclick="document.getElementById('locked-modal').close()">Cancel</button>
                <button class="tw:btn tw:btn-primary" onclick="document.getElementById('locked-modal').close()">Confirm</button>
            </x-slot:actions>
        </x-globals::actions.modal>
    </section>

    {{-- ============================================================ --}}
    {{-- GLOBAL MODAL (hash-based, powered by modalManager.js) --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Global Modal (hash-based)</h3>
        <p style="margin-bottom: 10px; color: var(--primary-font-color);">
            Hash links like <code>href="#/module/action/id"</code> open content in the global
            <code>&lt;dialog id="global-modal"&gt;</code> via <code>modalManager.js</code>.
            Content is fetched with an <code>is-modal</code> header so the server returns just the template (blank layout).
        </p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="#/tickets/newTicket" class="tw:btn tw:btn-primary tw:btn-sm">Open New Ticket Modal</a>
            <button class="tw:btn tw:btn-secondary tw:btn-sm" onclick="leantime.modals.openByUrl(leantime.appUrl + '/tickets/newTicket')">Open via JS API</button>
            <button class="tw:btn tw:btn-ghost tw:btn-sm" onclick="leantime.modals.closeModal()">Close Modal</button>
        </div>
        <pre style="margin-top: 10px; padding: 12px; background: var(--secondary-background); border-radius: var(--box-radius-small); font-size: 12px; overflow-x: auto;"><code>{{-- Hash link (auto-detected by modalManager.js hashchange listener) --}}
&lt;a href="#/tickets/newTicket"&gt;Open in modal&lt;/a&gt;

{{-- JS API --}}
leantime.modals.openByUrl(url)
leantime.modals.closeModal()
leantime.modals.setCustomModalCallback(fn)</code></pre>
    </section>

    {{-- ============================================================ --}}
    {{-- TABS --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Tabs</h3>
        <div role="tablist" class="tw:tabs tw:tabs-bordered">
            <a role="tab" class="tw:tab">Tab 1</a>
            <a role="tab" class="tw:tab tw:tab-active">Tab 2</a>
            <a role="tab" class="tw:tab">Tab 3</a>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TOGGLE / CHECKBOX / RADIO --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Form Controls</h3>

        <h4 style="margin: 15px 0 10px;">Toggle</h4>
        <div style="display: flex; gap: 12px;">
            <input type="checkbox" class="tw:toggle" checked />
            <input type="checkbox" class="tw:toggle tw:toggle-primary" checked />
            <input type="checkbox" class="tw:toggle tw:toggle-secondary" checked />
            <input type="checkbox" class="tw:toggle tw:toggle-accent" checked />
        </div>

        <h4 style="margin: 15px 0 10px;">Checkbox</h4>
        <div style="display: flex; gap: 12px;">
            <input type="checkbox" class="tw:checkbox" checked />
            <input type="checkbox" class="tw:checkbox tw:checkbox-primary" checked />
            <input type="checkbox" class="tw:checkbox tw:checkbox-secondary" checked />
        </div>

        <h4 style="margin: 15px 0 10px;">Radio</h4>
        <div style="display: flex; gap: 12px;">
            <input type="radio" name="preview-radio" class="tw:radio" checked />
            <input type="radio" name="preview-radio" class="tw:radio tw:radio-primary" />
            <input type="radio" name="preview-radio" class="tw:radio tw:radio-secondary" />
        </div>

        <h4 style="margin: 15px 0 10px;">Text Input</h4>
        <div style="display: flex; flex-direction: column; gap: 10px; max-width: 320px;">
            <input type="text" placeholder="Default input" class="tw:input tw:input-bordered tw:w-full" />
            <input type="text" placeholder="Primary" class="tw:input tw:input-bordered tw:input-primary tw:w-full" />
            <select class="tw:select tw:select-bordered tw:w-full">
                <option disabled selected>Select an option</option>
                <option>Option 1</option>
                <option>Option 2</option>
            </select>
            <textarea class="tw:textarea tw:textarea-bordered" placeholder="Textarea"></textarea>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- PROGRESS / LOADING --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Progress &amp; Loading</h3>

        <h4 style="margin: 15px 0 10px;">Progress Bars</h4>
        <div style="display: flex; flex-direction: column; gap: 8px; max-width: 320px;">
            <progress class="tw:progress tw:progress-primary" value="25" max="100"></progress>
            <progress class="tw:progress tw:progress-secondary" value="50" max="100"></progress>
            <progress class="tw:progress tw:progress-accent" value="75" max="100"></progress>
        </div>

        <h4 style="margin: 15px 0 10px;">Loading Spinners</h4>
        <div style="display: flex; gap: 12px; align-items: center;">
            <span class="tw:loading tw:loading-spinner tw:loading-xs"></span>
            <span class="tw:loading tw:loading-spinner tw:loading-sm"></span>
            <span class="tw:loading tw:loading-spinner tw:loading-md"></span>
            <span class="tw:loading tw:loading-spinner tw:loading-lg"></span>
            <span class="tw:loading tw:loading-dots tw:loading-md"></span>
            <span class="tw:loading tw:loading-ring tw:loading-md"></span>
            <span class="tw:loading tw:loading-bars tw:loading-md"></span>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- TOOLTIP --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Tooltip</h3>
        <div style="display: flex; gap: 12px;">
            <div class="tw:tooltip" data-tip="Hello tooltip!">
                <button class="tw:btn">Hover me</button>
            </div>
            <div class="tw:tooltip tw:tooltip-primary" data-tip="Primary tooltip">
                <button class="tw:btn tw:btn-primary">Primary</button>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- STEPS --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Steps</h3>
        <ul class="tw:steps" style="width: 100%;">
            <li class="tw:step tw:step-primary">Register</li>
            <li class="tw:step tw:step-primary">Choose plan</li>
            <li class="tw:step">Purchase</li>
            <li class="tw:step">Receive Product</li>
        </ul>
    </section>

    {{-- ============================================================ --}}
    {{-- SKELETON --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Skeleton</h3>
        <div style="display: flex; flex-direction: column; gap: 8px; max-width: 320px;">
            <div class="tw:skeleton tw:h-4 tw:w-full"></div>
            <div class="tw:skeleton tw:h-4 tw:w-3/4"></div>
            <div class="tw:skeleton tw:h-32 tw:w-full"></div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- BLADE COMPONENT LIBRARY (Phase 2) --}}
    {{-- ============================================================ --}}

    <h2 style="margin: 40px 0 20px; padding-top: 20px; border-top: 3px solid var(--accent1); color: var(--accent1);">
        Blade Component Library
    </h2>
    <p style="margin-bottom: 20px; color: var(--primary-font-color);">
        Reusable Blade components using <code>&lt;x-globals::category.name&gt;</code> syntax.
        These wrap DaisyUI 5 classes and accept props for customization.
    </p>

    {{-- FORM COMPONENTS --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Form Components</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::forms.input name="title" label="Title" /&gt;</code>
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 700px;">
            <x-globals::forms.input name="preview-text" label="Text Input" placeholder="Enter text..." />
            <x-globals::forms.input name="preview-email" label="Email" type="email" placeholder="you@example.com" />
            <x-globals::forms.input name="preview-error" label="With Error" value="bad value" error="This field is invalid" />
            <x-globals::forms.input name="preview-required" label="Required Field" :required="true" />
            <x-globals::forms.select name="preview-select" label="Select" :options="['opt1' => 'Option 1', 'opt2' => 'Option 2', 'opt3' => 'Option 3']" placeholder="Choose one..." />
            <x-globals::forms.date name="preview-date" label="Date" placeholder="Pick a date..." />
            <x-globals::forms.file name="preview-file" label="File Upload" />
        </div>

        <div style="max-width: 700px; margin-top: 15px;">
            <x-globals::forms.textarea name="preview-textarea" label="Textarea" placeholder="Enter description..." :rows="3" />
        </div>

        <h4 style="margin: 20px 0 10px;">Checkbox &amp; Radio (Blade Components)</h4>
        <div style="display: flex; gap: 20px;">
            <x-globals::forms.checkbox name="preview-check1" label="Default checkbox" :checked="true" />
            <x-globals::forms.checkbox name="preview-toggle1" label="Toggle switch" :toggle="true" :checked="true" />
            <x-globals::forms.radio name="preview-radio-blade" value="a" label="Option A" :checked="true" />
            <x-globals::forms.radio name="preview-radio-blade" value="b" label="Option B" />
        </div>
    </section>

    {{-- CARD COMPONENT --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Card Component</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::elements.card title="Title"&gt;Content&lt;/x-globals::elements.card&gt;</code>
        </p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 700px;">
            <x-globals::elements.card title="Basic Card">
                <p>Card content goes here. Supports title, body, and action slots.</p>
                <x-slot:actions>
                    <button class="tw:btn tw:btn-primary tw:btn-sm">Action</button>
                </x-slot:actions>
            </x-globals::elements.card>

            <x-globals::elements.card :compact="true" title="Compact Card">
                <p>A compact card with smaller padding.</p>
            </x-globals::elements.card>
        </div>
    </section>

    {{-- ALERT COMPONENT --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Alert Component</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::feedback.alert type="success"&gt;Message&lt;/x-globals::feedback.alert&gt;</code>
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px; max-width: 700px;">
            <x-globals::feedback.alert type="info">Info: New software update available.</x-globals::feedback.alert>
            <x-globals::feedback.alert type="success">Success: Item has been saved.</x-globals::feedback.alert>
            <x-globals::feedback.alert type="warning">Warning: Invalid email address.</x-globals::feedback.alert>
            <x-globals::feedback.alert type="error" :dismissible="true">Error: Click the X to dismiss this alert.</x-globals::feedback.alert>
        </div>
    </section>

    {{-- DROPDOWN COMPONENT --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Dropdown Component</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::elements.dropdown label="Options"&gt;&lt;li&gt;&lt;a&gt;Item&lt;/a&gt;&lt;/li&gt;&lt;/x-globals::elements.dropdown&gt;</code>
        </p>
        <div style="display: flex; gap: 12px;">
            <x-globals::elements.dropdown label="Actions" icon="fa-solid fa-caret-down">
                <li><a>Edit</a></li>
                <li><a>Duplicate</a></li>
                <li><a class="tw:text-error">Delete</a></li>
            </x-globals::elements.dropdown>

            <x-globals::elements.dropdown>
                <li><a>Option 1</a></li>
                <li><a>Option 2</a></li>
            </x-globals::elements.dropdown>
        </div>
    </section>

    {{-- STATUS INDICATOR --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Status Indicator</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::elements.status-indicator status="success" label="Active" /&gt;</code>
        </p>
        <div style="display: flex; gap: 20px; align-items: center;">
            <x-globals::elements.status-indicator status="success" label="Active" />
            <x-globals::elements.status-indicator status="warning" label="Pending" />
            <x-globals::elements.status-indicator status="error" label="Failed" />
            <x-globals::elements.status-indicator status="info" label="In Progress" />
            <x-globals::elements.status-indicator status="default" label="Unknown" />
        </div>
    </section>

    {{-- BADGE COMPONENT (UPDATED) --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Badge Component (Updated)</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::elements.badge color="primary"&gt;Label&lt;/x-globals::elements.badge&gt;</code>
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <x-globals::elements.badge>Default</x-globals::elements.badge>
            <x-globals::elements.badge color="primary">Primary</x-globals::elements.badge>
            <x-globals::elements.badge color="secondary">Secondary</x-globals::elements.badge>
            <x-globals::elements.badge color="accent">Accent</x-globals::elements.badge>
            <x-globals::elements.badge color="success">Success</x-globals::elements.badge>
            <x-globals::elements.badge color="warning">Warning</x-globals::elements.badge>
            <x-globals::elements.badge color="error">Error</x-globals::elements.badge>
            <x-globals::elements.badge color="outline">Outline</x-globals::elements.badge>
        </div>
    </section>

    {{-- EMPTY STATE --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Empty State</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::elements.empty-state headline="No items"&gt;Description&lt;/x-globals::elements.empty-state&gt;</code>
        </p>
        <x-globals::elements.card style="max-width: 500px;">
            <x-globals::elements.empty-state headline="No tickets found">
                Create your first ticket to get started.
                <x-slot:actions>
                    <button class="tw:btn tw:btn-primary tw:btn-sm">Create Ticket</button>
                </x-slot:actions>
            </x-globals::elements.empty-state>
        </x-globals::elements.card>
    </section>

    {{-- LOADER COMPONENT (UPDATED) --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Loader Component (Updated)</h3>
        <p style="margin-bottom: 10px; font-size: 12px; color: var(--secondary-font-color);">
            Usage: <code>&lt;x-globals::feedback.loading size="md" /&gt;</code>
        </p>
        <div style="display: flex; gap: 12px; align-items: center;">
            <x-globals::feedback.loading size="xs" />
            <x-globals::feedback.loading size="sm" />
            <x-globals::feedback.loading size="md" />
            <x-globals::feedback.loading size="lg" />
        </div>
    </section>

</div>
