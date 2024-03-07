const {VueLoaderPlugin} = require('vue-loader');
const webpack = require('webpack');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const path = require('path');
const {BundleAnalyzerPlugin} = require('webpack-bundle-analyzer');

require('dotenv').config();

console.log('NODE_ENV:', process.env.NODE_ENV);
console.log('__dirname: ', __dirname);

const outputDir = process.env.BUILD_OUTPUT_DIR;
const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    optimization: {
        splitChunks: false,
        runtimeChunk: false,
    },
    mode: process.env.NODE_ENV || 'development',
    devtool: isProduction ? false : 'eval-source-map',
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
        new CleanWebpackPlugin(),
        new CopyPlugin({
            patterns: [
                {
                    from: 'node_modules/tinymce/models',
                    to: 'models',
                    noErrorOnMissing: true
                },
                {
                    from: 'node_modules/tinymce/themes',
                    to: 'themes',
                    noErrorOnMissing: true
                },
                {
                    from: 'node_modules/tinymce/skins',
                    to: 'skins',
                    noErrorOnMissing: true,
                    globOptions: {
                        ignore: [
                            '**/skins/ui/tinymce-5/**',
                            '**/skins/ui/tinymce-5-dark/**',
                            '**/skins/ui/oxide-dark/**',
                            '**/skins/content/tinymce-5/**',
                            '**/skins/content/tinymce-5-dark/**',
                            '**/skins/content/dark/**',
                        ],
                    }
                },
                {
                    from: 'node_modules/tinymce/plugins/table',
                    to: 'plugins/table',
                    noErrorOnMissing: true
                },
                {
                    from: 'node_modules/tinymce/plugins/code',
                    to: 'plugins/code',
                    noErrorOnMissing: true
                }
            ],
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: JSON.stringify(true),
            __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
        }),
        new BundleAnalyzerPlugin({
            analyzerMode: 'static',
            reportFilename: path.resolve(__dirname, 'bundle-report.html'),
            openAnalyzer: false,
        }),
    ],
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm-bundler.js',
        },
        extensions: ['.js', '.vue', '.json']
    },
};
