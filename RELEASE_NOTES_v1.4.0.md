# Release Notes - v1.4.0

## 🔒 WebToWP Engine v1.4.0 - Security Release

**Fecha de Release:** 1 de Febrero, 2024  
**Tipo:** Security & Feature Release  
**Compatibilidad:** WordPress 5.8+, PHP 7.4+

Esta versión añade características avanzadas de seguridad con más de **2,350 líneas de código nuevo**, **4 archivos creados** y mejoras masivas en protección, auditoría y gestión de acceso a la API.

---

## 🌟 Highlights

### 🔑 Gestión Avanzada de API Keys
Sistema completo para gestionar múltiples API keys con permisos granulares, expiración, rate limiting y tracking de uso. Cada key puede tener permisos específicos y límites personalizados.

### 🚫 Rate Limiting y Protección DDoS
Protección automática contra abuso de API con rate limiting configurable, lista negra de IPs, detección de patrones sospechosos y auto-bloqueo por intentos fallidos.

### 🔐 Encriptación AES-256-CBC
Sistema de encriptación de nivel militar para proteger datos sensibles con métodos helper para opciones, post meta y user meta de WordPress.

### 📊 Auditoría de Seguridad Completa
Registro detallado de todos los eventos de seguridad con 4 niveles de severidad, estadísticas completas, detección de IPs sospechosas y alertas por email para eventos críticos.

---

## 📦 ¿Qué hay de nuevo?

### Gestión de API Keys 🔑

**Características:**
- ✅ Múltiples keys por instalación
- ✅ Generación segura (w2wp_ + 64 caracteres aleatorios)
- ✅ Hash SHA-256 para almacenamiento
- ✅ 7 tipos de permisos granulares:
  - `read` - Lectura (GET)
  - `write` - Escritura (POST, PUT, PATCH)
  - `delete` - Eliminación (DELETE)
  - `deploy` - Trigger de deployment
  - `cache` - Gestión de caché
  - `settings` - Modificar configuración
  - `*` - Todos los permisos
- ✅ Rate limit configurable (1-1000 req/min)
- ✅ Expiración opcional (en días)
- ✅ Tracking de uso (contador, última IP, timestamp)
- ✅ Activación/desactivación manual
- ✅ Desactivación automática al expirar
- ✅ Estadísticas detalladas por key

**Uso:**
```php
// Generar nueva key
$key_manager = W2WP_API_Key_Manager::get_instance();
$result = $key_manager->generate_key( array(
    'name'        => 'Frontend App',
    'permissions' => array( 'read', 'deploy' ),
    'rate_limit'  => 100,
    'expires_in'  => 365, // 1 año
) );

// Validar key
$key_data = $key_manager->validate_key( $api_key );
if ( $key_data && $key_manager->has_permission( $key_data, 'read' ) ) {
    // Permitir acceso
}
```

---

### Rate Limiting 🚫

**Características:**
- ✅ Protección contra DDoS
- ✅ Ventanas deslizantes para conteo preciso
- ✅ Límites por API key (configurable)
- ✅ Límites por IP para no autenticados (20/min)
- ✅ Headers HTTP estándar:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`
  - `Retry-After`
- ✅ Error 429 con información de retry
- ✅ Lista negra de IPs
- ✅ Auto-bloqueo por intentos fallidos
- ✅ Detección de IPs sospechosas

**Respuesta cuando se excede:**
```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit excedido. Intenta nuevamente en 45 segundos.",
  "data": {
    "status": 429
  }
}
```

**Headers de respuesta:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1706789456
Retry-After: 45
```

---

### Encriptación 🔐

**Características:**
- ✅ AES-256-CBC (estándar militar)
- ✅ IV aleatorio por encriptación
- ✅ Clave basada en WordPress salts
- ✅ Métodos para strings y objetos
- ✅ Hash one-way SHA-256
- ✅ Helpers para WordPress (opciones, meta)
- ✅ Regeneración de clave
- ✅ Verificación de integridad

**Uso:**
```php
$encryption = W2WP_Encryption::get_instance();

// Encriptar string
$encrypted = $encryption->encrypt( 'datos sensibles' );
$decrypted = $encryption->decrypt( $encrypted );

// Encriptar opción
$encryption->update_encrypted_option( 'mi_opcion_secreta', array(
    'api_key' => 'xxx',
    'secret'  => 'yyy',
) );

$data = $encryption->get_encrypted_option( 'mi_opcion_secreta' );

// Hash one-way
$hash = $encryption->hash( 'password' );
$valid = $encryption->verify_hash( 'password', $hash );
```

