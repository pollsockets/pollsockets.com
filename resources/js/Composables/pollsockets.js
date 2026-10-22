import { createPollsocket } from 'pollsockets-js';

let socket = null;

/**
 * Wrap pollsockets-js so the demo can measure every poll request.
 * onStart runs before each poll request, and onFinish runs with its duration and size in bytes.
 */
export function useSocket(socketChannel, socketEvents, options = {}) {
    const { onStart = () => {}, onFinish = () => {}, ...pollsocketOptions } = options;

    socket?.stop();

    socket = createPollsocket(socketChannel, {
        ...pollsocketOptions,
        fetch: async (url, init) => {
            onStart();
            const start = Date.now();

            try {
                const response = await fetch(url, init);
                const body = await response.clone().arrayBuffer();
                onFinish({ time: Date.now() - start, size: body.byteLength });

                return response;
            } catch (error) {
                onFinish({ time: Date.now() - start, size: 0 });

                throw error;
            }
        },
    });

    Object.entries(socketEvents).forEach(([event, callback]) => socket.on(event, callback));

    socket.start();

    return socket;
}

export function stopSockets() {
    socket?.stop();
}
