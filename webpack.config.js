const path = require('path');
const webpack = require('webpack');
const Utils = require('webpack-config-utils');
const getIfUtils = Utils.getIfUtils;
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin  = require('html-webpack-harddisk-plugin');
const Stylish = require('webpack-stylish');

const absPath = dir => path.resolve(__dirname, dir);
const assetBundles = (dir = '') => path.resolve(__dirname, 'src', 'resources', dir);

const webpackConfig = (env = {}) => {
  const { ifDevelopment, ifProduction } = getIfUtils(env);

  const CSS_STACK = ({
                       scss = true,
                       vue = false,
                       sassResources = false,
                       production = false,
                     } = {}) => {
    const styleLoader = production
      ? []
      : [
        {
          loader: vue ? 'vue-style-loader' : production ? '' : 'style-loader',
        },
      ];

    const scssLoader = scss
      ? [
        {
          loader: 'sass-loader',
          options: {
            includePaths: [assetBundles('src/scss/')],
            data:
              "@import 'settings.variables';\n" +
              "@import 'tools.functions'; \n" +
              "@import 'tools.mixins';",
          },
        },
      ]
      : [];

    const sassResourcesLoader = sassResources
      ? [
        {
          loader: 'sass-resoures-loader',
          options: {
            resources: [
              assetBundles('src/scss/_variables.scss'),
              assetBundles('src/scss/_mixins.scss'),
              assetBundles('src/scss/_functions.scss'),
            ],
          },
        },
      ]
      : [];

    return [
      ...styleLoader,
      {
        loader: 'css-loader',
      },
      ...scssLoader,
      ...sassResourcesLoader,
    ];
  };

  const devPublic = 'http://localhost:8080/';

  return {
    entry: {
      app: assetBundles('src/index.js'),
    },
    output: {
      path: assetBundles('dist'),
      publicPath: ifProduction('', devPublic),
      filename: 'js/index.js',
      chunkFilename: 'js/[id].js',
      hotUpdateMainFilename: 'js/[hash].hot-update.json',
    },
    stats: 'none',
    devServer: {
      contentBase: absPath('resources/'),
      host: '0.0.0.0',
      publicPath: devPublic,
      hot: true,
      inline: true,
      overlay: true,
      stats: 'errors-only',
      watchOptions: {
        poll: true,
      },
      headers: {
        'Access-Control-Allow-Origin': '*',
      },
      disableHostCheck: true,
    },
    resolve: {
      extensions: ['.js', '.jsx'],
      modules: [absPath('node_modules'), assetBundles('src')],
      // alias: {
      //   vue$: 'vue/dist/vue.esm.js',
      //   Modules: assetBundles('src/vue/components/'),
      //   '@Components': assetBundles('src/vue/components/'),
      //   '@Images': assetBundles('src/img/'),
      //   '@Views': assetBundles('src/vue/views/'),
      // },
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          loader: 'eslint-loader',
          enforce: 'pre',
          include: assetBundles('src'),
        },
        {
          test: /\.(png|jpg|gif|svg)$/,
          use: [
            {
              loader: 'file-loader',
            },
          ],
        },
        {
          test: /\.css$/,
          loader: CSS_STACK({
            scss: false,
            vue: true,
          }),
        },
        {
          test: /\.scss$/,
          include: assetBundles('src'),
          use: ifProduction(
            ExtractTextPlugin.extract({
              use: CSS_STACK({ production: env.production }),
            }),
            CSS_STACK(),
          ),
        },
        {
          test: /\.js$/,
          loader: 'babel-loader',
          include: assetBundles('src'),
        },
      ],
    },
    plugins: [
      new webpack.HotModuleReplacementPlugin(),
      new webpack.ProgressPlugin(),
      ...(env.production
        ? [
          new ExtractTextPlugin({
            filename: 'css/styles.css',
            allChunks: true,
          }),
          new BundleAnalyzerPlugin({
            analyzerMode: 'disabled',
            generateStatsFile: true,
            statsFilename: absPath('webpack/stats.json'),
            logLevel: 'info',
          }),
        ]
        : []),
      new HtmlWebpackPlugin({
        filename: absPath('src/templates/index.twig'),
        template: absPath('webpack/webpack_template.twig'),
        inject: false,
        devServer: devPublic,
        environment: ifProduction('production', 'development'),
        alwaysWriteToDisk: true,
      }),
      new HtmlWebpackHarddiskPlugin(),
      new webpack.NamedModulesPlugin(),
      new Stylish(),
    ],
  };
};

module.exports = webpackConfig;
