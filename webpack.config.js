const path = require('path');

module.exports = {
    target: 'web',
    mode: 'production',

    entry: './src/AdminMenu.tsx', 

    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'build'),
    },

    resolve: {
        extensions: ['.ts', '.tsx', '.js', '.jsx'],
    },

    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
        ],
    },
};
