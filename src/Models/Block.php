<?php

declare( strict_types=1 );

namespace Fieldify\Fields\Models;

use Closure;
use Fieldify\Fields\Blocks;
use function add_filter;
use function get_object_vars;
use function str_replace;

class Block {

	public string $name;
	public string $title;
	public string $category;
	public string $icon;
	public string $description;
	public string $textdomain;
	public string $editor_script;
	public string $editor_style;
	public string $style;
	public string $script;
	public string $view_script;
	public string $view_style;
	public string $version;
	public string $render;
	public array  $keywords;
	public array  $supports;
	public array  $attributes;
	public array  $allowed_blocks;
	public array  $ancestors;
	public array  $parent;
	public array  $uses_context;
	public array  $provides_context;
	public array  $variations;
	public array  $selectors;
	public array  $styles;
	public array  $panels;

	/**
	 * Render callback.
	 *
	 * @var callable
	 */
	public $render_callback;

	public static function create( ?string $name = null ): self {
		$instance = new self();

		if ( $name ) {
			$instance->name = $name;
		}

		return $instance;
	}

	public function name( string $name ): self {
		return $this->set( __METHOD__, $name );
	}

	public function title( string $title ): self {
		return $this->set( __METHOD__, $title );
	}

	public function category( string $category ): self {
		return $this->set( __METHOD__, $category );
	}

	public function icon( string $icon ): self {
		return $this->set( __METHOD__, $icon );
	}

	public function description( string $description ): self {
		return $this->set( __METHOD__, $description );
	}

	public function textdomain( string $textdomain ): self {
		return $this->set( __METHOD__, $textdomain );
	}

	public function editor_script( string $editor_script ): self {
		return $this->set( __METHOD__, $editor_script );
	}

	public function editor_style( string $editor_style ): self {
		return $this->set( __METHOD__, $editor_style );
	}

	public function style( string $style ): self {
		return $this->set( __METHOD__, $style );
	}

	public function script( string $script ): self {
		return $this->set( __METHOD__, $script );
	}

	public function view_script( string $view_script ): self {
		return $this->set( __METHOD__, $view_script );
	}

	public function view_style( string $view_style ): self {
		return $this->set( __METHOD__, $view_style );
	}

	public function version( string $version ): self {
		return $this->set( __METHOD__, $version );
	}

	public function render( string $render ): self {
		return $this->set( __METHOD__, $render );
	}

	public function keywords( array $keywords ): self {
		return $this->set( __METHOD__, $keywords );
	}

	public function supports( array $supports ): self {
		return $this->set( __METHOD__, $supports );
	}

	public function attributes( array $attributes ): self {
		return $this->set( __METHOD__, $attributes );
	}

	public function allowed_blocks( array $allowed_blocks ): self {
		return $this->set( __METHOD__, $allowed_blocks );
	}

	public function ancestors( array $ancestors ): self {
		return $this->set( __METHOD__, $ancestors );
	}

	public function parent( array $parent ): self {
		return $this->set( __METHOD__, $parent );
	}

	public function uses_context( array $uses_context ): self {
		return $this->set( __METHOD__, $uses_context );
	}

	public function provides_context( array $provides_context ): self {
		return $this->set( __METHOD__, $provides_context );
	}

	public function variations( array $variations ): self {
		return $this->set( __METHOD__, $variations );
	}

	public function selectors( array $selectors ): self {
		return $this->set( __METHOD__, $selectors );
	}

	public function styles( array $styles ): self {
		return $this->set( __METHOD__, $styles );
	}

	public function panels( array $panels ): self {
		return $this->set( __METHOD__, $panels );
	}

	public function render_callback( Closure $render_callback ): self {
		return $this->set( __METHOD__, $render_callback );
	}

	private function set( string $method, $value ): self {
		$property = str_replace( __CLASS__ . '::', '', $method );
		$instance = $this;

		$instance->{$property} = $value;

		add_filter(
			Blocks::class,
			static function ( array $blocks ) use ( $instance ): array {
				$properties = get_object_vars( $instance );

				$blocks[ $instance->name ] = $properties;

				return $blocks;
			}
		);

		return $instance;
	}

}
