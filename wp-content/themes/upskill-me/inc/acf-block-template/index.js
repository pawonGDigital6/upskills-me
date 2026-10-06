const { join } = require("path");

module.exports = {
    defaultValues: {
        slug: "acf-block-template",
        title: "ACF Block Template",
        description: "A custom ACF block template.",
        dashicon: "block-default",
        category: "acf-blocks",
        keywords: ["acf"],
        namespace: "acf-block",
        apiVersion: 2, // Set this to 2 so that ACF Block mode settings works properly
        supports: {
            html: false,
            anchor: true,
        },
        attributes: {
            previewImage: {
                type: "string",
                default: "preview.png",
            },
        },
        customBlockJSON: {
            acf: {
                mode: "preview",
                renderTemplate: "render.php",
                // Block version 3 is what makes the two settings below available:
                // it moves a block's fields out of the sidebar and into a full
                // editor opened from the block toolbar.
                blockVersion: 3,
                // Fields belong in that editor, not duplicated down the sidebar.
                hideFieldsInSidebar: true,
                // ...and the button that opens it belongs in the toolbar only.
                // Left at its default (true) ACF also draws a pencil in the
                // sidebar, which is the control this theme does not want.
                expandedEditorButtons: ["toolbar"],
            },
        },
        textDomain: "upskill-me",
        editorScript: "file:./index.js",
        editorStyle: "file:./index.css",
        style: "file:./style-index.css",
        viewScript: "file:./view.js",
    },
    blockTemplatesPath: join(__dirname, "block-templates"),
};