---

### Auditoría de Seguridad 📊

**Características:**
- ✅ Registro de todos los eventos
- ✅ 4 niveles de severidad (low, medium, high, critical)
- ✅ Tracking de IP, user agent, endpoint
- ✅ Metadata extensible (JSON)
- ✅ Estadísticas completas (30 días)
- ✅ Detección de IPs sospechosas
- ✅ Exportación a CSV
- ✅ Limpieza automática (90 días)
- ✅ Alertas por email (eventos críticos)

**Uso:**
```php
$logger = W2WP_Security_Logger::get_instance();

// Log manual
$logger->log( array(
    'action'   => 'custom_action',
    'severity' => 'medium',
    'metadata' => array( 'key' => 'value' ),
) );

// Métodos helper
$logger->log_api_request( $key_id, '/endpoint', true );
$logger->log_auth_failed( 'username' );
$logger->log_suspicious_activity( 'Descripción', array( 'data' => 'value' ) );

// Obtener estadísticas
$stats = $logger->get_stats( 30 ); // Últimos 30 días

// IPs sospechosas
$suspicious = $logger->get_suspicious_ips( 7, 10 ); // 7 días, mínimo 10 eventos
```

---

### Página de Administración 🎛️

Nueva página **WebToWP → Seguridad** con 4 tabs:

#### Tab 1: API Keys
- Estadísticas en tiempo real
- Formulario de generación con configuración completa
- Tabla de keys existentes con todas las métricas
- Acciones: desactivar, eliminar

#### Tab 2: Rate Limiting
- Estadísticas de bloqueos
- Formulario para bloquear IPs manualmente
- Tabla de IPs sospechosas (auto-detectadas)
- Lista negra con opción de desbloqueo

#### Tab 3: Logs de Seguridad
- Estadísticas por severidad
- Tabla de eventos recientes (últimos 50)
- Badges de color por severidad
- Información completa de cada evento

#### Tab 4: Encriptación
- Estado de OpenSSL
- Información sobre el algoritmo
- Regeneración de clave (con advertencias)
- Guía de uso

---

## 🔒 Mejoras de Seguridad

### Protección de API
- ✅ Autenticación mejorada con múltiples keys
- ✅ Permisos granulares por endpoint
- ✅ Rate limiting automático
- ✅ Detección de patrones de ataque
- ✅ Bloqueo automático de IPs maliciosas

### Protección de Datos
- ✅ Encriptación de datos sensibles
- ✅ Hash seguro de API keys
- ✅ IV aleatorio por encriptación
- ✅ Verificación de integridad

### Auditoría
- ✅ Log completo de eventos
- ✅ Tracking de todas las acciones
- ✅ Alertas automáticas
- ✅ Análisis de patrones

---

## 📊 Estadísticas del Release

| Métrica | Valor |
|---------|-------|
| Líneas de código añadidas | ~2,350+ |
| Archivos creados | 4 |
| Archivos modificados | 3 |
| Nuevas clases PHP | 4 |
| Nuevas tablas BD | 2 |
| Métodos públicos | 60+ |
| Permisos disponibles | 7 |
| Tabs en UI | 4 |

---

## 🔧 Instalación y Actualización

### Nuevos Usuarios

1. **Descarga e instala el plugin**
2. **Activa el plugin** (se crearán las tablas automáticamente)
3. **Ve a WebToWP → Seguridad**
4. **Genera tu primera API Key**

### Actualización desde v1.3.0

1. **Haz un backup** de tu sitio (recomendado)
2. **Actualiza el plugin**
3. **Las tablas se crean automáticamente:**
   - `wp_w2wp_api_keys`
   - `wp_w2wp_security_logs`
4. **Migra tus API keys existentes:**
   - Ve a WebToWP → Seguridad → API Keys
   - Genera nuevas keys con los permisos necesarios
   - Actualiza tu frontend con las nuevas keys
   - Desactiva las keys antiguas

**No hay breaking changes** - El sistema antiguo de API keys sigue funcionando, pero se recomienda migrar al nuevo sistema para aprovechar las nuevas características.

---

## 🆕 Nuevas Tablas de Base de Datos

