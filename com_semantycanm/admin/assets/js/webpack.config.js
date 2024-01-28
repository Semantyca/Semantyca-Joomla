const {VueLoaderPlugin} = require('vue-loader');
const webpack = require('webpack');
const CopyPlugin = require('copy-webpack-plugin');
const path = require('path');
const MonacoEditorPlugin = require('monaco-editor-webpack-plugin')
const fs = require('fs');

require('dotenv').config();

console.log('NODE_ENV:', process.env.NODE_ENV);

const outputDir = process.env.BUILD_OUTPUT_DIR;

module.exports = {
    mode: process.env.NODE_ENV || 'development',
    devtool: 'eval-source-map',
    entry: path.resolve(__dirname, 'src/main.js'),
    output: {
        path: path.resolve(__dirname, outputDir),
        filename: 'bundle-[fullhash].js'
    },
    watch: process.env.NODE_ENV === 'development',
    watchOptions: {
        ignored: /node_modules/,
        aggregateTimeout: 300,
        poll: 1000
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader'],
            },
            {
                test: /tinymce\/(models|themes|skins|plugins)\/.+/,
                loader: 'file-loader',
                options: {
                    name: '[path][name].[ext]',
                    context: 'node_modules/tinymce',
                },
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new CopyPlugin({
            patterns: [
                //... other patterns
                {
                    from: 'node_modules/tinymce/plugins/code',
                    to: 'plugins/code',
                    noErrorOnMissing: true,
                    filter: async (resourcePath) => {
                        // Copy files only if they don't exist in the destination
                        const destPath = path.resolve(__dirname, outputDir, path.relative('node_modules/tinymce', resourcePath));
                        return !fs.existsSync(destPath);
                    },
                },
                //... other patterns
            ],
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: JSON.stringify(true),
            __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
        }),
        //... other plugins
    ],
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm-bundler.js',
        },
        extensions: ['.js', '.vue', '.json']
    },
};
