export function addToGlobalScope(object) {
    window.leantime = {
        ...(window.leantime || {}),
        ...object,
    };
}

export function getFromGlobalScope(key) {
    return window.leantime?.[key];
}

export function removeFromGlobalScope(key) {
    if (window.leantime && key in window.leantime) {
        delete window.leantime[key];
    }
}
