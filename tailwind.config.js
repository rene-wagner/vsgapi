module.exports = {
    content: [
        './assets/**/*.js',
        './templates/**/*.twig',
        './node_modules/flowbite/**/*.js',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('flowbite/plugin'),
    ],
};
