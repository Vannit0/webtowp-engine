# Seguridad - WebToWP Engine

## 🔐 Configuración del Token de GitHub

### ⚠️ IMPORTANTE: NUNCA pongas el token en el código del plugin

El token de GitHub **NUNCA** debe estar en archivos del plugin que se suben a GitHub.

### ✅ Configuración Correcta

**1. Abre tu archivo `wp-config.php`** (ubicado en la raíz de WordPress, NO en el plugin)

**2. Añade esta línea:**
```php
define( 'W2WP_GITHUB_TOKEN', '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' );
```

**3. Guarda el archivo**

### 📍 Ubicación Correcta

```
wordpress/
├── wp-config.php          ← AQUÍ va el token
├── wp-content/
│   └── plugins/
│       └── webtowp-engine/
│           └── webtowp-engine.php  ← NUNCA aquí
```

## 🛡️ Buenas Prácticas de Seguridad

### ❌ NO HACER:
- ❌ Poner el token en archivos del plugin
- ❌ Subir `wp-config.php` a GitHub
- ❌ Compartir el token públicamente
- ❌ Hardcodear credenciales en el código

### ✅ SÍ HACER:
- ✅ Token en `wp-config.php` únicamente
- ✅ Usar `.gitignore` para proteger archivos sensibles
- ✅ Regenerar tokens si se comprometen
- ✅ Usar tokens con permisos mínimos necesarios

## 🔄 Si el Token se Comprometió

Si accidentalmente subiste el token a GitHub:

1. **Revoca el token inmediatamente:**
   - Ve a: https://github.com/settings/tokens
   - Encuentra el token comprometido
   - Click en "Delete"

2. **Genera un nuevo token:**
   - Sigue las instrucciones en `UPDATES.md`
   - Actualiza `wp-config.php` con el nuevo token

3. **Limpia el historial de Git (si es necesario):**
   ```bash
   git filter-branch --force --index-filter \
   "git rm --cached --ignore-unmatch webtowp-engine.php" \
   --prune-empty --tag-name-filter cat -- --all
   ```

## 📝 Verificación de Seguridad

Antes de hacer commit, verifica:

- [ ] El token NO está en ningún archivo del plugin
- [ ] `wp-config.php` está en `.gitignore`
- [ ] No hay credenciales hardcodeadas
- [ ] `.gitignore` está configurado correctamente

## 🔍 Archivos Protegidos por .gitignore

El archivo `.gitignore` protege:
- `wp-config.php`
- Archivos `.env`
- Archivos con `*token*` en el nombre
- Archivos con `*secret*` en el nombre
- Archivos con `*key.php` en el nombre

## 📚 Recursos Adicionales

- [GitHub Token Security](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token)
- [WordPress Security Best Practices](https://wordpress.org/support/article/hardening-wordpress/)
- [Plugin Update Checker Documentation](https://github.com/YahnisElsts/plugin-update-checker)

## 🆘 Soporte

Si tienes dudas sobre la seguridad del plugin, consulta la documentación o contacta al equipo de desarrollo.
