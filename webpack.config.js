/**
 * Webpack configuration for BruteFort plugin.
 *
 * @package BruteFort
 */

const path = require('path');
const isProduction = process.env.NODE_ENV === 'production';
const WebpackBar = !isProduction ? require("webpackbar") : null;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const webpack = require('webpack');

module.exports = {
    entry: './src/index.tsx',
    devServer: {
        headers: {'Access-Control-Allow-Origin': '*'},
        allowedHosts: 'all',
        host: 'localhost',
        port: 5432,
        client: {
            overlay: false,
        },
    },
    output: {
        path: path.resolve(__dirname, 'assets/build'),
        filename: 'admin.js',
        chunkFilename: '[name].[contenthash].js',
        publicPath: isProduction ? '' : 'http://localhost:5432/',
        clean: true, // Clean the output directory before each build.
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
                    isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
                    'css-loader',
                    'postcss-loader'
                ]
            },
        ],
    },
    plugins: [
        ...(isProduction ? [new MiniCssExtractPlugin(
            {
                filename: '../css/admin.css', // Will generate in /assets/css/.
            }
        )] : []),
        ...(!isProduction && WebpackBar ? [new WebpackBar()] : [])
    ],
};
