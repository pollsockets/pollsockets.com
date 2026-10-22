<script setup>
import Panel from "@/Components/Panel.vue"

defineProps({
    pollState: Object,
})

function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0 Bytes'

    const k = 1024
    const dm = decimals < 0 ? 0 : decimals
    const sizes = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']

    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}

function msToTime(ms) {
    let seconds = ms / 1000;

    if (seconds < 60) {
        return `${seconds.toFixed(2)} sec`
    }

    let minutes = seconds / 60;

    if (minutes < 60) {
        return `${minutes.toFixed(2)} min`
    }

    let hours = minutes / 60;

    return `${hours.toFixed(2)} hr`
}
</script>

<template>
    <Panel>
        <slot />
        <div class="sm:flex sm:justify-between mt-4">
            <div class="flex items-center gap-x-2">
                Requests: <span class="text-indigo-300 text-lg">{{ pollState.count }}</span>
                <span class="relative h-3 w-3">
                        <span v-if="pollState.inProgress" class="flex">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-500"></span>
                        </span>
                    </span>
            </div>
            <div>Size: <span class="text-indigo-300 text-lg">{{ formatBytes(pollState.size) }}</span></div>
            <div>Time: <span class="text-indigo-300 text-lg">{{ msToTime(pollState.time) }}</span></div>
        </div>
    </Panel>
</template>
