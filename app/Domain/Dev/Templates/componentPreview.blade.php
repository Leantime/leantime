<x-global::pageheader headline="Component Preview" />

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
    {{-- MODAL (native dialog) --}}
    {{-- ============================================================ --}}
    <section style="margin-bottom: 40px;">
        <h3 style="margin-bottom: 15px; border-bottom: 1px solid var(--neutral);">Modal (native dialog)</h3>
        <button class="tw:btn tw:btn-primary" onclick="document.getElementById('preview-modal').showModal()">Open Modal</button>

        <dialog id="preview-modal" class="tw:modal">
            <div class="tw:modal-box">
                <h3 style="font-size: var(--font-size-l); font-weight: bold;">Hello!</h3>
                <p style="padding: 15px 0;">This modal uses the native HTML &lt;dialog&gt; element with DaisyUI styling.</p>
                <div class="tw:modal-action">
                    <form method="dialog">
                        <button class="tw:btn">Close</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="tw:modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
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

</div>
