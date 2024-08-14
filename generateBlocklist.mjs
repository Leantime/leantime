import fs from 'fs';
import postcss from 'postcss';
import selectorParser from 'postcss-selector-parser';

const pjson = JSON.parse(fs.readFileSync('./package.json', 'utf-8'));
const css = fs.readFileSync(`./public/dist/css/app.${pjson.version}.min.css`, 'utf-8');
const classNames = new Set();

const addClassIfMatches = (classNode) => {
    if (classNode.value.startsWith('tw-')) {
        classNames.add(classNode.value);
    }
};

const processSelectors = (rule) => {
    selectorParser(selectors => {
        selectors.walkClasses(addClassIfMatches);
    }).processSync(rule);
};

const extractClassesPlugin = postcss.plugin('extract-classes', () => {
    return (root) => {
        root.walkRules(processSelectors);
    };
});

await postcss([extractClassesPlugin]).process(css, { from: undefined });
const blocklist = Array.from(classNames);
fs.writeFileSync('./blocklist.json', JSON.stringify(blocklist, null, 2));
