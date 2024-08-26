# Uploadit

The unausuming front end asset uploader for Craft 3. Use as a standalone uploader or as field in one of your forms:

## Requirements

This plugin requires Craft CMS 4.11.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Uploadit”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require presseddigital/uploadit

# tell Craft to install the plugin
./craft plugin/install uploadit
```

## Features:

*   Drop to upload
*   Reorder & remove uploads
*   Asset previews
*   Customisable
*   It's Vanilla (Zero dependencies writtin in battle tested javascript)

## Usage

```html
    {{ craft.upload.uploader({
    	id: 'myUid',
    	name: 'myFieldName',
        assets: [],

        field: 'images',
        element: entry,

        volume: 'myvolume',
    	folder: 'my/folder/path',

        preview: 'image',
        transform: 'square',

        limit: 5,
        allowReorder: true,
        allowRemove: true,
        customClass: 'custom--class',

    }) }}
```
