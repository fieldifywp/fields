# Fieldify

Composer package for creating react-powered fields, blocks and settings in the
WordPress editor with only PHP.

## Installation

From your theme or plugin directory:

```bash
composer require fieldify/fields
```

Currently, this package is not available on Packagist. To install from GitHub,
add the following to your composer.json file:

```json
{
	"require": {
		"fieldify/fields": "dev-main"
	},
	"repositories": [
		{
			"type": "git",
			"url": "git@github.com:fieldifywp/fields.git"
		},
		{
			"type": "git",
			"url": "git@github.com:blockifywp/utilities.git"
		}
	]
}
```

## Configuration

To enable the Fieldify package, add the following to your theme or plugin:

```php
// Require the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Configure main plugin file or theme functions.php.
Fieldify::register( __FILE__ );
```

## Usage

### Blocks

Block registration matches WordPress core block registration with the addition
of a 'panels' argument for grouping controls in the block sidebar. Attributes
must specify the 'panel' key to be grouped.

Block names must include a namespace and be in kebab-case in the following
format: `namespace/my-block`.

```php
register_block( 'namespace/my-block', [
	'title'           => __( 'My Block', 'text-domain' ),
	'description'     => __( 'My custom block', 'text-domain' ),
	'category'        => 'custom',
	'icon'            => 'admin-site',
	'keywords'        => [ 'my', 'block' ],
	'render_callback' => static function ( array $attributes, string $content ): string {
		return '<div class="my-block">' . ( $attributes['content'] ?? 'no content' ) . '</div>';
	},
	'style'           => plugin_dir_url( __FILE__ ) . '/assets/my-block.css',
	'supports'        => [
		'color'            => [
			'text'       => true,
			'background' => false,
		],
		'spacing'          => [
			'blockGap' => true,
			'margin'   => true,
		],
	],
	'panels'          => [
		'content' => [
			'title' => 'Content',
		],
	],
	'attributes'      => [
		'verticalAlign'   => [
			'type'    => 'string',
			'toolbar' => 'BlockVerticalAlignmentToolbar',
		],
		'hideContentSetting'  => [
			'type'    => 'boolean',
			'default' => false,
			'panel'   => 'content',
			'control' => 'toggle',
		],
		'content' => [
			'type'    => 'string',
			'default' => 'My block content',
			'panel'   => 'content',
			'control' => 'text',
			'show_if' => [
				[
					'attribute' => 'hideContentSetting',
					'operator'  => '!==',
					'value'     => true,
				],
			],
		],
	],
] );
```

*Attributes*

Block attributes are defined as an associative array with the attribute name as
the key and an array of options as the value.

### Meta Boxes

```php

register_meta_box( 'my-meta-box', [
	'title'      => 'My Meta Box',
	'post_types' => [ 'post' ],
	'context'    => 'side',
	'priority'   => 'default',
	'fields'     => [
		'hideContentSetting' => [
			'default' => false,
			'control' => 'toggle',
		],
		'content' => [
			'label'   => 'Content',
			'control' => 'text',
			'default' => 'My meta box content',
			'show_if' => [
				[
					'field'    => 'hideContentSetting',
					'operator' => '!==',
					'value'    => true,
				],
			],
		],
	],
] );
```

*Fields*

Meta box fields are defined as an associative array with the field name as the
key and an array of options as the value.

### Settings

```php
register_settings('my-settings', [
	'title' => 'My Settings',
	'fields' => [
		'content' => [
			'type' => 'string',
			'default' => 'My settings content',
		],
	],
]);
```

### Supported Controls

#### Core controls

Most WordPress core control component types are supported. Available props can
be found for each component in the WordPress block editor reference guide:

https://developer.wordpress.org/block-editor/reference-guides/components/

- text
- toggle
- checkbox
- number
- unit
- range
- textarea
- select - (with additional support
  for [React Select](https://react-select.com/home) props,
  e.g. `creatable`, `searchable`, `multiple`, etc.)

#### Custom controls

- image
- embed
- gallery
- icon
- color
- repeater
	- subfields: *array*
	- sortable: *boolean*
	- direction: *string* (row|column)

### Utility functions

- **register_block**: *string $id, array $args*
- **register_meta_box**: *string $id, array $args*
- **register_settings**: *string $id, array $args*
- **get_icon**: *string $set, string $name, $size = null*
- **is_rendering_preview**
