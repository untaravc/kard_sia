/* eslint-disable no-undef */
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Register handlers during initial evaluation to satisfy browser requirements.
self.addEventListener('push', () => {});
self.addEventListener('pushsubscriptionchange', () => {});

let initPromise = null;

const initFirebase = async () => {
    if (initPromise) {
        return initPromise;
    }

    initPromise = fetch('/api/firebase-config')
        .then((response) => response.json())
        .then((data) => {
            const config = data && data.result ? data.result : null;
            if (!config) {
                throw new Error('Missing firebase config');
            }

            if (!firebase.apps.length) {
                firebase.initializeApp(config);
            }

            return firebase.messaging();
        })
        .catch(() => null);

    return initPromise;
};

initFirebase().then((messaging) => {
    if (!messaging) {
        return;
    }

    messaging.onBackgroundMessage((payload) => {
        console.log('[FCM] Background message received:', payload);
        const notification = payload && payload.notification ? payload.notification : {};
        const title = notification.title || 'Notification';
        const options = {
            body: notification.body || '',
            icon: '/assets/images/logo-blu-sm.png',
            data: payload.data || {},
        };

        self.registration.showNotification(title, options);
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = event.notification && event.notification.data ? event.notification.data.link : null;
    const url = target || '/blu';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
            return null;
        })
    );
});
