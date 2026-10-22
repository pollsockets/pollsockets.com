<script setup>
import {Head, useHttp} from '@inertiajs/vue3';
import Table from "@/Components/Table.vue"
import {reactive, ref} from "vue"
import {useSocket} from "@/Composables/pollsockets.js"
import Info from "@/Pages/Info.vue"
import Panel from "@/Components/Panel.vue"
import {Switch, SwitchGroup, SwitchLabel} from "@headlessui/vue"

let props = defineProps({
    'since': Number,
    'people': Array,
});

const pollState = reactive({people: props.people, count: 0, size: 0, time: 0, inProgress: false})
const pollTimings = reactive({start: 0, end: 0})

const pollSocketState = reactive({people: props.people, count: 0, size: 0, time: 0, inProgress: 0})
const pollSocketTimings = reactive({fetch: {start: 0, end: 0}})

const optimized = ref(false)

function refresh() {
    setTimeout(() => {
        pollState.count++;
        pollTimings.start = Date.now()
        pollState.inProgress = true

        fetch('/people' + (optimized.value ? '?noSleep=1' : ''))
            .then((response) => response.json())
            .then((data) => {
                pollTimings.end = Date.now()
                pollState.time += pollTimings.end - pollTimings.start
                pollState.inProgress = false
                pollState.size += data.size;
                pollState.people = data.people
                refresh();
            })
    },  1000);
}

refresh();

function refreshPollsocket() {
    pollSocketTimings.fetch.start = Date.now()
    pollSocketState.count++
    pollSocketState.inProgress++

    fetch('/people' + (optimized.value ? '?noSleep=1' : ''))
        .then((response) => response.json())
        .then((data) => {
            pollSocketState.inProgress--
            pollSocketTimings.fetch.end = Date.now()
            pollSocketState.time += pollSocketTimings.fetch.end - pollSocketTimings.fetch.start

            pollSocketState.people = data.people
            pollSocketState.size += data.size;
        })
}

useSocket(`pollsockets`, {
    'refresh': () => refreshPollsocket(),
}, {
    interval: 1000,
    since: props.since,
    onStart: () => {
        pollSocketState.count++
        pollSocketState.inProgress++
    },
    onFinish: ({time, size}) => {
        pollSocketState.time += time
        pollSocketState.size += size
        pollSocketState.inProgress--
    }
})

const simulate = useHttp()

</script>

<template>
    <Head title="Polling vs Pollsockets"/>

    <div
        class="relative flex justify-center items-center min-h-screen bg-dots bg-center bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <div class="w-full p-6 lg:p-8 xl:flex xl:justify-around xl:space-x-6 space-y-12 xl:space-y-0">
            <Panel>
                <svg class="w-2/3" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 340 80" preserveAspectRatio="xMidYMid meet">
                    <path d="M7.66 64.67C.69 62.38 0 61.51 0 55V23.34c0-2.25.56-3.68 2.65-4.85Q18 9.74 33.25.74a4.32 4.32 0 0 1 5 .08Q53.29 9.69 68.5 18.35a4.54 4.54 0 0 1 2.65 4.52q-.14 17.51 0 35a4.49 4.49 0 0 1-2.54 4.56c-8.5 4.77-16.91 9.7-25.38 14.6-2-5-1.12-7.94 3-10.37 5-2.94 10.15-5.77 15.1-8.85a4.87 4.87 0 0 0 2.22-3.27c.17-9.2.09-18.41.07-27.61a11 11 0 0 0-.4-1.72 31.33 31.33 0 0 0-3.07 1.33c-7.1 4.07-14.14 8.27-21.31 12.22-2.3 1.28-3 2.82-3 5.36.13 12.22.06 24.45.06 36.72-6-1.37-7.67-3.31-7.67-8.93 0-10.66.1-21.32-.07-32a5.39 5.39 0 0 1 3.25-5.52C38.1 30.83 44.55 27 51.06 23.21c1.52-.88 3-1.82 4.55-2.76-3.91-4.22-5.39-4.48-9.68-2-7.09 4.11-14.15 8.27-21.3 12.26a5 5 0 0 0-2.92 5.07c.12 11 .05 22 .05 33v3.7c-5.35-.79-7.39-3-7.39-7.92 0-11.12.05-22.23 0-33.34a4.7 4.7 0 0 1 2.74-4.79C23.66 22.8 30.13 19 36.63 15.2c1.61-.93 3.18-1.93 4.82-2.93-3.9-4.2-5.37-4.46-9.71-1.95-6.8 3.93-13.55 8-20.44 11.73-2.66 1.46-3.78 3.12-3.72 6.29.21 12 .08 24.01.08 36.33z" class="fill-red-500"></path>
                    <path d="M42.46 61.39v-16a2.27 2.27 0 0 1 1-1.61c4.28-2.58 8.61-5.06 13.6-8v16a2.29 2.29 0 0 1-.95 1.63C51.8 56 47.46 58.5 42.46 61.39z" class="fill-indigo-600"></path>
                    <text x="85" y="50" font-size="50" class="fill-white">pollsockets</text>
                </svg>
                <div class="mt-6 prose prose-invert max-w-none xl:max-w-2xl">
                    <h1>Polling vs Pollsockets</h1>
                    <p>A simple example showing differences between polling and pollsockets. In both examples data comes from <code>/people</code> endpoint that takes 3 seconds to compute and return 10 rows of data.</p>
                    <p>With current set up (worst case scenario) polling loses heavily, because it keeps hitting an expensive endpoint thus increasing load on the server.</p>
                    <p>Even if the endpoint is optimized (e.g. cache) polling still performs worse because of the amount of data that is transmitted even though it mostly never changes.</p>
                    <SwitchGroup as="div" class="flex justify-center">
                        <Switch v-model="optimized" :class="[optimized ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']">
                            <span aria-hidden="true" :class="[optimized ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out']" />
                        </Switch>
                        <SwitchLabel as="span" class="ml-3 text-sm">
                            <span class="font-medium text-white">Use optimized endpoint</span>
                        </SwitchLabel>
                    </SwitchGroup>
                    <p>You can force data change by clicking the button below to simulate an automated process on the server.</p>
                </div>
                <div class="mt-6 flex justify-center">
                    <button @click="simulate.post('/simulate')" type="button" :disabled="simulate.processing" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:grayscale" :class="{'cursor-not-allowed': simulate.processing}">Simulate data change</button>
                </div>
            </Panel>

            <div class="space-y-6 xl:max-w-3xl">
                <Info :poll-state="pollState">
                    <h2 class="text-xl">Polling</h2>
                    <p class="text-sm">Regular polling mechanism - fetching table data every second. Produces less requests, but each request takes a long time and consumes bandwidth even when data doesn't change.</p>
                </Info>
                <Table :people="pollState.people"/>
            </div>
            <div class="space-y-6 xl:max-w-3xl">
                <Info :poll-state="pollSocketState">
                    <h2 class="text-xl">Pollsockets</h2>
                    <p class="text-sm">Polling for actions every second and fetching data only when it changes. Produces more requests, but most of them are very fast and consume next to nothing in bandwidth.</p>
                </Info>
                <Table :people="pollSocketState.people"/>
            </div>
        </div>
    </div>
</template>

