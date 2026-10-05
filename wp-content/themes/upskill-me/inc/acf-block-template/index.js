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
            },
        },
        textDomain: "acfblockdemo",
        editorScript: "file:./index.js",
        editorStyle: "file:./index.css",
        style: "file:./style-index.css",
        viewScript: "file:./view.js",
        viewStyle: "file:./view.css",
    },
    blockTemplatesPath: join(__dirname, "block-templates"),
};
