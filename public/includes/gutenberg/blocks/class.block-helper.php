<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ESHB_Block_Helper {

	public static function add_responsive_vars ($attributes, &$target_array, $attr_base, $prop_name, $properties = [], $is_object = false) {
   		$devices = ['' => 'desktop', 'Tablet' => 'tablet', 'Mobile' => 'mobile'];
    
    	foreach ($devices as $d_suffix => $device) {
			$attr_name = $attr_base . $d_suffix;
			$val = isset($attributes[$attr_name]) && $attributes[$attr_name] !== '' ? $attributes[$attr_name] : null;
			
			if ($is_object && is_array($val)) {
				foreach ($properties as $prop_key => $css_prop) {
					if ( isset( $val[$prop_key] ) && $val[$prop_key] !== '' ) {
						$v = $val[$prop_key];
						if ( in_array( $prop_key, ['top', 'right', 'bottom', 'left', 'fontSize', 'letterSpacing', 'itemGap'] ) ) {
							$v = self::ensure_unit($v);
						}
						$target_array[$device][$css_prop] = $v;
					}
				}
			} elseif ( ! $is_object && ! empty( $val ) ) {
				$v = $val;
				if ( in_array($attr_base, ['itemGap', 'itemColGap', 'itemRowGap']) ) {
                     // handled per block usually, but let's store it
					 $v = self::ensure_unit($v);
                }
				$target_array[$device][$prop_name] = $v;
			}
		}
	}

	public static function ensure_unit ($value) {
		if ( $value === '' || $value === null ) return '0px';
		if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
		return $value;
	}

	public static function get_inline_styles ($style_map) {
		$styles = [];
		foreach ( $style_map as $prop => $value ) {
			//if ( $value !== '' && $value !== null && $value !== 'inherit' && $value !== '0px' ) {
				$styles[] = $prop . ':' . $value;
			//}
		}
		return implode( ';', $styles );
	}

	public static function generate_responsive_css($selector, $responsive_data) {
		$css = "";
		$breakpoints = [
			'desktop' => '',
			'tablet'  => '@media (max-width: 1024px)',
			'mobile'  => '@media (max-width: 767px)'
		];

		foreach ($breakpoints as $device => $media) {
			if (!empty($responsive_data[$device])) {
				$decls = "";
				foreach ($responsive_data[$device] as $prop => $val) {
					$decls .= $prop . ":" . wp_strip_all_tags( $val ) . ";";
				}
				if ($media) {
					$css .= $media . " { " . $selector . " { " . $decls . " } }\n";
				} else {
					$css .= $selector . " { " . $decls . " }\n";
				}
			}
		}
		return $css;
	}

	public static function add_custom_style( $handle, $selector, $responsive_css = "", $sub_styles = [] ) {
		$css = $responsive_css;
		
		foreach ( $sub_styles as $sub_sel => $style ) {
			if ( ! empty( $style ) ) {
				 $css .= $selector . " " . $sub_sel . " { " . $style . "; }\n";
			}
		}

		if ( ! empty( $css ) ) {
			//if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS values are sanitized via wp_strip_all_tags() during generation
				echo '<style>' . $css . '</style>';
			//} else {
			//	wp_add_inline_style( $handle, $css );
			//}
		}
	}

	public static function box_shadow_to_css($shadow) {
		$x = self::ensure_unit($shadow['x'] ?? 0);
		$y = self::ensure_unit($shadow['y'] ?? 0);
		$b = self::ensure_unit($shadow['b'] ?? 0);
		$s = self::ensure_unit($shadow['s'] ?? 0);
		$c = $shadow['c'] ?? 'rgba(0,0,0,0)';
		return "$x $y $b $s $c";
	}

	public static function border_to_css($border) {
		$width = self::ensure_unit($border['width'] ?? 0);
		$style = $border['style'] ?? 'solid';
		$color = $border['color'] ?? 'rgba(0,0,0,0)';
		return "$width $style $color";
	}
}