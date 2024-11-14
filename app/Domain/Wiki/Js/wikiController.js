leantime.wikiController = (function () {

    //Functions
    var initTree = function (id, selectedId) {

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
        });

        jQuery(id).on("ready.jstree", function (e, data) {

            jQuery(this).jstree("deselect_all");
            jQuery(this).jstree("select_node", "treenode_" + selectedId + "", true);
            jQuery(this).jstree("save_state");

        })

        jQuery(id).on('activate_node.jstree', function (e, data) {

            jQuery(this).jstree("save_state");

            if (data == undefined || data.node == undefined || data.node.id == undefined) {
                return;
            }

            window.location.href = data.node.a_attr.href;
        });



    }

    // Make public what you want to have public, everything else is private
    return {
        initTree: initTree,
    };
})();
