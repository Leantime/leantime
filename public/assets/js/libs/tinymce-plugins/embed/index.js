(function() {
    tinymce.PluginManager.add( 'embed', function( editor, url ) {

        const isMediaElement = (element) =>
            element.hasAttribute('data-mce-object') || element.hasAttribute('data-ephox-embed-iri');

        // Add Button to Visual Editor Toolbar
        editor.ui.registry.addToggleButton('embed', {
            title: 'Embed External Documents',
            icon: 'browse',
            tooltip: 'Embed an external document such as Google Docs or sheet files.',
            onAction: function () {
                const selection = editor.selection;

                let initialValues = {
                    url: "",
                    width: "",
                    height: ""

                }

                if(isMediaElement(selection.getNode())) {

                    initialValues = {
                        url: selection.getNode().firstChild.getAttribute("src"),
                        width: "100vw",
                        height: "50vh"

                    }
                }

                editor.windowManager.open({
                    title: 'Insert Document Link Below',
                    width : 500,
                    height : 300,
                    initialData: initialValues,

                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'htmlpanel',
                                html: '<ul><li>Enter the link to an embeddable document such as a Google Doc.</li><li>Changes made here will be reflected in the original document.</li><li>Only users who have access to the external resource will be able to view the document.</li></ul>'
                            },
                            {
                                type: 'input',
                                size: 20,
                                name: 'url',
                                label: 'Url'
                            },
                        ]
                    },
                    buttons: [ // A list of footer buttons
                        {
                            type: 'submit',
                            text: 'Insert'
                        }
                    ],

                    onSubmit: function(e) {

                        var url = e.getData().url;

                        var code = '<iframe src="'+url+'" style="display:block; width:99%; height:700px;"  class="autoresizeIframe"></iframe>';

                        editor.insertContent(code);

                        e.close();
                    }
                });
            },
            onSetup: (buttonApi) => editor.selection.selectorChangedWithUnbind('span[data-mce-object="iframe"]', buttonApi.setActive).unbind
        });
    });
})();
