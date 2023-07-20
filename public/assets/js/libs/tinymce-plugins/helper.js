

function addPluginToPluginManager(name, register) {
    // tinymce puts itself in the global namespace
    if (!globalThis.tinymce)
        throw new Error(
            `Please import tinymce before importing the ${name} plugin.`
        );

    globalThis.tinymce.PluginManager.add(name, register);
}

function getWindow() {
    return globalThis.tinymce.activeEditor.contentWindow;
}

function notifyEditorChange(editor, type) {
    setTimeout(() => {
        editor.fire("input", { inputType: type, data: "" });
    }, 0);
}
