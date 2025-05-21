const path = require('path');
const isProduction = process.env.NODE_ENV === 'production';
const WebpackBar = !isProduction ? require("webpackbar") : null;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: './src/index.tsx',
    output: {
        path: path.resolve(__dirname, 'assets/build'),
        filename: 'admin.js',
        chunkFilename: '[name].[contenthash].js',
    },
    mode: isProduction ? 'production' : 'development',
    devtool: isProduction ? false : 'source-map',
    resolve: {
        extensions: ['.tsx', '.ts', '.js', '.jsx'],
    },
    module: {
        rules: [
            {
                test: /\.(ts|tsx)$/,
                exclude: /node_modules/,
                use: 'babel-loader',
            },
            {
                test: /\.css$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'postcss-loader'
                ]
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/admin.css', // will generate in /assets/css/
        }),
        ...(!isProduction && WebpackBar ? [new WebpackBar()] : [])
    ]
};
