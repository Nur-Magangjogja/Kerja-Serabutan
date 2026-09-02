import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Safe fallback for Laravel Echo when WebSocket/Pusher is not active
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

