/**
 * Tiptap Editor Test Utilities
 *
 * Run these tests in browser console to verify editor functionality.
 * Usage: leantime.tiptapTests.runAll()
 *
 * @module tiptap/test-utils
 */

(function() {
    'use strict';

    var testResults = [];

    function log(message, success) {
        var status = success ? '✓' : '✗';
        var color = success ? 'color: green' : 'color: red';
        console.log('%c' + status + ' ' + message, color);
        testResults.push({ message: message, success: success });
    }

    function assert(condition, message) {
        log(message, condition);
        return condition;
    }

    /**
     * Test 1: Registry exists and is functional
     */
    function testRegistry() {
        console.log('\n--- Testing EditorRegistry ---');

        var registry = window.leantime?.tiptapController?.registry;
        assert(registry !== undefined, 'Registry exists on tiptapController');
        assert(typeof registry.register === 'function', 'Registry has register method');
        assert(typeof registry.get === 'function', 'Registry has get method');
        assert(typeof registry.destroy === 'function', 'Registry has destroy method');
        assert(typeof registry.destroyAll === 'function', 'Registry has destroyAll method');
        assert(typeof registry.destroyWithin === 'function', 'Registry has destroyWithin method');
    }

    /**
     * Test 2: Controller exists and has required methods
     */
    function testController() {
        console.log('\n--- Testing TiptapController ---');

        var controller = window.leantime?.tiptapController;
        assert(controller !== undefined, 'tiptapController exists on window.leantime');
        assert(typeof controller.initComplex === 'function', 'Controller has initComplex method');
        assert(typeof controller.initSimple === 'function', 'Controller has initSimple method');
        assert(typeof controller.initNotes === 'function', 'Controller has initNotes method');
        assert(typeof controller.initInline === 'function', 'Controller has initInline method');
        assert(typeof controller.initEditors === 'function', 'Controller has initEditors method');
        assert(typeof controller.getEditor === 'function', 'Controller has getEditor method');
        assert(typeof controller.destroyAll === 'function', 'Controller has destroyAll method');
        assert(typeof controller.registerExtension === 'function', 'Controller has registerExtension method');
    }

    /**
     * Test 3: Create and destroy an editor
     */
    function testEditorLifecycle() {
        console.log('\n--- Testing Editor Lifecycle ---');

        // Create a test textarea
        var container = document.createElement('div');
        container.id = 'tiptap-test-container';
        container.innerHTML = '<textarea id="tiptap-test-textarea" class="tiptapComplex">Initial content</textarea>';
        document.body.appendChild(container);

        var controller = window.leantime.tiptapController;
        var textarea = document.getElementById('tiptap-test-textarea');

        // Initialize editor
        var editorWrapper = controller.initComplex(textarea);
        assert(editorWrapper !== null, 'Editor initialized successfully');
        assert(editorWrapper.editor !== null, 'Editor instance exists');
        assert(typeof editorWrapper.getHTML === 'function', 'Editor has getHTML method');

        // Test content
        var content = editorWrapper.getHTML();
        assert(content.includes('Initial content'), 'Editor loaded initial content');

        // Test setContent
        editorWrapper.setContent('<p>New content</p>');
        var newContent = editorWrapper.getHTML();
        assert(newContent.includes('New content'), 'Editor setContent works');

        // Test textarea sync
        var textareaValue = textarea.value;
        assert(textareaValue.includes('New content'), 'Textarea synced with editor content');

        // Test registry tracking
        var editorElement = editorWrapper.element;
        var registeredEditor = controller.registry.get(editorElement);
        assert(registeredEditor !== null, 'Editor registered in registry');

        // Test destroy
        editorWrapper.destroy();
        var afterDestroy = controller.registry.get(editorElement);
        assert(afterDestroy === null, 'Editor removed from registry after destroy');

        // Cleanup
        container.remove();
    }

    /**
     * Test 4: Multiple editors
     */
    function testMultipleEditors() {
        console.log('\n--- Testing Multiple Editors ---');

        var container = document.createElement('div');
        container.id = 'tiptap-multi-test';
        container.innerHTML = `
            <textarea id="test-editor-1" class="tiptapComplex">Editor 1</textarea>
            <textarea id="test-editor-2" class="tiptapSimple">Editor 2</textarea>
            <textarea id="test-editor-3" class="tiptapNotes">Editor 3</textarea>
        `;
        document.body.appendChild(container);

        var controller = window.leantime.tiptapController;

        // Initialize all
        var editors = controller.initEditors(container);
        assert(editors.length === 3, 'All 3 editors initialized');

        // Check registry count
        var allEditors = controller.registry.getAll();
        assert(allEditors.length === 3, 'Registry tracks all 3 editors');

        // Destroy all
        var destroyedCount = controller.destroyAll();
        assert(destroyedCount === 3, 'All 3 editors destroyed');

        // Verify empty
        var afterDestroy = controller.registry.getAll();
        assert(afterDestroy.length === 0, 'Registry is empty after destroyAll');

        // Cleanup
        container.remove();
    }

    /**
     * Test 5: Editor formatting commands
     */
    function testFormattingCommands() {
        console.log('\n--- Testing Formatting Commands ---');

        var container = document.createElement('div');
        container.id = 'tiptap-format-test';
        container.innerHTML = '<textarea id="format-test-textarea" class="tiptapComplex"></textarea>';
        document.body.appendChild(container);

        var controller = window.leantime.tiptapController;
        var editorWrapper = controller.initComplex(document.getElementById('format-test-textarea'));
        var editor = editorWrapper.editor;

        // Test bold
        editor.commands.setContent('<p>test</p>');
        editor.commands.selectAll();
        editor.commands.toggleBold();
        var boldContent = editorWrapper.getHTML();
        assert(boldContent.includes('<strong>') || boldContent.includes('font-weight'), 'Bold command works');

        // Test heading
        editor.commands.setContent('<p>heading test</p>');
        editor.commands.selectAll();
        editor.commands.toggleHeading({ level: 2 });
        var headingContent = editorWrapper.getHTML();
        assert(headingContent.includes('<h2>'), 'Heading command works');

        // Test bullet list
        editor.commands.setContent('<p>list item</p>');
        editor.commands.selectAll();
        editor.commands.toggleBulletList();
        var listContent = editorWrapper.getHTML();
        assert(listContent.includes('<ul>') && listContent.includes('<li>'), 'Bullet list command works');

        // Cleanup
        editorWrapper.destroy();
        container.remove();
    }

    /**
     * Test 6: destroyWithin for HTMX simulation
     */
    function testDestroyWithin() {
        console.log('\n--- Testing destroyWithin (HTMX simulation) ---');

        var outerContainer = document.createElement('div');
        outerContainer.id = 'htmx-test-outer';

        var innerContainer = document.createElement('div');
        innerContainer.id = 'htmx-test-inner';
        innerContainer.innerHTML = `
            <textarea id="htmx-editor-1" class="tiptapComplex">Content 1</textarea>
            <textarea id="htmx-editor-2" class="tiptapSimple">Content 2</textarea>
        `;

        outerContainer.appendChild(innerContainer);
        document.body.appendChild(outerContainer);

        var controller = window.leantime.tiptapController;

        // Initialize editors
        controller.initEditors(innerContainer);
        var beforeCount = controller.registry.getAll().length;
        assert(beforeCount === 2, 'Two editors initialized in inner container');

        // Simulate HTMX swap - destroy editors within inner container
        var destroyed = controller.registry.destroyWithin(innerContainer);
        assert(destroyed === 2, 'destroyWithin destroyed 2 editors');

        var afterCount = controller.registry.getAll().length;
        assert(afterCount === 0, 'Registry empty after destroyWithin');

        // Cleanup
        outerContainer.remove();
    }

    /**
     * Test 7: Plugin extension registration
     */
    function testExtensionRegistration() {
        console.log('\n--- Testing Extension Registration ---');

        var controller = window.leantime.tiptapController;

        // Register a mock extension
        var mockExtension = { name: 'testExtension' };
        controller.registerExtension('test', mockExtension);
        assert(true, 'Extension registration did not throw error');

        // Register a slash command
        var mockHandler = function() { return true; };
        controller.registerSlashCommand('/test', mockHandler);
        assert(true, 'Slash command registration did not throw error');

        var commands = controller.getSlashCommands();
        assert(commands.has('/test'), 'Slash command was registered');

        // Register a toolbar button
        controller.registerToolbarButton('testBtn', { icon: 'test', action: function() {} });
        var buttons = controller.getToolbarButtons();
        assert(buttons.has('testBtn'), 'Toolbar button was registered');
    }

    /**
     * Run all tests
     */
    function runAll() {
        console.log('=== Tiptap Editor Tests ===\n');
        testResults = [];

        try {
            testRegistry();
            testController();
            testEditorLifecycle();
            testMultipleEditors();
            testFormattingCommands();
            testDestroyWithin();
            testExtensionRegistration();
        } catch (e) {
            console.error('Test error:', e);
        }

        // Summary
        console.log('\n=== Test Summary ===');
        var passed = testResults.filter(function(r) { return r.success; }).length;
        var failed = testResults.filter(function(r) { return !r.success; }).length;
        console.log('Passed: ' + passed);
        console.log('Failed: ' + failed);
        console.log('Total: ' + testResults.length);

        return {
            passed: passed,
            failed: failed,
            total: testResults.length,
            results: testResults
        };
    }

    // Export to global
    window.leantime = window.leantime || {};
    window.leantime.tiptapTests = {
        runAll: runAll,
        testRegistry: testRegistry,
        testController: testController,
        testEditorLifecycle: testEditorLifecycle,
        testMultipleEditors: testMultipleEditors,
        testFormattingCommands: testFormattingCommands,
        testDestroyWithin: testDestroyWithin,
        testExtensionRegistration: testExtensionRegistration
    };

    console.log('[Tiptap] Test utilities loaded. Run leantime.tiptapTests.runAll() to test.');
})();
