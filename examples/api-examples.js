/**
 * WebToWP Engine - API Examples
 * 
 * Ejemplos prácticos de uso de la API REST
 */

// ============================================
// Configuración
// ============================================

const CONFIG = {
  apiKey: 'tu_api_key_aqui',
  baseUrl: 'https://tu-sitio.com/wp-json/webtowp/v1'
};

// ============================================
// Clase Helper para API
// ============================================

class WebToWPAPI {
  constructor(apiKey, baseUrl) {
    this.apiKey = apiKey;
    this.baseUrl = baseUrl;
  }

  async request(endpoint) {
    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        headers: {
          'X-WebToWP-Key': this.apiKey
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      console.error(`Error fetching ${endpoint}:`, error);
      throw error;
    }
  }

  async getSiteInfo() {
    return this.request('/site-info');
  }

  async getSettings() {
    return this.request('/settings');
  }

  async getDebugInfo() {
    return this.request('/debug');
  }
}

// ============================================
// Ejemplo 1: Uso Básico
// ============================================

async function example1_BasicUsage() {
  console.log('=== Ejemplo 1: Uso Básico ===');
  
  const api = new WebToWPAPI(CONFIG.apiKey, CONFIG.baseUrl);
  
  try {
    const siteInfo = await api.getSiteInfo();
    console.log('Nombre del sitio:', siteInfo.data.name);
    console.log('URL:', siteInfo.data.url);
    console.log('Versión WP:', siteInfo.data.version);
  } catch (error) {
    console.error('Error:', error.message);
  }
}

// ============================================
// Ejemplo 2: Obtener Colores del Tema
// ============================================

async function example2_GetThemeColors() {
  console.log('=== Ejemplo 2: Colores del Tema ===');
  
  const api = new WebToWPAPI(CONFIG.apiKey, CONFIG.baseUrl);
  
  try {
    const settings = await api.getSettings();
    const colors = settings.data.colors;
    
    console.log('Color Primario:', colors.primary);
    console.log('Color Secundario:', colors.secondary);
    
    // Aplicar colores al CSS
    document.documentElement.style.setProperty('--primary-color', colors.primary);
    document.documentElement.style.setProperty('--secondary-color', colors.secondary);
  } catch (error) {
    console.error('Error:', error.message);
  }
}

// ============================================
// Ejemplo 3: Obtener Redes Sociales
// ============================================

async function example3_GetSocialNetworks() {
  console.log('=== Ejemplo 3: Redes Sociales ===');
  
  const api = new WebToWPAPI(CONFIG.apiKey, CONFIG.baseUrl);
  
  try {
    const settings = await api.getSettings();
    const social = settings.data.social_networks;
    
    // Crear enlaces de redes sociales
    const socialLinks = Object.entries(social)
      .filter(([key, url]) => url !== null)
      .map(([key, url]) => ({
        network: key,
        url: url,
        icon: getSocialIcon(key)
      }));
    
    console.log('Redes sociales configuradas:', socialLinks);
    return socialLinks;
  } catch (error) {
    console.error('Error:', error.message);
  }
}

function getSocialIcon(network) {
  const icons = {
    instagram: '📷',
    facebook: '👥',
    twitter: '🐦',
    linkedin: '💼',
    youtube: '📺'
  };
  return icons[network] || '🔗';
}

// ============================================
// Ejemplo 4: Renderizar Footer con Datos
// ============================================

async function example4_RenderFooter() {
  console.log('=== Ejemplo 4: Renderizar Footer ===');
  
  const api = new WebToWPAPI(CONFIG.apiKey, CONFIG.baseUrl);
  
  try {
    const settings = await api.getSettings();
    const { brand_identity, communication, branding } = settings.data;
    
    const footerHTML = `
      <footer>
        <div class="footer-content">
          <div class="footer-brand">
            ${brand_identity.logo_contraste ? 
              `<img src="${brand_identity.logo_contraste}" alt="${brand_identity.brand_name}">` : 
              `<h3>${brand_identity.brand_name}</h3>`
            }
            <p>${brand_identity.copyright_text}</p>
          </div>
          
          <div class="footer-contact">
            <h4>Contacto</h4>
            ${communication.support_email ? `<p>📧 ${communication.support_email}</p>` : ''}
            ${communication.whatsapp ? `<p>📱 ${communication.whatsapp}</p>` : ''}
            ${communication.physical_address ? `<p>📍 ${communication.physical_address}</p>` : ''}
          </div>
          
          <div class="footer-signature">
            ${branding.html}
          </div>
        </div>
      </footer>
    `;
    
    console.log('Footer HTML generado');
    return footerHTML;
  } catch (error) {
    console.error('Error:', error.message);
  }
}

// ============================================
// Ejemplo 5: Caché Local con LocalStorage
// ============================================

class CachedWebToWPAPI extends WebToWPAPI {
  constructor(apiKey, baseUrl, cacheTime = 3600000) { // 1 hora por defecto
    super(apiKey, baseUrl);
    this.cacheTime = cacheTime;
  }