### wp_w2wp_api_keys
Almacena las API keys con toda su configuración:
- ID, nombre, hash, prefijo
- Permisos (JSON)
- Rate limit
- Fechas (creación, expiración, último uso)
- Tracking (IP, contador de uso)
- Estado (activa/inactiva)

### wp_w2wp_security_logs
Almacena todos los eventos de seguridad:
- ID, acción, severidad
- User ID, Key ID
- IP, user agent, endpoint
- Metadata (JSON)
- Timestamp

---

## 📚 Documentación

### Nuevos Documentos
- **Guía de API Keys** - Cómo generar y gestionar keys
- **Guía de Rate Limiting** - Configuración y mejores prácticas
- **Guía de Encriptación** - Uso de métodos de encriptación

### Documentos Actualizados
- **[CHANGELOG.md](CHANGELOG.md)** - Historial completo actualizado
- **[README.md](README.md)** - Características de seguridad añadidas
- **[SECURITY.md](SECURITY.md)** - Nuevas prácticas de seguridad
- **[API-DOCUMENTATION.md](API-DOCUMENTATION.md)** - Autenticación actualizada

---

## 🎯 Casos de Uso

### Caso 1: Múltiples Frontends
```php
// Key para frontend público (solo lectura)
$public_key = $key_manager->generate_key( array(
    'name'        => 'Frontend Público',
    'permissions' => array( 'read' ),
    'rate_limit'  => 100,
) );

// Key para panel admin (todos los permisos)
$admin_key = $key_manager->generate_key( array(
    'name'        => 'Panel Admin',
    'permissions' => array( '*' ),
    'rate_limit'  => 500,
) );

// Key para CI/CD (solo deploy)
$deploy_key = $key_manager->generate_key( array(
    'name'        => 'GitHub Actions',
    'permissions' => array( 'deploy', 'cache' ),
    'rate_limit'  => 50,
    'expires_in'  => 90, // 3 meses
) );
```

### Caso 2: Protección contra Ataques
```php
// El sistema detecta automáticamente:
// - Rate limit excedido → Error 429
// - Intentos fallidos repetidos → Auto-bloqueo
// - Patrones sospechosos → Log + alerta
// - IPs maliciosas → Blacklist

// Bloqueo manual de IP
$rate_limiter->blacklist_ip( '192.168.1.100', 'Actividad sospechosa' );

// Ver IPs sospechosas
$suspicious = $logger->get_suspicious_ips( 7, 5 );
foreach ( $suspicious as $ip_data ) {
    // Revisar y bloquear si es necesario
}
```

### Caso 3: Datos Sensibles
```php
// Encriptar configuración sensible
$encryption->update_encrypted_option( 'payment_gateway_config', array(
    'api_key'    => 'sk_live_xxx',
    'secret_key' => 'xxx',
) );

// Recuperar datos encriptados
$config = $encryption->get_encrypted_option( 'payment_gateway_config' );
```

---

## ⚠️ Breaking Changes

**Ninguno.** Esta versión es completamente compatible con v1.3.0.

El sistema antiguo de API keys sigue funcionando, pero se recomienda migrar al nuevo sistema.

---

## 🐛 Bugs Conocidos

Ninguno reportado hasta el momento.

---

## 🔜 Próximas Versiones

### v1.5.0 (Q2 2024)
- [ ] Tests unitarios con PHPUnit
- [ ] Tests E2E con Playwright
- [ ] Soporte para ACF Free (opcional)
- [ ] Más módulos (E-commerce, Membresías)

### v2.0.0 (Q3 2024)
- [ ] Soporte para GraphQL
- [ ] Webhooks personalizados
- [ ] Analytics integrado
- [ ] CLI para gestión desde terminal

---

## 🙏 Agradecimientos

Gracias a la comunidad por el feedback y sugerencias que hicieron posible esta versión.

---

## 📞 Soporte

¿Necesitas ayuda?

- **GitHub Issues:** [Crear Issue](https://github.com/Vannit0/webtowp-engine/issues)
- **Email:** info@webtowp.com
- **Documentación:** [API Docs](API-DOCUMENTATION.md)

---

## 💬 Feedback

Tu opinión es importante. Si encuentras algún problema o tienes sugerencias:

1. Abre un issue en GitHub
2. Envíanos un email
3. Contribuye con un Pull Request

---

## 📄 Licencia

GPL v2 o posterior. Ver [LICENSE](LICENSE) para más detalles.

---

**¡Disfruta de WebToWP Engine v1.4.0 con seguridad mejorada!** 🔒

**⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!**
