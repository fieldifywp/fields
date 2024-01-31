# Fieldify

Composer package for creating react-powered fields, blocks and settings in the WordPress editor with only PHP.

## Installation

From your theme or plugin directory:

```bash
composer require fieldify/fields
```

## Usage

### Blocks

```php
add_filter('fieldify', function ($fields) {
	$fields['my-field'] = [
		'title' => 'My Field',
		'description' => 'My field description',
		'category' => 'common',
		'icon' => 'admin-site',
		'keywords' => ['my', 'field'],
		'render_callback' => function ($attributes, $content) {
			return '<div class="my-field">' . $content . '</div>';
		},
		'attributes' => [
			'content' => [
				'type' => 'string',
				'default' => 'My field content',
			],
		],
	];

	return $fields;
});
```

### Metaboxes

```php
add_filter('fieldify', function ($fields) {
	$fields['my-field'] = [
		'title' => 'My Field',
		'description' => 'My field description',
		'category' => 'common',
		'icon' => 'admin-site',
		'keywords' => ['my', 'field'],
		'render_callback' => function ($attributes, $content) {
			return '<div class="my-field">' . $content . '</div>';
		},
		'attributes' => [
			'content' => [
				'type' => 'string',
				'default' => 'My field content',
			],
		],
	];

	return $fields;
});
```

### Settings

```php

```
