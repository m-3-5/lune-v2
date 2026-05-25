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
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
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
