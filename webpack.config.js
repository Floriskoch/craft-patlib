const path = require("path");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const Stylish = require('webpack-stylish');

const settings = {
  distPath: path.join(__dirname, "dist"),
  srcPath: path.join(__dirname, "src/resources/src")
};

const absPath = dir => path.resolve(__dirname, dir);

module.exports = (env, options) => {
  const isDevMode = options.mode === "development";
  const devPublic = 'http://localhost:8080/';

  return {
    entry: './src/resources/src/index.js',
    output: {
      path: path.join(__dirname, '/src', 'resources', 'dist')
    },
    devtool: isDevMode ? "source-map" : false,
    module: {
      rules: [
        {
          test: /\.js$/,
          use: ["babel-loader"]
        }
      ]
    },
    // devServer: {
    //   contentBase: absPath('resources/'),
    //   // host: '0.0.0.0',
    //   publicPath: devPublic,
    //   hot: true,
    //   inline: true,
    //   overlay: true,
    //   stats: 'errors-only',
    //   watchOptions: {
    //     poll: true,
    //   },
    //   headers: {
    //     'Access-Control-Allow-Origin': '*',
    //   },
    //   disableHostCheck: true,
    // },
    plugins: [
      new HtmlWebpackPlugin({
        title: 'test',
        filename: absPath('src/templates/index.twig'),
        template: "./src/resources/templates/index_template.twig",
        inject: false,
        environment: process.env.WEBPACK_MODE === 'production' ? 'production' : 'development',
        devServer: devPublic
      }),
      new Stylish()
    ]
  };
};
