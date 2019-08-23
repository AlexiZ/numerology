const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const nodeModules = path.resolve(__dirname, 'node_modules');
const resources = path.resolve(__dirname, 'src/ExtranetBundle/Resources,src/SiteBundle/Resources');

const config = {
    cache: true,
    entry: {
        extranet: [
            './src/ExtranetBundle/Resources/assets/app.js',
        ],
        site: [
            './src/SiteBundle/Resources/assets/app.js',
        ],
    },
    output: {
        library: '[name]', // assets build
        libraryTarget: 'umd',
        path: path.join(__dirname, 'web/compiled'),
        filename: '[name].js',
        chunkFilename: '[name]-[chunkhash].js'
    },
    module: {
        rules: [{
            // required for es6
            test: /\.js$/,
            exclude: file => (
                /node_modules/.test(file)
            ),
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['env']
                }
            }
        },
        {
            // required to write 'require('./style.scss')'
            test: /\.s(a|c)ss$/,
            exclude: /node_modules/, // Ignore the folder containing 3rd party scripts
            loader: ExtractTextPlugin.extract({
                fallback: 'style-loader',
                use: [
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                            sourceMap: true
                        }
                    }, {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: true,
                            plugins: []
                        }
                    }, {
                        loader: 'sass-loader',
                        options: {
                            includePaths: [
                                path.resolve(resources , '/assets/'),
                                nodeModules
                            ],
                            sourceMap: true
                        }
                    },
                ]
            })
        }, {
            // required to write 'require('./style.css')'
            test: /\.css$/,
            loader: ExtractTextPlugin.extract({
                fallback: 'style-loader',
                use: [
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1
                        }
                    },
                    'postcss-loader'
                ]
            })
        }, {
            // required for bootstrap icons
            test: /\.woff2?(\?v=\d+\.\d+\.\d+)?$/,
            loader: 'url-loader',
            options: {
                prefix: 'font/',
                limit: 5000,
                mimetype: 'application/font-woff'
            }
        }, {
            test: /\.(ttf|eot|svg|otf)(\?v=\d+\.\d+\.\d+)?$/,
            loader: 'file-loader',
            options: {
                prefix: 'font/'
            }
        }, {
            // required to support images
            test: /\.(jpe?g|png|gif|svg)$/i,
            use: [
                {
                    loader: 'file-loader',
                    options: {
                        hash: 'sha512',
                        digest: 'hex',
                        name: '[hash].[ext]'
                    }
                },
                {
                    loader: 'image-webpack-loader',
                    options: {
                        includePaths: [
                            path.resolve(resources , '/public/'),
                        ],
                        gifsicle: {
                            interlaced: false
                        },
                        bypassOnDebug: true,
                        optipng: {
                            optimizationLevel: 7
                        }
                    }
                }
            ]
        }, {
            // Expose jQuery
            test: require.resolve('jquery'),
            use: [
                {
                    loader: 'expose-loader?jQuery'
                }, {
                    loader: 'expose-loader?$'
                }
            ]
        }]
    },
    // devtool: 'cheap-module-eval-source-map',
    devtool: 'source-map',
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({
            name: 'common',
            filename: 'common.js',
            chunks: ['extranet'],
        }),
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(process.env.NODE_ENV)
            }
        }),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new webpack.NoEmitOnErrorsPlugin(),
        // Use the plugin to specify the resulting filename (and add needed behavior to the compiler)
        new ExtractTextPlugin({
            filename: '[name].css',
            allChunks: true
        })
    ],
    externals: {
        translator: 'Translator',
        routing: 'Routing',
    }
};

if (process.env.NODE_ENV === 'production') {
    config.devtool= false;
    config.plugins.push(
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
        // optimize module ids by occurrence count
        new webpack.optimize.OccurrenceOrderPlugin()
    );
} else {
    config.plugins.push(
        new webpack.LoaderOptionsPlugin({
            debug: true
        }),
    );
}

module.exports = config;
