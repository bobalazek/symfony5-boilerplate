const webpack = require('webpack');
const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  entry: {
    app: path.resolve(__dirname, 'src', 'index.ts'),
  },
  output: {
    filename: '[name].[hash].js',
    path: path.resolve(__dirname, 'dist'),
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js']
  },
  watchOptions: {
    poll: 250,
  },
  devtool: 'source-map',
  context: __dirname,
  plugins: [
    new HtmlWebpackPlugin({
      template: path.resolve(__dirname, 'static', 'index.html'),
    }),
    new MiniCssExtractPlugin(),
    new webpack.ProvidePlugin({
        'ammo': 'ammo',
    }),
    new CopyWebpackPlugin([
      {
          from: 'static',
          to: 'static',
          ignore: [
            'index.html', // already handled by HtmlWebpackPlugin
            '*.css', // already handled by the MiniCssExtractPlugin
          ],
      },
    ], {
      copyUnmodified: true,
    }),
  ],
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        loader: 'ts-loader',
        exclude: /node_modules/
      },
      {
        test: /\.css$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              esModule: true,
            },
          },
          'css-loader',
        ],
      },
    ]
  },
  optimization: {
    splitChunks: {
      chunks: 'all',
    },
  },
  node: {
    fs: 'empty',
  },
}
