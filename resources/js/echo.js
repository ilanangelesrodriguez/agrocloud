import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbKey = document.querySelector('meta[name="reverb-key"]')?.content;
const reverbHost = document.querySelector('meta[name="reverb-host"]')?.content;
const reverbPort = Number(document.querySelector('meta[name="reverb-port"]')?.content);
const forceTLS = document.querySelector('meta[name="reverb-scheme"]')?.content === 'https';

if (reverbKey && reverbHost) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort || 80,
        wssPort: reverbPort || 443,
        forceTLS,
        enabledTransports: forceTLS ? ['wss'] : ['ws'],
    });
}
