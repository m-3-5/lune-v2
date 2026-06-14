self.addEventListener('fetch', function (event) {
    event.respondWith(fetch(event.request));
});

self.addEventListener('push', function (event) {
    if (!event.data) return;
    let data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'Jlune', body: event.data.text() };
    }
    const options = {
        body: data.body || '',
        icon: '/pwa-icons/admin-192.png',
        badge: '/pwa-icons/admin-192.png',
        tag: data.tag || 'jlune-admin',
        vibrate: data.vibrate || [200, 100, 200, 100, 200],
        data: { url: data.url || '/admin/progetto' },
        requireInteraction: true,
    };
    event.waitUntil(self.registration.showNotification(data.title || 'Jlune Gestione', options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data?.url || '/admin';
    event.waitUntil(clients.openWindow(url));
});

self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(clients.claim());
});
