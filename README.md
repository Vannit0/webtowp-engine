# WebToWP Engine 🚀

[![Version](https://img.shields.io/badge/version-1.3.0-blue.svg)](https://github.com/Vannit0/webtowp-engine/releases)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)

Motor headless profesional para WordPress. Transforma tu WordPress en un CMS headless potente con API REST completa, gestión de módulos, sistema de caché, deployment automático y una experiencia de administración moderna.

---

## ✨ Características Principales

### 🎯 Core
- ✅ **API REST Completa** - Endpoints optimizados con caché automático
- ✅ **Módulos Intercambiables** - Informativo Pro y Landing Page
- ✅ **ACF Integrado** - Campos personalizados preconfigurados
- ✅ **Sistema de Caché** - Transients con gestión inteligente
- ✅ **Deployment Automático** - Integración con Cloudflare Pages

### 🔒 Seguridad
- ✅ **Validación Completa** - 15+ métodos de validación
- ✅ **Sanitización** - Todos los inputs sanitizados
- ✅ **API Key** - Autenticación segura para API REST
- ✅ **Verificación de Dependencias** - Checks automáticos

### 📊 Gestión
- ✅ **Dashboard Inteligente** - Métricas y salud del sistema en tiempo real
- ✅ **Logs de Deployment** - Historial completo con estadísticas
- ✅ **Sistema de Backups** - Exportación/importación de configuración
- ✅ **Notificaciones** - Toast, persistentes y modales

### 🎨 UX/UI
- ✅ **Diseño Moderno** - UI completamente renovada con 600+ líneas de CSS
- ✅ **Onboarding Wizard** - Configuración guiada en 5 pasos
- ✅ **Responsive** - Adaptado a todos los dispositivos
- ✅ **Dark Mode** - Soporte para modo oscuro

### 🌍 Internacionalización
- ✅ **Multiidioma** - Sistema completo de traducciones
- ✅ **Español** - Traducción completa incluida
- ✅ **Extensible** - Fácil añadir nuevos idiomas

---

## 📦 Instalación

### Requisitos
- WordPress 5.8 o superior
- PHP 7.4 o superior
- Advanced Custom Fields (ACF) PRO

### Pasos

1. **Descarga el plugin:**
```bash
git clone https://github.com/Vannit0/webtowp-engine.git
```

2. **Sube a WordPress:**
   - Copia la carpeta a `/wp-content/plugins/`
   - O sube el ZIP desde el admin de WordPress

3. **Activa el plugin:**
   - Ve a Plugins → Plugins instalados
   - Activa "WebToWP Engine"

4. **Configuración inicial:**
   - Sigue el wizard de onboarding (5 pasos)
   - O configura manualmente desde WebToWP → Dashboard

---

## 🚀 Inicio Rápido

### 1. Activa un Módulo
```
WebToWP → Módulos Activos → Activa "Sitio Informativo Pro"
```

### 2. Configura Ajustes Globales
```
WebToWP → Ajustes Globales
- Sube tu logo
- Define colores de marca
- Configura redes sociales
```

### 3. Genera API Key
```
WebToWP → Despliegue & API → Generar Nueva Clave
```

### 4. Conecta tu Frontend
```javascript
const response = await fetch('https://tu-sitio.com/wp-json/webtowp/v1/settings', {
  headers: {
    'X-WebToWP-Key': 'tu_api_key'
  }
});
```

---

## 📚 Documentación

- **[API REST Documentation](API-DOCUMENTATION.md)** - Documentación completa de endpoints
- **[Ejemplos de Código](examples/api-examples.js)** - Ejemplos prácticos
- **[Changelog](CHANGELOG.md)** - Historial de cambios
- **[Release Notes](RELEASE_NOTES.md)** - Notas de la versión actual
- **[Seguridad](SECURITY.md)** - Guía de seguridad
- **[Actualizaciones](UPDATES.md)** - Sistema de actualizaciones

---

## 🎯 Módulos Disponibles

### Sitio Informativo Pro
Módulo completo para sitios corporativos e informativos.

**Incluye:**
- 📄 8 Páginas predefinidas (Inicio, Nosotros, Servicios, Blog, Recursos, Contacto, FAQ, Legales)
- 🔧 2 Custom Post Types (Servicios, Recursos)
- 🎨 Campos ACF configurados
- 📱 Secciones responsivas

### Landing Page
Módulo optimizado para páginas de conversión.

**Incluye:**
- 🎯 Secciones de conversión (Hero, Beneficios, Precios)
- 💬 Testimonios y CTA
- 📊 Optimizado para conversión
- 🎨 Campos ACF para landing

---

## 🔌 API REST

### Endpoints Principales

#### Site Info
```http
GET /wp-json/webtowp/v1/site-info
X-WebToWP-Key: tu_api_key
```

#### Global Settings
```http
GET /wp-json/webtowp/v1/settings
X-WebToWP-Key: tu_api_key
```

#### Debug Info
```http
GET /wp-json/webtowp/v1/debug
X-WebToWP-Key: tu_api_key
```

Ver [documentación completa](API-DOCUMENTATION.md) para más detalles.

---

## 💻 Ejemplos de Uso

### React
```javascript
import { useWebToWP } from './hooks/useWebToWP';

function App() {
  const { data, loading } = useWebToWP('/site-info');
  
  if (loading) return <div>Cargando...</div>;
  
  return <h1>{data.name}</h1>;
}
```

### Next.js
```javascript
export async function getServerSideProps() {
  const res = await fetch('https://tu-sitio.com/wp-json/webtowp/v1/settings', {
    headers: { 'X-WebToWP-Key': process.env.API_KEY }
  });
  
  return { props: { settings: await res.json() } };
}
```

Ver más ejemplos en [`examples/api-examples.js`](examples/api-examples.js)

---

## 🛠️ Desarrollo

### Estructura del Proyecto
```
webtowp-engine/
├── includes/           # Clases PHP principales
│   ├── class-dashboard.php
│   ├── class-cache-manager.php
│   ├── class-deployment-logger.php
│   ├── class-backup-manager.php
│   ├── class-notification-system.php
│   └── class-onboarding-wizard.php
├── assets/            # CSS y JavaScript
│   ├── css/          # Estilos modernos
│   └── js/           # Scripts del admin
├── languages/         # Archivos de traducción
├── examples/          # Ejemplos de código
└── modules/           # Módulos adicionales
```

### Clases Principales
- `W2WP_Dashboard` - Dashboard principal con widgets
- `W2WP_Cache_Manager` - Sistema de caché
- `W2WP_Deployment_Logger` - Logs de deployment
- `W2WP_Backup_Manager` - Sistema de backups
- `W2WP_Notification_System` - Notificaciones
- `W2WP_Onboarding_Wizard` - Wizard de configuración
- `W2WP_Validator` - Validación y sanitización
- `W2WP_i18n` - Internacionalización

---

## 🆕 Novedades v1.3.0

### FASE 1: Estabilización
- ✅ Sistema de verificación de dependencias
- ✅ Validación y sanitización completa
- ✅ Gestor de avisos mejorado
- ✅ Optimización de rewrite rules

### FASE 2: Funcionalidad Core
- ✅ Sistema de caché con transients
- ✅ Logs de deployment con UI
- ✅ Internacionalización completa
- ✅ Sistema de backups/exportación
- ✅ Documentación API REST

### FASE 3: UX/UI
- ✅ Diseño moderno (600+ líneas CSS)
- ✅ Dashboard inteligente
- ✅ Sistema de notificaciones
- ✅ Onboarding wizard

Ver [CHANGELOG.md](CHANGELOG.md) para detalles completos.

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas!

1. Fork el proyecto
2. Crea tu rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Roadmap

### v1.4.0 (Próxima)
- [ ] Gestión avanzada de API keys
- [ ] Rate limiting
- [ ] Encriptación de datos sensibles
- [ ] Tests unitarios
- [ ] Tests E2E

### v1.5.0 (Futuro)
- [ ] Más módulos (E-commerce, Membresías)
- [ ] GraphQL support
- [ ] Webhooks personalizados
- [ ] Analytics integrado

---

## 📄 Licencia

GPL v2 o posterior. Ver [LICENSE](LICENSE) para más detalles.

---

## 👥 Autores

- **Vannit0** - *Desarrollo inicial* - [GitHub](https://github.com/Vannit0)

---

## 🙏 Agradecimientos

- Equipo de WordPress
- Comunidad de ACF
- Todos los contribuidores

---

## 📞 Soporte

- **Issues:** [GitHub Issues](https://github.com/Vannit0/webtowp-engine/issues)
- **Email:** info@webtowp.com
- **Documentación:** [Docs](API-DOCUMENTATION.md)

---

**⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!**
