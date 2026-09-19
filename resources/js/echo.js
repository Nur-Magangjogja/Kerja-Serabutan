import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
    const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    const reverbPort = import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT, 10) : 8080;
    const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || (window.location.protocol === 'https:' ? 'https' : 'http');

    if (reverbKey) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }
} catch (e) {
    console.warn('Laravel Echo initialization fallback active:', e);
}

// Fallback stub if Echo could not connect or initialize
if (typeof window !== 'undefined' && typeof window.Echo === 'undefined') {
    window.Echo = {
        socketId: () => undefined,
        private: () => ({
            listen: () => ({}),
            stopListening: () => ({}),
        }),
        channel: () => ({
            listen: () => ({}),
            stopListening: () => ({}),
        }),
        join: () => ({
            here: () => ({
                joining: () => ({
                    leaving: () => ({
                        listen: () => ({}),
                    }),
                }),
            }),
        }),
        leave: () => {},
        leaveChannel: () => {},
        connector: {
            socketId: () => undefined,
            channels: {},
        }
    };
}
