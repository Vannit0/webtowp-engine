# Sistema de Actualizaciones Automáticas

Este plugin utiliza **Plugin Update Checker** para recibir actualizaciones automáticas desde GitHub.

## 📋 Requisitos Previos

### 1. Instalar Plugin Update Checker

Descarga la librería desde: https://github.com/YahnisElsts/plugin-update-checker/releases

**Instalación rápida:**
```bash
cd includes/plugin-update-checker
wget https://github.com/YahnisElsts/plugin-update-checker/archive/refs/tags/v5.3.tar.gz
tar -xzf v5.3.tar.gz
mv plugin-update-checker-5.3/* .
rm -rf plugin-update-checker-5.3 v5.3.tar.gz
```

O descarga manualmente y coloca los archivos en:
```
includes/plugin-update-checker/
├── Puc/
└── plugin-update-checker.php
```

## � Configuración para Repositorio Privado

**IMPORTANTE**: Este repositorio es privado, por lo que necesitas configurar un token de GitHub.

### Configurar el Token

1. Abre tu archivo `wp-config.php` de WordPress
2. Añade esta línea (reemplaza con tu token real):
   ```php
   define( 'W2WP_GITHUB_TOKEN', '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' );
   ```
3. Guarda el archivo

**Ver archivo de ejemplo**: `wp-config-sample.php` incluido en el plugin.

### ⚠️ Seguridad del Token
- **NUNCA** subas tu `wp-config.php` a GitHub
- El token da acceso a tus repositorios privados
- Mantenlo seguro y privado

## �🚀 Cómo Funciona

El plugin está configurado para:
- **Repositorio**: https://github.com/Vannit0/webtowp-engine (PRIVADO)
- **Rama**: `main`
- **Versión actual**: Se lee del header del plugin (Version: 1.0.0)
- **Autenticación**: Mediante token de GitHub (W2WP_GITHUB_TOKEN)

### Proceso de Actualización

1. WordPress revisa periódicamente el repositorio de GitHub
2. Compara la versión en el header del plugin con las releases de GitHub
3. Si hay una versión más nueva, muestra notificación en el dashboard
4. El usuario puede actualizar con un clic desde WordPress

## 📦 Crear una Nueva Release en GitHub

Para que las actualizaciones funcionen correctamente:

### Paso 1: Actualizar la Versión
Edita `webtowp-engine.php` y cambia la versión:
```php
/**
 * Version: 1.1.0
 */
```

Y también en la constante:
```php
define( 'W2WP_VERSION', '1.1.0' );
```

### Paso 2: Commit y Push
```bash
git add .
git commit -m "Release v1.1.0"
git push origin main
```

### Paso 3: Crear Release en GitHub

**Opción A: Desde la interfaz web**
1. Ve a: https://github.com/Vannit0/webtowp-engine/releases
2. Click en "Create a new release"
3. Tag version: `v1.1.0` (debe coincidir con la versión del plugin)
4. Release title: `Version 1.1.0`
5. Descripción: Changelog de cambios
6. Click "Publish release"

**Opción B: Desde línea de comandos**
```bash
git tag v1.1.0
git push origin v1.1.0
```

Luego crea la release desde GitHub web interface.

### Paso 4: Verificar
- La release debe tener el tag en formato: `v1.0.0`, `v1.1.0`, etc.
- El tag debe coincidir con la versión en el header del plugin
- WordPress detectará automáticamente la nueva versión

## 🔍 Verificación Manual

Para probar que funciona:

1. Ve a WordPress Admin → Plugins
2. Busca "WebToWP Engine"
3. Si hay una actualización disponible, verás un mensaje
4. Click en "Update Now"

## 🛠️ Troubleshooting

### No aparecen actualizaciones

**Verifica que:**
- La librería Plugin Update Checker esté instalada correctamente
- El archivo `includes/plugin-update-checker/plugin-update-checker.php` existe
- El repositorio de GitHub es público o tienes configurado un token de acceso
- La release en GitHub tiene un tag válido (v1.0.0, v1.1.0, etc.)
- La versión en GitHub es mayor que la versión instalada

### Forzar verificación de actualizaciones

Desde WordPress:
1. Ve a Dashboard → Updates
2. Click en "Check Again"

O borra el transient:
```php
delete_site_transient('update_plugins');
```

## 📝 Formato de Versiones

Usa **Semantic Versioning**:
- `1.0.0` - Release inicial
- `1.0.1` - Bug fixes
- `1.1.0` - Nuevas características (minor)
- `2.0.0` - Cambios importantes (major)

## � Generar Token de GitHub (Para Repositorio Privado)

Si aún no tienes un token o necesitas generar uno nuevo:

1. Ve a: https://github.com/settings/tokens
2. Click en **"Generate new token"** → **"Generate new token (classic)"**
3. Configuración del token:
   - **Note**: `WebToWP Plugin Updates`
   - **Expiration**: Selecciona la duración deseada
   - **Scopes**: Marca ✅ **`repo`** (Full control of private repositories)
4. Click **"Generate token"**
5. **Copia el token inmediatamente** (empieza con `ghp_`)
6. Pégalo en tu `wp-config.php` como se indicó arriba

**Nota**: El token solo se muestra una vez. Si lo pierdes, deberás generar uno nuevo.

## 📚 Documentación Adicional

- Plugin Update Checker: https://github.com/YahnisElsts/plugin-update-checker
- GitHub Releases: https://docs.github.com/en/repositories/releasing-projects-on-github
