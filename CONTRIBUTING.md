# Guía de Contribución

¡Gracias por tu interés en contribuir a WebToWP Engine! Esta guía te ayudará a empezar.

---

## 📋 Tabla de Contenidos

1. [Código de Conducta](#código-de-conducta)
2. [¿Cómo Puedo Contribuir?](#cómo-puedo-contribuir)
3. [Configuración del Entorno](#configuración-del-entorno)
4. [Proceso de Desarrollo](#proceso-de-desarrollo)
5. [Estándares de Código](#estándares-de-código)
6. [Proceso de Pull Request](#proceso-de-pull-request)
7. [Reportar Bugs](#reportar-bugs)
8. [Sugerir Mejoras](#sugerir-mejoras)

---

## 📜 Código de Conducta

Este proyecto y todos los participantes están regidos por nuestro Código de Conducta. Al participar, se espera que mantengas este código. Por favor, reporta comportamientos inaceptables a info@webtowp.com.

### Nuestros Estándares

**Ejemplos de comportamiento que contribuyen a crear un ambiente positivo:**
- Usar lenguaje acogedor e inclusivo
- Ser respetuoso de diferentes puntos de vista y experiencias
- Aceptar críticas constructivas con gracia
- Enfocarse en lo que es mejor para la comunidad
- Mostrar empatía hacia otros miembros de la comunidad

**Ejemplos de comportamiento inaceptable:**
- Uso de lenguaje o imágenes sexualizadas
- Trolling, comentarios insultantes/despectivos, y ataques personales o políticos
- Acoso público o privado
- Publicar información privada de otros sin permiso explícito
- Otra conducta que razonablemente podría considerarse inapropiada

---

## 🤝 ¿Cómo Puedo Contribuir?

### Reportar Bugs

Los bugs se rastrean como [GitHub Issues](https://github.com/Vannit0/webtowp-engine/issues). Antes de crear un issue:

1. **Verifica** que el bug no haya sido reportado ya
2. **Determina** qué repositorio debería recibir el problema
3. **Recopila** información sobre el bug

**Cuando crees un bug report, incluye:**
- Título claro y descriptivo
- Pasos exactos para reproducir el problema
- Comportamiento esperado vs. comportamiento actual
- Screenshots si es aplicable
- Versión de WordPress, PHP y del plugin
- Cualquier información adicional relevante

### Sugerir Mejoras

Las sugerencias de mejoras también se rastrean como GitHub Issues. Cuando crees una sugerencia:

- Usa un título claro y descriptivo
- Proporciona una descripción detallada de la mejora sugerida
- Explica por qué esta mejora sería útil
- Lista algunos ejemplos de cómo funcionaría

### Tu Primera Contribución de Código

¿No estás seguro por dónde empezar? Busca issues etiquetados con:

- `good first issue` - Issues que deberían requerir solo unas pocas líneas de código
- `help wanted` - Issues que pueden ser más complejos

---

## 🛠️ Configuración del Entorno

### Requisitos

- WordPress 5.8+
- PHP 7.4+
- Composer
- Node.js y npm (para assets)
- Git

### Instalación

1. **Fork el repositorio**
```bash
# Haz fork en GitHub, luego clona tu fork
git clone https://github.com/TU-USUARIO/webtowp-engine.git
cd webtowp-engine
```

2. **Configura el upstream**
```bash
git remote add upstream https://github.com/Vannit0/webtowp-engine.git
```

3. **Instala dependencias**
```bash
# Si hay dependencias de Composer
composer install

# Si hay dependencias de npm
npm install
```

4. **Configura WordPress local**
- Usa Local by Flywheel, XAMPP, o Docker
- Copia el plugin a `wp-content/plugins/`
- Instala ACF PRO
- Activa el plugin

---

## 💻 Proceso de Desarrollo

### Workflow de Git

1. **Crea una rama para tu feature/fix**
```bash
git checkout -b feature/nombre-descriptivo
# o
git checkout -b fix/nombre-del-bug
```

2. **Haz tus cambios**
- Escribe código limpio y bien documentado
- Sigue los estándares de código
- Añade tests si es aplicable

3. **Commit tus cambios**
```bash
git add .
git commit -m "feat: descripción clara del cambio"
```

**Formato de commits:**
- `feat:` Nueva funcionalidad
- `fix:` Corrección de bug
- `docs:` Cambios en documentación
- `style:` Cambios de formato (sin cambios de código)
- `refactor:` Refactorización de código
- `test:` Añadir o modificar tests
- `chore:` Tareas de mantenimiento

4. **Mantén tu rama actualizada**
```bash
git fetch upstream
git rebase upstream/main
```

5. **Push a tu fork**
```bash
git push origin feature/nombre-descriptivo
```

### Estructura de Archivos

```
webtowp-engine/
├── includes/              # Clases PHP principales
│   ├── class-*.php       # Una clase por archivo
│   └── ...
├── assets/
│   ├── css/              # Estilos
│   ├── js/               # Scripts
│   └── images/           # Imágenes
├── languages/            # Archivos de traducción
├── examples/             # Ejemplos de código
├── tests/                # Tests (si existen)
└── ...
```

### Convenciones de Nombres

**PHP:**
- Clases: `W2WP_Feature_Name` → `class-feature-name.php`
- Métodos: `snake_case`
- Variables: `$snake_case`
- Constantes: `W2WP_CONSTANT_NAME`

**JavaScript:**
- Funciones: `camelCase`
- Clases: `PascalCase`
- Constantes: `UPPER_SNAKE_CASE`

**CSS:**
- Clases: `webtowp-component-name`
- IDs: `webtowp-unique-id`

---

## 📝 Estándares de Código

### PHP

Seguimos los [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

**Puntos clave:**
- Indentación con tabs
- Espacios alrededor de operadores
- Llaves en nueva línea para funciones y clases
- Comentarios PHPDoc para todas las funciones públicas
- Sanitización de todos los inputs
- Escape de todos los outputs
- Nonces para formularios
- Verificación de capacidades

**Ejemplo:**
```php
<?php
/**
 * Descripción de la función
 *
 * @param string $param Descripción del parámetro
 * @return bool Descripción del retorno
 */
public function example_function( $param ) {
    // Verificar nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'action_name' ) ) {
        return false;
    }
    
    // Verificar capacidades
    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    
    // Sanitizar input
    $param = sanitize_text_field( $param );
    
    // Tu código aquí
    
    return true;
}
```

### JavaScript

Seguimos los estándares de ES6+.

**Puntos clave:**
- Usar `const` y `let`, no `var`
- Arrow functions cuando sea apropiado
- Template literals para strings
- Comentarios JSDoc
- Manejo de errores con try/catch

**Ejemplo:**
```javascript
/**
 * Descripción de la función
 * @param {string} param - Descripción del parámetro
 * @returns {Promise} Descripción del retorno
 */
async function exampleFunction(param) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ param })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}
```

### CSS

Seguimos metodología BEM simplificada.

**Puntos clave:**
- Prefijo `webtowp-` para todas las clases
- Mobile-first approach
- Variables CSS para valores reutilizables
- Comentarios para secciones

**Ejemplo:**
```css
/* ============================================
   Component Name
   ============================================ */
.webtowp-component {
    display: flex;
    gap: var(--webtowp-spacing);
}

.webtowp-component__element {
    padding: 1rem;
}

.webtowp-component--modifier {
    background: var(--webtowp-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .webtowp-component {
        flex-direction: column;
    }
}
```

---

## 🔍 Testing

### Tests Manuales

Antes de enviar un PR, verifica:

1. **Funcionalidad:**
   - ✅ El código hace lo que se supone que debe hacer
   - ✅ No rompe funcionalidad existente
   - ✅ Funciona en diferentes navegadores

2. **Seguridad:**
   - ✅ Todos los inputs están sanitizados
   - ✅ Todos los outputs están escapados
   - ✅ Nonces verificados en formularios
   - ✅ Capacidades verificadas

3. **Rendimiento:**
   - ✅ No añade queries innecesarias
   - ✅ Assets se cargan solo cuando es necesario
   - ✅ Código optimizado

4. **Compatibilidad:**
   - ✅ WordPress 5.8+
   - ✅ PHP 7.4+
   - ✅ ACF PRO

### Tests Automatizados (Futuro)

Estamos trabajando en añadir:
- PHPUnit para tests unitarios
- Playwright para tests E2E

---

## 📤 Proceso de Pull Request

### Antes de Enviar

1. **Actualiza tu rama**
```bash
git fetch upstream
git rebase upstream/main
```

2. **Verifica tu código**
- Ejecuta linters si están disponibles
- Prueba manualmente todos los cambios
- Revisa que no haya console.logs o var_dumps

3. **Actualiza documentación**
- README.md si es necesario
- Comentarios inline
- PHPDoc/JSDoc

### Crear el Pull Request

1. **Push a tu fork**
```bash
git push origin feature/nombre-descriptivo
```

2. **Crea el PR en GitHub**
- Usa un título descriptivo
- Completa la plantilla de PR
- Enlaza issues relacionados
- Añade screenshots si es aplicable

### Plantilla de PR

```markdown
## Descripción
Breve descripción de los cambios

## Tipo de cambio
- [ ] Bug fix
- [ ] Nueva funcionalidad
- [ ] Breaking change
- [ ] Documentación

## ¿Cómo se ha probado?
Describe las pruebas que realizaste

## Checklist
- [ ] Mi código sigue los estándares del proyecto
- [ ] He realizado una auto-revisión de mi código
- [ ] He comentado mi código, especialmente en áreas difíciles
- [ ] He actualizado la documentación
- [ ] Mis cambios no generan nuevas advertencias
- [ ] He probado que mi fix es efectivo o que mi feature funciona
- [ ] Los tests existentes pasan localmente
```

### Revisión

- Responde a comentarios de manera constructiva
- Haz los cambios solicitados
- Mantén la conversación profesional y amigable

---

## 🐛 Reportar Bugs

### Plantilla de Bug Report

```markdown
**Describe el bug**
Una descripción clara y concisa del bug.

**Pasos para reproducir**
1. Ve a '...'
2. Haz clic en '...'
3. Scroll hasta '...'
4. Ver error

**Comportamiento esperado**
Descripción de lo que esperabas que sucediera.

**Screenshots**
Si es aplicable, añade screenshots.

**Entorno:**
- WordPress: [versión]
- PHP: [versión]
- Plugin: [versión]
- Navegador: [nombre y versión]

**Información adicional**
Cualquier otro contexto sobre el problema.
```

---

## 💡 Sugerir Mejoras

### Plantilla de Feature Request

```markdown
**¿Tu feature request está relacionado con un problema?**
Descripción clara del problema.

**Describe la solución que te gustaría**
Descripción clara de lo que quieres que suceda.

**Describe alternativas que hayas considerado**
Descripción de soluciones o features alternativas.

**Contexto adicional**
Cualquier otro contexto o screenshots sobre el feature request.
```

---

## 📞 Contacto

¿Tienes preguntas? Contáctanos:

- **GitHub Issues:** [Crear Issue](https://github.com/Vannit0/webtowp-engine/issues)
- **Email:** info@webtowp.com
- **Discussions:** [GitHub Discussions](https://github.com/Vannit0/webtowp-engine/discussions)

---

## 🙏 Agradecimientos

Gracias por contribuir a WebToWP Engine. Tu tiempo y esfuerzo son muy apreciados.

---

## 📄 Licencia

Al contribuir a este proyecto, aceptas que tus contribuciones serán licenciadas bajo la licencia GPL v2 o posterior.
