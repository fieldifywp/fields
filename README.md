# Fieldify

Composer package for creating react-powered fields, blocks and settings in the
WordPress editor with only PHP.

## Installation

From your theme or plugin directory:

```bash
composer require fieldify/fields
```

## Usage

### Blocks

```php
register_block('my-block', [
	'title' => 'My Block',
	'description' => 'My block description',
	'category' => 'common',
	'icon' => 'admin-site',
	'keywords' => ['my', 'block'],
	'render_callback' => function ($attributes, $content) {
		return '<div class="my-block">' . $content . '</div>';
	},
	'attributes' => [
		'content' => [
			'type' => 'string',
			'default' => 'My block content',
		],
	],
]);

```

### Metaboxes

```php
register_meta_box('my-metabox', [
	'title' => 'My Metabox',
	'screen' => 'post',
	'context' => 'side',
	'priority' => 'default',
	'callback' => function ($post) {
		echo '<p>My metabox content</p>';
	},
	'fields' => [
		'content' => [
			'type' => 'string',
			'default' => 'My metabox content',
		],
	],
]);
```

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
])
```
