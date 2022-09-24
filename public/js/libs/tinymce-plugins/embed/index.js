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
                        width: selection.getNode().firstChild.getAttribute("width"),
                        height: selection.getNode().firstChild.getAttribute("height")

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

                        {
                            type: 'input',
                            size: 10,
                            name: 'width',
                            label: 'Width',
                            value: '100'
                        },
                        {
                            type: 'input',
                            size: 10,
                            name: 'height',
                            label: 'Height',
                            value: '100'
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
                        var width = e.getData().width;
                        var height = e.getData().height;
                        var classAttr = e.getData().class;
                        var idAttr = e.getData().id;
                        var iframe = e.getData().embedcode;

                        if (url.trim() == "" && iframe.trim() !== "" ) {
                            var regEx = /(src|width|height)=["']([^"']*)["']/gi;

                            iframe.replace(regEx, function(all, type, value) {
                                switch (type) {
                                    case 'src':
                                        url = value;
                                        break;
                                    case 'width':
                                        width = value;
                                        break;
                                    case 'height':
                                        height = value;
                                        break;
                                }
                            });
                        }

                        var code = '<iframe src="'+url+'" width="'+width+'" height="'+height+'" class="autoresizeIframe"></iframe>';

                        editor.insertContent(code);

                        e.close();
                    }
                });
            },
            onSetup: (buttonApi) => editor.selection.selectorChangedWithUnbind('span[data-mce-object="iframe"]', buttonApi.setActive).unbind
        });
    });
})();
