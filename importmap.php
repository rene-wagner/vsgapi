<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    '@fortawesome/fontawesome-free/css/fontawesome.min.css' => [
        'version' => '7.2.0',
        'type' => 'css',
    ],
    '@fortawesome/fontawesome-free/css/solid.min.css' => [
        'version' => '7.2.0',
        'type' => 'css',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'easymde' => [
        'version' => '2.20.0',
    ],
    'codemirror' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/edit/continuelist.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/display/fullscreen.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/mode/markdown/markdown.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/mode/overlay.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/display/placeholder.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/display/autorefresh.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/selection/mark-selection.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/addon/search/searchcursor.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/mode/gfm/gfm.js' => [
        'version' => '5.65.18',
    ],
    'codemirror/mode/xml/xml.js' => [
        'version' => '5.65.18',
    ],
    'codemirror-spell-checker' => [
        'version' => '1.1.2',
    ],
    'marked' => [
        'version' => '3.0.8',
    ],
    'typo-js' => [
        'version' => '1.2.5',
    ],
    'codemirror/lib/codemirror.min.css' => [
        'version' => '5.65.18',
        'type' => 'css',
    ],
    'easymde/dist/easymde.min.css' => [
        'version' => '2.20.0',
        'type' => 'css',
    ],
];
