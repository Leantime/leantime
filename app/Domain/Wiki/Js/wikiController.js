import jQuery from 'jquery';

//Functions
export const initTree = function (id, selectedId) {
    jQuery(id).jstree({
        "core": {
            "expand_selected_onload":true,
            "themes": {
                "dots":false
            }
        },
        "state" : {
            "key" : "tree_state",

        },
        "types" : {
            "default": {
                "icon": "far fa-file-alt"
            },
        },
        "plugins" : ["wholerow", "types", "state"]
    }).bind("loaded.jstree", function (e, data) {
        jQuery(id).jstree("select_node", "treenode_" + selectedId + "", true);
    });

    jQuery(id).on('activate_node.jstree', function (e, data) {

        jQuery(id).jstree("save_state");

        if (data == undefined || data.node == undefined || data.node.id == undefined) {
            return;
        }

        window.location.href = data.node.a_attr.href;
    });
}

export const wikiModal = function () {
    var wikiModalConfig = {
        sizes: {
            minW: 400,
            minH: 350
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            afterShowCont: function () {

                jQuery(".formModal").nyroModal(wikiModalConfig);
            },
            beforeClose: function () {
                location.reload();
            }
        },
        titleFromIframe: true
    };
    jQuery(".wikiModal").nyroModal(wikiModalConfig);
}

// Make public what you want to have public, everything else is private
export default {
    initTree: initTree,
    wikiModal: wikiModal,
};
