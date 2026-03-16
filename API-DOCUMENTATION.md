# WebToWP Engine - API REST Documentation

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Autenticación](#autenticación)
3. [Endpoints Disponibles](#endpoints-disponibles)
4. [Ejemplos de Uso](#ejemplos-de-uso)
5. [Códigos de Error](#códigos-de-error)
6. [Rate Limiting](#rate-limiting)

---

## 🔐 Introducción

WebToWP Engine proporciona una API REST completa para acceder a la configuración del sitio, ajustes globales y información de debug desde aplicaciones frontend headless.

**Base URL:** `https://tu-sitio.com/wp-json/webtowp/v1/`

**Formato de Respuesta:** JSON

**Caché:** Las respuestas están cacheadas por 1 hora para optimizar el rendimiento.

---

## 🔑 Autenticación

Todos los endpoints requieren autenticación mediante API Key en el header de la petición.

### Header Requerido

```
X-WebToWP-Key: tu_api_key_aqui
```

### Obtener tu API Key

1. Ve a **WebToWP > Despliegue & API** en el admin de WordPress
2. Haz clic en **"Generar Nueva Clave"**
3. Copia la clave generada
4. Guárdala de forma segura (no se puede recuperar después)

---

## 📡 Endpoints Disponibles

### 1. Site Info

Obtiene información básica del sitio.

**Endpoint:** `GET /wp-json/webtowp/v1/site-info`

**Headers:**
```http
X-WebToWP-Key: tu_api_key
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "name": "Mi Sitio Web",
    "description": "Descripción del sitio",
    "url": "https://mi-sitio.com",
    "logo": "https://mi-sitio.com/wp-content/uploads/logo.png",
    "admin_email": "admin@mi-sitio.com",
    "language": "es-ES",
    "charset": "UTF-8",
    "version": "6.4.2",
    "plugin_version": "1.2.0"
  },
  "timestamp": "2024-01-15 10:30:00"
}
```

**Campos:**
- `name` (string): Nombre del sitio
- `description` (string): Descripción del sitio
- `url` (string): URL del sitio
- `logo` (string|null): URL del logo del sitio
- `admin_email` (string): Email del administrador
- `language` (string): Código de idioma
- `charset` (string): Codificación de caracteres
- `version` (string): Versión de WordPress
- `plugin_version` (string): Versión del plugin

---

### 2. Global Settings

Obtiene todos los ajustes globales configurados en el plugin.

**Endpoint:** `GET /wp-json/webtowp/v1/settings`

**Headers:**
```http
X-WebToWP-Key: tu_api_key
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "brand_identity": {
      "logo_principal": "https://mi-sitio.com/wp-content/uploads/logo.png",
      "logo_contraste": "https://mi-sitio.com/wp-content/uploads/logo-white.png",
      "favicon": "https://mi-sitio.com/wp-content/uploads/favicon.ico",
      "brand_name": "Mi Marca",
      "copyright_text": "© 2024 Mi Marca. Todos los derechos reservados."
    },
    "colors": {
      "primary": "#667eea",
      "secondary": "#764ba2"
    },
    "communication": {
      "whatsapp": "+34123456789",
      "support_email": "soporte@mi-sitio.com",
      "physical_address": "Calle Principal 123, Madrid, España"
    },
    "social_networks": {
      "instagram": "https://instagram.com/mi-marca",
      "linkedin": "https://linkedin.com/company/mi-marca",
      "facebook": "https://facebook.com/mi-marca",
      "twitter": "https://twitter.com/mi-marca",
      "youtube": "https://youtube.com/@mi-marca"
    },
    "marketing": {
      "google_analytics_id": "G-XXXXXXXXXX",
      "facebook_pixel_id": "123456789"
    },
    "headless": {
      "frontend_url": "https://frontend.mi-sitio.com"
    },
    "scripts": {
      "header": "<script>/* Header scripts */</script>",
      "footer": "<script>/* Footer scripts */</script>"
    },
    "branding": {
      "signature_text": "Desarrollado por WebToWP",
      "signature_url": "https://webtowp.com",
      "html": "<a href=\"https://webtowp.com\" target=\"_blank\" rel=\"noopener\">Desarrollado por WebToWP</a>"
    },
    "modules": {
      "informativo": true,
      "landing": false
    }
  },
  "timestamp": "2024-01-15 10:30:00"
}
```

**Secciones:**

#### Brand Identity
- `logo_principal` (string|null): URL del logo principal
- `logo_contraste` (string|null): URL del logo en contraste
- `favicon` (string|null): URL del favicon
- `brand_name` (string|null): Nombre de la marca
- `copyright_text` (string|null): Texto de copyright

#### Colors
- `primary` (string): Color primario (hexadecimal)
- `secondary` (string): Color secundario (hexadecimal)

#### Communication
- `whatsapp` (string|null): Número de WhatsApp
- `support_email` (string|null): Email de soporte
- `physical_address` (string|null): Dirección física

#### Social Networks
- `instagram` (string|null): URL de Instagram
- `linkedin` (string|null): URL de LinkedIn
- `facebook` (string|null): URL de Facebook
- `twitter` (string|null): URL de Twitter
- `youtube` (string|null): URL de YouTube

#### Marketing
- `google_analytics_id` (string|null): ID de Google Analytics
- `facebook_pixel_id` (string|null): ID de Facebook Pixel

#### Headless
- `frontend_url` (string|null): URL del frontend headless

#### Scripts
- `header` (string|null): Scripts para el header
- `footer` (string|null): Scripts para el footer

#### Branding
- `signature_text` (string): Texto de firma
- `signature_url` (string): URL de firma
- `html` (string): HTML de firma renderizado

#### Modules
- `informativo` (boolean): Estado del módulo informativo
- `landing` (boolean): Estado del módulo landing

---

### 3. Debug Info

Obtiene información de debug del sistema (solo para desarrollo).

**Endpoint:** `GET /wp-json/webtowp/v1/debug`

**Headers:**
```http
X-WebToWP-Key: tu_api_key
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "webhook_configured": true,
    "cors_configured": true,
    "acf_active": true,
    "php_version": "8.1.0",
    "wp_version": "6.4.2",
    "plugin_version": "1.2.0",
    "active_modules": ["informativo"],
    "cache_stats": {
      "total_items": 15,
      "total_size": 45678
    }
  },
  "timestamp": "2024-01-15 10:30:00"
}
```

---

## 💡 Ejemplos de Uso

### JavaScript (Fetch API)

```javascript
const API_KEY = 'tu_api_key_aqui';
const BASE_URL = 'https://tu-sitio.com/wp-json/webtowp/v1';

// Obtener información del sitio
async function getSiteInfo() {
  try {
    const response = await fetch(`${BASE_URL}/site-info`, {
      headers: {
        'X-WebToWP-Key': API_KEY
      }
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Site Info:', data);
    return data;
  } catch (error) {
    console.error('Error fetching site info:', error);
  }
}

// Obtener ajustes globales
async function getGlobalSettings() {
  try {
    const response = await fetch(`${BASE_URL}/settings`, {
      headers: {
        'X-WebToWP-Key': API_KEY
      }
    });
    
    const data = await response.json();
    console.log('Global Settings:', data);
    return data;
  } catch (error) {
    console.error('Error fetching settings:', error);
  }
}

// Uso
getSiteInfo();
getGlobalSettings();
```

### React Hook

```javascript
import { useState, useEffect } from 'react';

const API_KEY = process.env.REACT_APP_WEBTOWP_API_KEY;
const BASE_URL = process.env.REACT_APP_WEBTOWP_API_URL;

export function useWebToWP(endpoint) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchData() {
      try {
        const response = await fetch(`${BASE_URL}${endpoint}`, {
          headers: {
            'X-WebToWP-Key': API_KEY
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        setData(result.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [endpoint]);

  return { data, loading, error };
}

// Uso en componente
function App() {
  const { data: siteInfo, loading, error } = useWebToWP('/site-info');

  if (loading) return <div>Cargando...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      <h1>{siteInfo.name}</h1>
      <p>{siteInfo.description}</p>
    </div>
  );
}
```

### Next.js (Server Side)

```javascript
// pages/index.js
export async function getServerSideProps() {
  const API_KEY = process.env.WEBTOWP_API_KEY;
  const BASE_URL = process.env.WEBTOWP_API_URL;

  try {
    const [siteInfoRes, settingsRes] = await Promise.all([
      fetch(`${BASE_URL}/site-info`, {
        headers: { 'X-WebToWP-Key': API_KEY }
      }),
      fetch(`${BASE_URL}/settings`, {
        headers: { 'X-WebToWP-Key': API_KEY }
      })
    ]);

    const siteInfo = await siteInfoRes.json();
    const settings = await settingsRes.json();

    return {
      props: {
        siteInfo: siteInfo.data,
        settings: settings.data
      }
    };
  } catch (error) {
    console.error('Error fetching data:', error);
    return {
      props: {
        siteInfo: null,
        settings: null
      }
    };
  }
}

export default function Home({ siteInfo, settings }) {
  return (
    <div>
      <h1>{siteInfo?.name}</h1>
      <p style={{ color: settings?.colors.primary }}>
        {siteInfo?.description}
      </p>
    </div>
  );
}
```

### cURL

```bash
# Obtener información del sitio
curl -X GET "https://tu-sitio.com/wp-json/webtowp/v1/site-info" \
  -H "X-WebToWP-Key: tu_api_key_aqui"

# Obtener ajustes globales
curl -X GET "https://tu-sitio.com/wp-json/webtowp/v1/settings" \
  -H "X-WebToWP-Key: tu_api_key_aqui"

# Obtener información de debug
curl -X GET "https://tu-sitio.com/wp-json/webtowp/v1/debug" \
  -H "X-WebToWP-Key: tu_api_key_aqui"
```

### Python

```python
import requests

API_KEY = 'tu_api_key_aqui'
BASE_URL = 'https://tu-sitio.com/wp-json/webtowp/v1'

headers = {
    'X-WebToWP-Key': API_KEY
}

# Obtener información del sitio
response = requests.get(f'{BASE_URL}/site-info', headers=headers)
site_info = response.json()
print(site_info)

# Obtener ajustes globales
response = requests.get(f'{BASE_URL}/settings', headers=headers)
settings = response.json()
print(settings)
```

---

## ⚠️ Códigos de Error

### 400 Bad Request
```json
{
  "code": "rest_missing_callback_param",
  "message": "Parámetro requerido faltante",
  "data": {
    "status": 400
  }
}
```

### 403 Forbidden
```json
{
  "code": "invalid_api_key",
  "message": "Clave de API inválida.",
  "data": {
    "status": 403
  }
}
```

**Causas comunes:**
- API Key no proporcionada en el header
- API Key incorrecta o expirada
- API Key no configurada en WordPress

### 404 Not Found
```json
{
  "code": "rest_no_route",
  "message": "No se encontró ninguna ruta que coincida con la URL",
  "data": {
    "status": 404
  }
}
```

### 500 Internal Server Error
```json
{
  "code": "internal_server_error",
  "message": "Error interno del servidor",
  "data": {
    "status": 500
  }
}
```

---

## 🚦 Rate Limiting

**Límite:** No hay límite de rate por defecto, pero se recomienda implementar caché en el cliente.

**Caché del Servidor:** Las respuestas están cacheadas por 1 hora en el servidor de WordPress.

**Recomendaciones:**
- Implementa caché en tu aplicación frontend
- Usa SWR o React Query para gestión de estado
- Evita hacer peticiones innecesarias en cada render
- Considera usar ISR (Incremental Static Regeneration) en Next.js

---

## 🔄 Caché y Revalidación

### Limpiar Caché

El caché se limpia automáticamente cuando:
- Se actualiza un post o página
- Se modifican los ajustes globales
- Se ejecuta un deployment exitoso

También puedes limpiar el caché manualmente desde:
- Admin bar: Botón "Limpiar Caché"
- Admin: WebToWP > Ajustes Globales

### Headers de Caché

Las respuestas incluyen headers de caché estándar:

```http
Cache-Control: public, max-age=3600
X-Cache-Status: HIT|MISS
```

---

## 🛠️ Troubleshooting

### Error: "Clave de API inválida"

1. Verifica que estás enviando el header `X-WebToWP-Key`
2. Confirma que la API Key es correcta
3. Genera una nueva API Key si es necesario

### Error: CORS

Si estás haciendo peticiones desde un dominio diferente:

1. Ve a **WebToWP > Despliegue & API**
2. Configura "Orígenes Permitidos (CORS)"
3. Añade tu dominio frontend (ej: `https://frontend.mi-sitio.com`)

### Respuestas Vacías

Si recibes respuestas con datos null:

1. Verifica que has configurado los ajustes en WordPress
2. Comprueba que los módulos necesarios están activos
3. Revisa los logs de deployment para errores

---

## 📚 Recursos Adicionales

- [Repositorio GitHub](https://github.com/Vannit0/webtowp-engine)
- [Documentación de Actualizaciones](UPDATES.md)
- [Guía de Seguridad](SECURITY.md)
- [README Principal](README.md)

---

## 📞 Soporte

¿Necesitas ayuda? Contacta con nosotros:

- **Email:** info@webtowp.com
- **GitHub Issues:** [Crear Issue](https://github.com/Vannit0/webtowp-engine/issues)

---

**Última actualización:** Enero 2024  
**Versión del Plugin:** 1.2.0
