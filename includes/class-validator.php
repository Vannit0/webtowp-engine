<?php
/**
 * Validator Class
 * 
 * Proporciona métodos de validación y sanitización para todos los inputs del plugin.
 *
 * @package WebToWP_Engine
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Validator {

    /**
     * Valida y sanitiza una URL
     */
    public static function sanitize_url( $url ) {
        if ( empty( $url ) ) {
            return '';
        }
        return esc_url_raw( $url );
    }

    /**
     * Valida y sanitiza un email
     */
    public static function sanitize_email( $email ) {
        if ( empty( $email ) ) {
            return '';
        }
        return sanitize_email( $email );
    }

    /**
     * Valida que un email sea válido
     */
    public static function is_valid_email( $email ) {
        return is_email( $email );
    }

    /**
     * Valida y sanitiza texto simple
     */
    public static function sanitize_text( $text ) {
        if ( empty( $text ) ) {
            return '';
        }
        return sanitize_text_field( $text );
    }

    /**
     * Valida y sanitiza textarea
     */
    public static function sanitize_textarea( $text ) {
        if ( empty( $text ) ) {
            return '';
        }
        return sanitize_textarea_field( $text );
    }

    /**
     * Valida y sanitiza código hexadecimal de color
     */
    public static function sanitize_hex_color( $color ) {
        if ( empty( $color ) ) {
            return '';
        }
        
        // Remover espacios
        $color = trim( $color );
        
        // Asegurar que $color no sea null antes de usar strpos
        if ( ! is_string( $color ) || $color === '' ) {
            return '';
        }
        
        // Agregar # si no lo tiene
        if ( strpos( $color, '#' ) !== 0 ) {
            $color = '#' . $color;
        }
        
        // Validar formato hexadecimal
        if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
            return $color;
        }
        
        return '';
    }

    /**
     * Valida y sanitiza un número de teléfono
     */
    public static function sanitize_phone( $phone ) {
        if ( empty( $phone ) ) {
            return '';
        }
        
        // Permitir solo números, espacios, guiones, paréntesis y el símbolo +
        return preg_replace( '/[^0-9\s\-\(\)\+]/', '', $phone );
    }

    /**
     * Valida formato de número de teléfono internacional
     */
    public static function is_valid_phone( $phone ) {
        if ( empty( $phone ) ) {
            return false;
        }
        
        // Formato internacional básico: +[código país][número]
        return preg_match( '/^\+?[0-9\s\-\(\)]{7,20}$/', $phone );
    }

    /**
     * Valida y sanitiza API Key
     */
    public static function sanitize_api_key( $key ) {
        if ( empty( $key ) ) {
            return '';
        }
        
        // Solo permitir caracteres alfanuméricos, guiones y guiones bajos
        return preg_replace( '/[^a-zA-Z0-9\-_]/', '', $key );
    }

    /**
     * Genera una API Key segura
     */
    public static function generate_api_key( $length = 32 ) {
        return bin2hex( random_bytes( $length / 2 ) );
    }

    /**
     * Valida formato de API Key
     */
    public static function is_valid_api_key( $key ) {
        if ( empty( $key ) ) {
            return false;
        }
        
        // Debe tener al menos 16 caracteres y solo alfanuméricos, guiones y guiones bajos
        return strlen( $key ) >= 16 && preg_match( '/^[a-zA-Z0-9\-_]+$/', $key );
    }

    /**
     * Valida y sanitiza un slug
     */
    public static function sanitize_slug( $slug ) {
        if ( empty( $slug ) ) {
            return '';
        }
        return sanitize_title( $slug );
    }

    /**
     * Valida y sanitiza HTML permitido
     */
    public static function sanitize_html( $html, $allowed_tags = null ) {
        if ( empty( $html ) ) {
            return '';
        }
        
        if ( $allowed_tags === null ) {
            // Tags permitidos por defecto
            $allowed_tags = array(
                'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'p' => array(),
                'ul' => array(),
                'ol' => array(),
                'li' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
            );
        }
        
        return wp_kses( $html, $allowed_tags );
    }

    /**
     * Valida y sanitiza un número entero
     */
    public static function sanitize_int( $number ) {
        return absint( $number );
    }

    /**
     * Valida y sanitiza un número flotante
     */
    public static function sanitize_float( $number ) {
        return floatval( $number );
    }

    /**
     * Valida y sanitiza un booleano
     */
    public static function sanitize_bool( $value ) {
        return (bool) $value;
    }

    /**
     * Valida y sanitiza un array de strings
     */
    public static function sanitize_array( $array ) {
        if ( ! is_array( $array ) ) {
            return array();
        }
        
        return array_map( 'sanitize_text_field', $array );
    }

    /**
     * Valida que un valor esté en una lista de opciones permitidas
     */
    public static function validate_in_array( $value, $allowed_values ) {
        return in_array( $value, $allowed_values, true );
    }

    /**
     * Valida longitud mínima de un string
     */
    public static function validate_min_length( $string, $min_length ) {
        return strlen( $string ) >= $min_length;
    }

    /**
     * Valida longitud máxima de un string
     */
    public static function validate_max_length( $string, $max_length ) {
        return strlen( $string ) <= $max_length;
    }

    /**
     * Valida que un campo no esté vacío
     */
    public static function validate_required( $value ) {
        if ( is_string( $value ) ) {
            return ! empty( trim( $value ) );
        }
        return ! empty( $value );
    }

    /**
     * Valida formato de fecha
     */
    public static function validate_date( $date, $format = 'Y-m-d' ) {
        $d = DateTime::createFromFormat( $format, $date );
        return $d && $d->format( $format ) === $date;
    }

    /**
     * Sanitiza un ID de imagen/attachment
     */
    public static function sanitize_attachment_id( $id ) {
        $id = absint( $id );
        
        // Verificar que el attachment existe
        if ( $id > 0 && get_post_type( $id ) === 'attachment' ) {
            return $id;
        }
        
        return 0;
    }

    /**
     * Valida y sanitiza un JSON
     */
    public static function sanitize_json( $json ) {
        if ( empty( $json ) ) {
            return '';
        }
        
        // Intentar decodificar
        $decoded = json_decode( $json, true );
        
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return '';
        }
        
        // Re-encodear para asegurar formato válido
        return wp_json_encode( $decoded );
    }

    /**
     * Valida un nonce
     */
    public static function verify_nonce( $nonce, $action ) {
        return wp_verify_nonce( $nonce, $action );
    }

    /**
     * Valida capacidad del usuario actual
     */
    public static function verify_capability( $capability = 'manage_options' ) {
        return current_user_can( $capability );
    }

    /**
     * Valida múltiples campos a la vez
     * 
     * @param array $data Array de datos a validar
     * @param array $rules Array de reglas de validación
     * @return array Array con 'valid' (bool) y 'errors' (array)
     */
    public static function validate_fields( $data, $rules ) {
        $errors = array();
        
        foreach ( $rules as $field => $field_rules ) {
            $value = isset( $data[ $field ] ) ? $data[ $field ] : '';
            
            foreach ( $field_rules as $rule => $params ) {
                switch ( $rule ) {
                    case 'required':
                        if ( ! self::validate_required( $value ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s es requerido.', 'webtowp-engine' ), $field );
                        }
                        break;
                        
                    case 'email':
                        if ( ! empty( $value ) && ! self::is_valid_email( $value ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s debe ser un email válido.', 'webtowp-engine' ), $field );
                        }
                        break;
                        
                    case 'url':
                        if ( ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s debe ser una URL válida.', 'webtowp-engine' ), $field );
                        }
                        break;
                        
                    case 'min_length':
                        if ( ! self::validate_min_length( $value, $params ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s debe tener al menos %d caracteres.', 'webtowp-engine' ), $field, $params );
                        }
                        break;
                        
                    case 'max_length':
                        if ( ! self::validate_max_length( $value, $params ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s no debe exceder %d caracteres.', 'webtowp-engine' ), $field, $params );
                        }
                        break;
                        
                    case 'in_array':
                        if ( ! self::validate_in_array( $value, $params ) ) {
                            $errors[ $field ][] = sprintf( __( 'El campo %s contiene un valor no válido.', 'webtowp-engine' ), $field );
                        }
                        break;
                }
            }
        }
        
        return array(
            'valid' => empty( $errors ),
            'errors' => $errors,
        );
    }
}
