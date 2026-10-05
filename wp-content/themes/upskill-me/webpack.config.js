const defaults = require("@wordpress/scripts/config/webpack.config");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const path = require("path");

const copyPreviewImage = [];

if(process.env.COPY_PREVIEW_IMAGE === "true"){
    copyPreviewImage.push(
        new CopyWebpackPlugin({
            patterns: [
                {
                    from:  "**/preview.png",
                    to:  "[path][name][ext]",
                    context: path.resolve(__dirname, "src", "blocks")
                },
            ],
        }),
    )
}

module.exports = {
    ...defaults,
    output: {
        ...defaults.output,
        clean: true
    },
    externals: {
        ...defaults.externals,
        jquery: "jQuery", // Make the 'jQuery' available from external source so that we do not have to install saperate dependency.
    },
    plugins: [
        ...defaults.plugins,
        ...copyPreviewImage
    ],
};
