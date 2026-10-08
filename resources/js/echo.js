import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED !== 'false';
    const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
    let reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    // On Windows, 'localhost' resolves to IPv6 (::1) which fails to connect to IPv4 Reverb (0.0.0.0:8080).
    // Normalize localhost to 127.0.0.1 for local development to ensure reliable WebSocket handshake.
    if (reverbHost === 'localhost' || (!import.meta.env.VITE_REVERB_HOST && window.location.hostname === 'localhost')) {
        reverbHost = '127.0.0.1';
    }
    const reverbPort = import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT, 10) : 8080;
    const isHttps = window.location.protocol === 'https:' || import.meta.env.VITE_REVERB_SCHEME === 'https';

    if (reverbEnabled && reverbKey) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: isHttps,
            enabledTransports: isHttps ? ['wss'] : ['ws'],
        });

        // Graceful disconnect if Reverb WebSocket is unreachable (prevent infinite error spam)
        if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
            const pusher = window.Echo.connector.pusher;
            let failureHandled = false;
            const handleDisconnect = () => {
                if (failureHandled) return;
                failureHandled = true;
                try {
                    pusher.disconnect();
                } catch (err) {}
            };
            pusher.connection.bind('unavailable', handleDisconnect);
            pusher.connection.bind('failed', handleDisconnect);
            pusher.connection.bind('error', (err) => {
                // If connection refused or socket error occurs, immediately disconnect to stop spamming
                handleDisconnect();
            });
        }
    }
} catch (e) {
    console.warn('Laravel Echo initialization fallback active:', e);
}

// Fallback stub if Echo could not connect or initialize
if (typeof window !== 'undefined' && typeof window.Echo === 'undefined') {
    const createStubChannel = () => {
        const stub = {
            listen: () => stub,
            listenForWhisper: () => stub,
            stopListening: () => stub,
            notification: () => stub,
            whisper: () => stub,
            error: () => stub,
            subscribed: () => stub,
        };
        return stub;
    };

    window.Echo = {
        socketId: () => undefined,
        private: () => createStubChannel(),
        channel: () => createStubChannel(),
        encryptedPrivate: () => createStubChannel(),
        join: () => ({
            here: () => ({
                joining: () => ({
                    leaving: () => ({
                        listen: () => createStubChannel(),
                    }),
                }),
            }),
            listen: () => createStubChannel(),
            error: () => createStubChannel(),
        }),
        leave: () => {},
        leaveChannel: () => {},
        disconnect: () => {},
        connector: {
            socketId: () => undefined,
            channels: {},
        }
    };
}
