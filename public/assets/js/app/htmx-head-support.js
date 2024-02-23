require('htmx.org/dist/ext/head-support');

window.htmx.onLoad(function () {
    const links = [...document.getElementsByTagName('a')].filter(el => el.getAttributeNames().includes('href'));
    const forms = document.getElementsByTagName('form');
    const elements = [...links, ...forms].filter(el => el.getAttributeNames().filter(attr => attr.startsWith('hx-')).length == 0);

    if (elements.length == 0) {
        return;
    }

    elements.forEach(el => {
        el.setAttribute('hx-boost','true');
        el.setAttribute('hx-ext','head-support');
        window.htmx.process(el);
    });
});