  getCacheKey(endpoint) {
    return `webtowp_cache_${endpoint.replace(/\//g, '_')}`;
  }

  getFromCache(endpoint) {
    const cacheKey = this.getCacheKey(endpoint);
    const cached = localStorage.getItem(cacheKey);
    
    if (!cached) return null;
    
    const { data, timestamp } = JSON.parse(cached);
    const now = Date.now();
    
    if (now - timestamp > this.cacheTime) {
      localStorage.removeItem(cacheKey);
      return null;
    }
    
    return data;
  }

  saveToCache(endpoint, data) {
    const cacheKey = this.getCacheKey(endpoint);
    const cacheData = {
      data,
      timestamp: Date.now()
    };
    localStorage.setItem(cacheKey, JSON.stringify(cacheData));
  }

  async request(endpoint) {
    // Intentar obtener del caché
    const cached = this.getFromCache(endpoint);
    if (cached) {
      console.log(`Cache HIT: ${endpoint}`);
      return cached;
    }
    
    console.log(`Cache MISS: ${endpoint}`);
    
    // Si no hay caché, hacer petición
    const data = await super.request(endpoint);
    
    // Guardar en caché
    this.saveToCache(endpoint, data);
    
    return data;
  }
}

async function example5_CachedAPI() {
  console.log('=== Ejemplo 5: API con Caché ===');
  
  const api = new CachedWebToWPAPI(CONFIG.apiKey, CONFIG.baseUrl);
  
  // Primera llamada - Cache MISS
  const settings1 = await api.getSettings();
  console.log('Primera llamada completada');
  
  // Segunda llamada - Cache HIT
  const settings2 = await api.getSettings();
  console.log('Segunda llamada completada (desde caché)');
}

// ============================================
// Ejemplo 6: React Component
// ============================================

const ReactExample = `
import React, { useState, useEffect } from 'react';

function SiteHeader() {
  const [siteInfo, setSiteInfo] = useState(null);
  const [settings, setSettings] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      const api = new WebToWPAPI(
        process.env.REACT_APP_WEBTOWP_API_KEY,
        process.env.REACT_APP_WEBTOWP_API_URL
      );

      try {
        const [info, config] = await Promise.all([
          api.getSiteInfo(),
          api.getSettings()
        ]);

        setSiteInfo(info.data);
        setSettings(config.data);
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, []);

  if (loading) return <div>Cargando...</div>;

  return (
    <header style={{ 
      backgroundColor: settings?.colors.primary 
    }}>
      <img 
        src={settings?.brand_identity.logo_principal} 
        alt={siteInfo?.name} 
      />
      <h1>{siteInfo?.name}</h1>
      <nav>
        {/* Tu navegación aquí */}
      </nav>
    </header>
  );
}

export default SiteHeader;
`;

// ============================================
// Ejemplo 7: Next.js con SSR
// ============================================

const NextJSExample = `
// pages/index.js
import { WebToWPAPI } from '../lib/webtowp-api';

export async function getServerSideProps() {
  const api = new WebToWPAPI(
    process.env.WEBTOWP_API_KEY,
    process.env.WEBTOWP_API_URL
  );

  try {
    const [siteInfo, settings] = await Promise.all([
      api.getSiteInfo(),
      api.getSettings()
    ]);

    return {
      props: {
        siteInfo: siteInfo.data,
        settings: settings.data
      }
    };
  } catch (error) {
    console.error('Error:', error);
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
      <p>{siteInfo?.description}</p>
      
      <style jsx>{\`
        h1 {
          color: \${settings?.colors.primary};
        }
      \`}</style>
    </div>
  );
}
`;

// ============================================
// Ejemplo 8: Error Handling Robusto
// ============================================

class RobustWebToWPAPI extends WebToWPAPI {
  async request(endpoint, retries = 3) {
    for (let i = 0; i < retries; i++) {
      try {
        const response = await fetch(`${this.baseUrl}${endpoint}`, {
          headers: {
            'X-WebToWP-Key': this.apiKey
          },
          timeout: 10000 // 10 segundos
        });

        if (!response.ok) {
          if (response.status === 403) {
            throw new Error('API Key inválida o expirada');
          }
          if (response.status === 404) {
            throw new Error('Endpoint no encontrado');
          }
          throw new Error(`HTTP ${response.status}`);
        }

        return await response.json();
      } catch (error) {
        console.error(`Intento ${i + 1} fallido:`, error.message);
        
        if (i === retries - 1) {
          throw error;
        }
        
        // Esperar antes de reintentar (exponential backoff)
        await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
      }
    }
  }
}

// ============================================
// Ejecutar Ejemplos
// ============================================

async function runAllExamples() {
  await example1_BasicUsage();
  await example2_GetThemeColors();
  await example3_GetSocialNetworks();
  await example4_RenderFooter();
  await example5_CachedAPI();
}

// Descomentar para ejecutar
// runAllExamples();

// ============================================
// Exportar para uso en módulos
// ============================================

if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    WebToWPAPI,
    CachedWebToWPAPI,
    RobustWebToWPAPI
  };
}
