import { initializeApp, getApps } from 'firebase/app';
import { getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';
import Repository from '../repository';

let messagingInstance = null;
let firebaseApp = null;

const getFirebaseConfig = async () => {
    const response = await Repository.get('/api/firebase-config');
    const data = response && response.data ? response.data : null;
    return data && data.result ? data.result : null;
};

const ensureFirebaseApp = async () => {
    if (firebaseApp) {
        return firebaseApp;
    }

    if (getApps().length) {
        firebaseApp = getApps()[0];
        return firebaseApp;
    }

    const config = await getFirebaseConfig();
    if (!config) {
        throw new Error('Missing firebase config');
    }

    firebaseApp = initializeApp(config);
    return firebaseApp;
};

const ensureMessaging = async () => {
    if (messagingInstance) {
        return messagingInstance;
    }

    await ensureFirebaseApp();
    messagingInstance = getMessaging();
    return messagingInstance;
};

const registerServiceWorker = async () => {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    try {
        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
        await navigator.serviceWorker.ready;
        return registration;
    } catch (error) {
        console.error('[FCM] Service worker registration failed:', error);
        return null;
    }
};

export const initWebFcm = async ({ onMessageCallback } = {}) => {
    const supported = await isSupported();
    if (!supported) {
        return null;
    }

    if (typeof Notification === 'undefined') {
        return null;
    }

    const token = localStorage.getItem('token');
    if (!token) {
        return null;
    }

    const permission = await Notification.requestPermission();
    console.log('[FCM] Notification permission:', permission);
    if (permission !== 'granted') {
        return null;
    }

    const config = await getFirebaseConfig();
    if (!config || !config.vapidKey) {
        throw new Error('Missing VAPID key');
    }

    const messaging = await ensureMessaging();
    const swRegistration = await registerServiceWorker();
    if (!swRegistration) {
        return null;
    }

    let fcmToken = null;
    try {
        fcmToken = await getToken(messaging, {
            vapidKey: config.vapidKey,
            serviceWorkerRegistration: swRegistration,
        });
    } catch (error) {
        console.error('[FCM] getToken failed:', error);
        return null;
    }

    console.log('[FCM] Token:', fcmToken || '(empty)');

    if (fcmToken) {
        try {
            await Repository.post('/api/device-tokens', {
                token: fcmToken,
                platform: 'web',
            });
            console.log('[FCM] Device token registered');
        } catch (error) {
            console.error('[FCM] Device token register failed:', error);
        }
    }

    onMessage(messaging, (payload) => {
        // Foreground message handler
        console.log('[FCM] Foreground message received:', payload);

        const notification = payload && payload.notification ? payload.notification : null;
        if (notification && Notification.permission === 'granted') {
            try {
                new Notification(notification.title || 'Notification', {
                    body: notification.body || '',
                    icon: '/assets/images/logo-blu-sm.png',
                });
            } catch (error) {
                console.error('[FCM] Foreground notification error:', error);
            }
        }

        if (onMessageCallback) {
            onMessageCallback(payload);
        }

        window.dispatchEvent(new CustomEvent('fcm-message', { detail: payload }));
    });

    return fcmToken || null;
};
