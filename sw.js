const CACHE_NAME = 'segalimentos-v1';

// Arquivos vitais que precisam abrir mesmo offline
const ASSETS_TO_CACHE = [
  '/index.php',
  '/checklist.php',
  // Adicione aqui os caminhos para o seu Bootstrap / CSS / JS principal, por exemplo:
  // '/css/bootstrap.min.css',
  // '/js/bootstrap.bundle.min.js',
  // '/includes/header.php' (O SW lida com as requisições que o navegador faz)
];

// Instalação do Service Worker e Armazenamento em Cache dos arquivos estáticos
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Armazenando arquivos estruturais em cache...');
      return cache.addAll(ASSETS_TO_CACHE);
    }).then(() => self.skipWaiting())
  );
});

// Ativação e limpeza de caches antigos
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log('Removendo cache antigo:', cache);
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Intercepta as requisições: Tenta a rede, se falhar (offline), busca no Cache
self.addEventListener('fetch', (event) => {
  // Ignora requisições do tipo POST (como envios de formulários normais)
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Se a requisição deu certo na rede, atualiza uma cópia dela no cache
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Se falhar (ex: falta de internet), busca o que tem guardado no cache do navegador
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // Se nem no cache tiver (ex: uma página que ele nunca abriu antes), você pode retornar um aviso
        });
      })
  );
});