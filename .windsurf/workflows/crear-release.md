---
description: Cómo crear un Release en GitHub para actualización automática del plugin
---

# Crear Release en GitHub - WebToWP Engine

## Proceso Automatizado para Futuras Actualizaciones

### 1. Actualizar Versión del Plugin

Edita `webtowp-engine.php` y cambia:
- `Version: X.X.X` en el header del plugin
- `define( 'W2WP_VERSION', 'X.X.X' );` en la línea 21

### 2. Crear Commit y Tag

```bash
git add webtowp-engine.php
git commit -m "Bump version to X.X.X - Descripción del release"
git tag -a vX.X.X -m "Release vX.X.X - Descripción detallada"
```

### 3. Subir a GitHub

```bash
git push origin main
git push origin vX.X.X
```

### 4. Crear Release en GitHub (Manual)

1. Ve a: https://github.com/Vannit0/webtowp-engine/releases/new
2. Selecciona el tag: `vX.X.X`
3. Título del Release: `v1.1.0 - Fase 1: Marca Blanca y Sistema de Roles`
4. Descripción del Release:

```markdown
## 🎯 Novedades de esta versión

### ✨ Marca Blanca
- Nuevo menú principal "WebToWP" en el panel de administración
- 3 sub-páginas: Módulos Activos, Ajustes Globales, Despliegue & API
- Icono moderno y posición optimizada en el menú

### 🔒 Sistema de Roles (Security Vault)
- Restricción automática de menús sensibles para usuarios no-admin
- Ocultación de: Plugins, Temas, Ajustes, Herramientas, Comentarios, ACF
- Limpieza visual: Oculta opciones de pantalla y ayuda para clientes

### 📦 Archivos Modificados
- `includes/class-admin-setup.php` (nuevo)
- `includes/class-webtowp-engine.php` (actualizado)

### 🔧 Requisitos
- WordPress 5.8+
- PHP 7.4+
- ACF Pro (recomendado)
```

5. Marca como "Latest release"
6. Clic en "Publish release"

## 📋 Para el Release v1.1.0 Actual

**URL para crear el release:**
https://github.com/Vannit0/webtowp-engine/releases/new?tag=v1.1.0

**Título sugerido:**
```
v1.1.0 - Fase 1: Marca Blanca y Sistema de Roles
```

**Descripción sugerida:**
```markdown
## 🎯 Novedades de esta versión

### ✨ Marca Blanca
- Nuevo menú principal "WebToWP" en el panel de administración
- 3 sub-páginas: Módulos Activos, Ajustes Globales, Despliegue & API
- Icono moderno (dashicons-admin-site-alt3) y posición optimizada

### 🔒 Sistema de Roles (Security Vault)
- Restricción automática de menús sensibles para usuarios sin privilegios de administrador
- Menús ocultos para clientes/editores: Plugins, Temas, Ajustes, Herramientas, Comentarios, ACF
- Limpieza visual: Oculta panel de "Opciones de pantalla" y pestaña "Ayuda"

### 📦 Cambios Técnicos
- **Nuevo archivo:** `includes/class-admin-setup.php` - Clase para gestión de marca blanca y roles
- **Actualizado:** `includes/class-webtowp-engine.php` - Integración del nuevo sistema
- **Patrón Singleton:** Implementación optimizada para la clase de administración
- **Hooks utilizados:** `acf/init`, `admin_menu` (prioridad 999), `admin_head`

### 🔧 Requisitos
- WordPress 5.8 o superior
- PHP 7.4 o superior
- ACF Pro (recomendado para aprovechar las opciones de página)

### 📝 Notas de Actualización
Esta actualización es compatible con versiones anteriores. No requiere cambios en la configuración existente.
```

## ⚡ Actualización Automática

Una vez publicado el release:
- WordPress detectará la actualización automáticamente (cada 12 horas)
- Los administradores verán la notificación en el panel
- Podrán actualizar con un solo clic desde Plugins > Actualizaciones
