const path = require('path');
const isProduction = process.env.NODE_ENV === 'production';
const WebpackBar = !isProduction ? require("webpackbar") : null;
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const webpack = require('webpack');

module.exports = {
    entry: isProduction ? './src/index.tsx' : [
        'webpack-dev-server/client/index.js?http://localhost:8080/&sockHost=localhost&sockPort=8080&sockPath=/ws&sockProtocol=ws&sockSecure=false',
        'webpack/hot/dev-server.js',
        './src/index.tsx'
    ],
    output: {
        path: path.resolve(__dirname, 'assets/build'),
        filename: 'admin.js',
        chunkFilename: '[name].[contenthash].js',
        publicPath: isProduction ? '' : 'http://localhost:8080/',
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
        ...(isProduction ? [new MiniCssExtractPlugin({
            filename: '../css/admin.css', // will generate in /assets/css/
        })] : []),
        ...(!isProduction && WebpackBar ? [new WebpackBar()] : []),
        ...(!isProduction ? [new webpack.HotModuleReplacementPlugin()] : [])
    ],
    ...(isProduction ? {} : {
        devServer: {
            static: {
                directory: path.join(__dirname, 'assets/build'),
            },
            port: 8080,
            hot: 'only',
            liveReload: false,
            open: false,
            compress: true,
            allowedHosts: 'all',
            headers: {
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
                "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
            },
            host: '0.0.0.0',
            client: {
                webSocketURL: 'ws://localhost:8080/ws',
                overlay: {
                    errors: true,
                    warnings: false,
                }
            },
            webSocketServer: {
                options: {
                    path: '/ws'
                }
            }
        }
    })
};
