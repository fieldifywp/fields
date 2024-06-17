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

	public string $id;

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

	public string $panel = '';

	/**
	 * Field factory.
	 *
	 * @param ?string $id The field ID.
	 *
	 * @return self
	 */
	public static function create( ?string $id = null ): self {
		$instance = new self();

		if ( $id ) {
			$instance->id = $id;
		}

		return $instance;
	}

	public function id( string $id ): self {
		return $this->set( __METHOD__, $id );
	}

	public function type( string $type ): self {
		return $this->set( __METHOD__, $type );
	}

	public function label( string $label ): self {
		return $this->set( __METHOD__, $label );
	}

	public function value( string $value ): self {
		return $this->set( __METHOD__, $value );
	}

	public function placeholder( string $placeholder ): self {
		return $this->set( __METHOD__, $placeholder );
	}

	public function help( string $help ): self {
		return $this->set( __METHOD__, $help );
	}

	public function min( int $min ): self {
		return $this->set( __METHOD__, $min );
	}

	public function max( string $max ): self {
		return $this->set( __METHOD__, $max );
	}

	public function step( int $step ): self {
		return $this->set( __METHOD__, $step );
	}

	public function options( array $options ): self {
		return $this->set( __METHOD__, $options );
	}

	public function optgroups( array $optgroups ): self {
		return $this->set( __METHOD__, $optgroups );
	}

	public function multiple( bool $multiple ): self {
		return $this->set( __METHOD__, $multiple );
	}

	public function rows( int $rows ): self {
		return $this->set( __METHOD__, $rows );
	}

	public function withInputField( bool $withInputField ): self {
		return $this->set( __METHOD__, $withInputField );
	}

	public function marks( array $marks ): self {
		return $this->set( __METHOD__, $marks );
	}

	public function allowReset( bool $allowReset ): self {
		return $this->set( __METHOD__, $allowReset );
	}

	public function searchable( bool $searchable ): self {
		return $this->set( __METHOD__, $searchable );
	}

	public function dynamicSearch( bool $dynamicSearch ): self {
		return $this->set( __METHOD__, $dynamicSearch );
	}

	public function creatable( bool $creatable ): self {
		return $this->set( __METHOD__, $creatable );
	}

	public function showTitle( bool $showTitle ): self {
		return $this->set( __METHOD__, $showTitle );
	}

	public function enableAlpha( bool $enableAlpha ): self {
		return $this->set( __METHOD__, $enableAlpha );
	}

	public function name( string $name ): self {
		return $this->set( __METHOD__, $name );
	}

	public function disableCustomColors( bool $disableCustomColors ): self {
		return $this->set( __METHOD__, $disableCustomColors );
	}

	public function disableCustomGradients( bool $disableCustomGradients ): self {
		return $this->set( __METHOD__, $disableCustomGradients );
	}

	public function endpoint( string $endpoint ): self {
		return $this->set( __METHOD__, $endpoint );
	}

	public function query( array $query ): self {
		return $this->set( __METHOD__, $query );
	}

	public function content( string $content ): self {
		return $this->set( __METHOD__, $content );
	}

	public function buttons( bool $buttons ): self {
		return $this->set( __METHOD__, $buttons );
	}

	public function panel( string $panel ): self {
		return $this->set( __METHOD__, $panel );
	}

	/**
	 * Set a property.
	 *
	 * @param string $method The method name.
	 * @param mixed  $value  The value.
	 *
	 * @return self
	 */
	private function set( string $method, $value ): self {
		$property = str_replace( __CLASS__ . '::', '', $method );

		$this->{$property} = $value;

		return $this;
	}
}
