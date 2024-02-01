# Fieldify

Composer package for creating react-powered fields, blocks and settings in the
WordPress editor with only PHP.

## Installation

From your theme or plugin directory:

```bash
composer require fieldify/fields
```

## Usage

### Registration

#### Blocks

Block registration matches WordPress core block registration with the addition
of a 'panels' argument for grouping controls in the block sidebar. Attributes
must specify the 'panel' key to be grouped.

Block names must include a namespace and be in kebab-case in the following
format: `namespace/my-block`.

```php
register_block("namespace/my-block", [
	"title" => "My Block",
	"description" => "My block description",
	"category" => "common",
	"icon" => "admin-site",
	"keywords" => ["my", "block"],
	"render_callback" => function ($attributes, $content) {
		return "<div class='my-block'>" . $content . "</div>";
	},
	"panels" => [
		"content" => [
			"title" => "Content",
			"initialOpen" => false,
			"className" => "my-block-content-panel-extra-class-name",
		],
	],
	"attributes" => [
		"content" => [
			"type" => "string",
			"default" => "My block content",
			"panel" => "content",
		],
	],
]);
```

*Attributes*

Block attributes are defined as an associative array with the attribute name as
the key and an array of options as the value.

### Meta Boxes

```php
register_meta_box('my-meta-box', [
	'title' => 'My Meta Box',
	'post_types' => ['post'],
	'context' => 'side',
	'priority' => 'default',
	'callback' => function ($post) {
		echo '<p>My meta box content</p>';
	},
	'fields' => [
		'content' => [
			'type' => 'string',
			'default' => 'My meta box content',
		],
	],
]);
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

*Core controls*

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

*Custom controls*

- image
- embed
- gallery
- icon
- color
- repeater
	- subfields: *array*
	- sortable: *boolean*
	- direction: *string* (row|column)
