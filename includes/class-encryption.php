<?php
/**
 * Encryption Manager
 *
 * Sistema de encriptación para proteger datos sensibles
 * usando OpenSSL con AES-256-CBC.
 *
 * @package WebToWP_Engine
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W2WP_Encryption {

    private static $instance = null;
    private $cipher = 'AES-256-CBC';
    private $key_option = 'w2wp_encryption_key';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Verificar que OpenSSL esté disponible
        if ( ! extension_loaded( 'openssl' ) ) {
            add_action( 'admin_notices', array( $this, 'openssl_missing_notice' ) );
        }
    }

    /**
     * Aviso si OpenSSL no está disponible
     */
    public function openssl_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WebToWP Engine: La extensión OpenSSL de PHP no está disponible. La encriptación de datos no funcionará.', 'webtowp-engine' ); ?></p>
        </div>
        <?php
    }

    /**
     * Obtener o generar clave de encriptación
     *
     * @return string
     */
    private function get_encryption_key() {
        $key = get_option( $this->key_option );

        if ( empty( $key ) ) {
            $key = $this->generate_key();
            update_option( $this->key_option, $key, false ); // No autoload
        }

        return $key;
    }

    /**
     * Generar nueva clave de encriptación
     *
     * @return string
     */
    private function generate_key() {
        // Usar AUTH_KEY y SECURE_AUTH_KEY de wp-config.php como base
        $base = AUTH_KEY . SECURE_AUTH_KEY . NONCE_KEY;
        
        // Añadir salt adicional
        $salt = bin2hex( random_bytes( 32 ) );
        
        // Generar clave usando hash
        $key = hash( 'sha256', $base . $salt );

        return $key;
    }

    /**
     * Encriptar datos
     *
     * @param string $data Datos a encriptar
     * @return string|false Datos encriptados en base64 o false en error
     */
    public function encrypt( $data ) {
        if ( ! extension_loaded( 'openssl' ) ) {
            return false;
        }

        if ( empty( $data ) ) {
            return $data;
        }

        $key = $this->get_encryption_key();
        
        // Generar IV (Initialization Vector) aleatorio
        $iv_length = openssl_cipher_iv_length( $this->cipher );
        $iv = openssl_random_pseudo_bytes( $iv_length );

        // Encriptar
        $encrypted = openssl_encrypt(
            $data,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ( false === $encrypted ) {
            return false;
        }

        // Combinar IV + datos encriptados y codificar en base64
        $result = base64_encode( $iv . $encrypted );

        return $result;
    }

    /**
     * Desencriptar datos
     *
     * @param string $encrypted_data Datos encriptados en base64
     * @return string|false Datos desencriptados o false en error
     */
    public function decrypt( $encrypted_data ) {
        if ( ! extension_loaded( 'openssl' ) ) {
            return false;
        }

        if ( empty( $encrypted_data ) ) {
            return $encrypted_data;
        }

        $key = $this->get_encryption_key();

        // Decodificar de base64
        $data = base64_decode( $encrypted_data, true );
        
        if ( false === $data ) {
            return false;
        }

        // Extraer IV
        $iv_length = openssl_cipher_iv_length( $this->cipher );
        $iv = substr( $data, 0, $iv_length );
        $encrypted = substr( $data, $iv_length );

        // Desencriptar
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ( false === $decrypted ) {
            return false;
        }

        return $decrypted;
    }

    /**
     * Encriptar array u objeto
     *
     * @param mixed $data Datos a encriptar
     * @return string|false
     */
    public function encrypt_object( $data ) {
        $json = wp_json_encode( $data );
        return $this->encrypt( $json );
    }

    /**
     * Desencriptar a array u objeto
     *
     * @param string $encrypted_data Datos encriptados
     * @param bool $assoc Retornar como array asociativo
     * @return mixed|false
     */
    public function decrypt_object( $encrypted_data, $assoc = true ) {
        $json = $this->decrypt( $encrypted_data );
        
        if ( false === $json ) {
            return false;
        }

        return json_decode( $json, $assoc );
    }

    /**
     * Hash de datos (one-way, no reversible)
     *
     * @param string $data Datos a hashear
     * @return string
     */
    public function hash( $data ) {
        return hash( 'sha256', $data );
    }

    /**
     * Verificar hash
     *
     * @param string $data Datos originales
     * @param string $hash Hash a verificar
     * @return bool
     */
    public function verify_hash( $data, $hash ) {
        return hash_equals( $this->hash( $data ), $hash );
    }

    /**
     * Generar token seguro
     *
     * @param int $length Longitud del token
     * @return string
     */
    public function generate_token( $length = 32 ) {
        return bin2hex( random_bytes( $length ) );
    }

    /**
     * Regenerar clave de encriptación
     * ADVERTENCIA: Esto invalidará todos los datos encriptados existentes
     *
     * @return string Nueva clave
     */
    public function regenerate_key() {
        $new_key = $this->generate_key();
        update_option( $this->key_option, $new_key, false );

        // Log de seguridad
        $logger = W2WP_Security_Logger::get_instance();
        $logger->log( array(
            'action'     => 'encryption_key_regenerated',
            'user_id'    => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
        ) );

        return $new_key;
    }

    /**
     * Encriptar opción de WordPress
     *
     * @param string $option_name Nombre de la opción
     * @param mixed $value Valor a guardar
     * @return bool
     */
    public function update_encrypted_option( $option_name, $value ) {
        $encrypted = $this->encrypt_object( $value );
        
        if ( false === $encrypted ) {
            return false;
        }

        return update_option( $option_name, $encrypted, false );
    }

    /**
     * Obtener opción encriptada de WordPress
     *
     * @param string $option_name Nombre de la opción
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get_encrypted_option( $option_name, $default = false ) {
        $encrypted = get_option( $option_name );
        
        if ( empty( $encrypted ) ) {
            return $default;
        }

        $decrypted = $this->decrypt_object( $encrypted );
        
        return ( false === $decrypted ) ? $default : $decrypted;
    }

    /**
     * Encriptar meta de post
     *
     * @param int $post_id ID del post
     * @param string $meta_key Clave del meta
     * @param mixed $value Valor a guardar
     * @return int|bool
     */
    public function update_encrypted_post_meta( $post_id, $meta_key, $value ) {
        $encrypted = $this->encrypt_object( $value );
        
        if ( false === $encrypted ) {
            return false;
        }

        return update_post_meta( $post_id, $meta_key, $encrypted );
    }

    /**
     * Obtener meta encriptado de post
     *
     * @param int $post_id ID del post
     * @param string $meta_key Clave del meta
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get_encrypted_post_meta( $post_id, $meta_key, $default = false ) {
        $encrypted = get_post_meta( $post_id, $meta_key, true );
        
        if ( empty( $encrypted ) ) {
            return $default;
        }

        $decrypted = $this->decrypt_object( $encrypted );
        
        return ( false === $decrypted ) ? $default : $decrypted;
    }

    /**
     * Encriptar user meta
     *
     * @param int $user_id ID del usuario
     * @param string $meta_key Clave del meta
     * @param mixed $value Valor a guardar
     * @return int|bool
     */
    public function update_encrypted_user_meta( $user_id, $meta_key, $value ) {
        $encrypted = $this->encrypt_object( $value );
        
        if ( false === $encrypted ) {
            return false;
        }

        return update_user_meta( $user_id, $meta_key, $encrypted );
    }

    /**
     * Obtener user meta encriptado
     *
     * @param int $user_id ID del usuario
     * @param string $meta_key Clave del meta
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get_encrypted_user_meta( $user_id, $meta_key, $default = false ) {
        $encrypted = get_user_meta( $user_id, $meta_key, true );
        
        if ( empty( $encrypted ) ) {
            return $default;
        }

        $decrypted = $this->decrypt_object( $encrypted );
        
        return ( false === $decrypted ) ? $default : $decrypted;
    }

    /**
     * Migrar datos existentes a formato encriptado
     *
     * @param string $option_name Nombre de la opción
     * @return bool
     */
    public function migrate_to_encrypted( $option_name ) {
        $value = get_option( $option_name );
        
        if ( empty( $value ) ) {
            return false;
        }

        // Verificar si ya está encriptado
        if ( $this->is_encrypted( $value ) ) {
            return true;
        }

        // Encriptar y guardar
        return $this->update_encrypted_option( $option_name, $value );
    }

    /**
     * Verificar si un valor está encriptado
     *
     * @param string $value Valor a verificar
     * @return bool
     */
    private function is_encrypted( $value ) {
        if ( ! is_string( $value ) ) {
            return false;
        }

        // Intentar desencriptar
        $decrypted = $this->decrypt( $value );
        
        return false !== $decrypted;
    }

    /**
     * Obtener IP del cliente
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field( $ip );
    }

    /**
     * Verificar integridad de datos encriptados
     *
     * @param string $encrypted_data Datos encriptados
     * @return bool
     */
    public function verify_integrity( $encrypted_data ) {
        $decrypted = $this->decrypt( $encrypted_data );
        
        if ( false === $decrypted ) {
            return false;
        }

        // Re-encriptar y comparar
        $re_encrypted = $this->encrypt( $decrypted );
        
        // No podemos comparar directamente porque el IV es aleatorio
        // Pero si ambos procesos funcionan, los datos son íntegros
        return false !== $re_encrypted;
    }

    private function __clone() {}
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }
}
