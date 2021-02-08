/* eslint-disable import/no-extraneous-dependencies */
const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const { DefinePlugin } = require('webpack');
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
const resolve = require('./webpack.resolve').forWebpack;

module.exports = env => ({

  entry: {
    ajaxSelectField: ['core-js/stable', 'regenerator-runtime/runtime', 'src/ajaxSelectField.js']
  },

  output: {
    path: path.resolve(__dirname, '../dist'),
    filename: '[name].js',
    publicPath: ''
  },

  mode: 'development',

  devtool: 'source-map',

  resolve,

  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: file => (
          /node_modules/.test(file)
          && !/\.vue\.js/.test(file)
        ),
        use: {
          loader: 'babel-loader',
          options: {
            plugins: [
              '@babel/plugin-proposal-object-rest-spread',
              '@babel/plugin-syntax-dynamic-import'
            ],
            presets: [['@babel/preset-env', { modules: false }, '@babel/preset-stage-3']]
          }
        }
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader'
      },
      {
        test: /\.css$/,
        use: [
          {
            loader: 'vue-style-loader'
          },
          {
            loader: MiniCSSExtractPlugin.loader
          },
          {
            loader: 'css-loader'
          }
        ]
      },
      {
        test: /\.less$/,
        use: [
          {
            loader: 'vue-style-loader'
          },
          {
            loader: MiniCSSExtractPlugin.loader
          },
          {
            loader: 'css-loader'
          },
          {
            loader: 'less-loader'
          }
        ]
      }
    ]
  },

  plugins: [
    new DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(env.NODE_ENV)
      }
    }),

    new VueLoaderPlugin(),

    new MiniCSSExtractPlugin({
      filename: '[name].css'
    })
  ]
});
