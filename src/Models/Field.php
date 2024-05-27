<?php

declare( strict_types=1 );

namespace Fieldify\Fields\Models;

class Field {

	public const FIELD_TYPES = [
		'text'           => 'text',
		'file'           => 'file',
		'hidden'         => 'hidden',
		'password'       => 'password',
		'date'           => 'date',
		'datetime-local' => 'datetime-local',
		'email'          => 'email',
		'month'          => 'month',
		'tel'            => 'tel',
		'time'           => 'time',
		'url'            => 'url',
		'week'           => 'week',
		'toggle'         => 'toggle',
		'checkbox'       => 'checkbox',
		'number'         => 'number',
		'radio'          => 'radio',
		'unit'           => 'unit',
		'range'          => 'range',
		'select'         => 'select',
		'textarea'       => 'textarea',
		'image'          => 'image',
		'embed'          => 'embed',
		'gallery'        => 'gallery',
		'icon'           => 'icon',
		'color'          => 'color',
		'repeater'       => 'repeater',
		'license'        => 'license',
		'code'           => 'code',
		'post'           => 'post',
		'html'           => 'html',
	];

	public string $type = 'text';

	public ?string $label = null;

	public string $value;

	public string $placeholder;

	public string $help;

	public int $min = 0;

	public string $max;

	public int $step = 1;

	public array $options;

	public array $optgroups;

	public bool $multiple;

	public int $rows = 4;

	public bool $withInputField = false;

	public array $marks = [];

	public bool $allowReset = false;

	public bool $searchable = false;

	public bool $dynamicSearch = false;

	public bool $creatable = false;

	public bool $showTitle = false;

	public bool $enableAlpha = false;

	public string $name = '';

	public bool $disableCustomColors = false;

	public bool $disableCustomGradients = false;

	public string $endpoint = '';

	public array $query = [];

	public string $content = '';

	public bool $buttons = false;

	/**
	 * Field factory.
	 *
	 * @return self
	 */
	public static function create(): self {
		return new static();
	}
}
