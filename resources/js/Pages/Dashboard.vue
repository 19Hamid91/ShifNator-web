<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    employeeCount: {
        type: Number,
        default: 0,
    },
    scheduleCount: {
        type: Number,
        default: 0,
    },
    shift: {
        type: Array,
        default: () => [], // default array kosong
    },
    schedule: {
        type: Object,
        default: () => ({}), // default object kosong
    },
    periode: {
        type: String,
        default: "", // default string kosong
    },
});

const today = new Date().toISOString().slice(0, 10);
const todayShiftCount = computed(() => {
    return props.shift.filter((s) => s.start === today).length;
});

const type = "month";
const weekday = [0, 1, 2, 3, 4, 5, 6];
const defaultDate = new Date();

const value = ref([
    props.schedule
        ? new Date(props.schedule?.year, props.schedule?.month - 1, 1)
        : new Date(defaultDate.getFullYear(), defaultDate.getMonth(), 1),
]);

const events = computed(() =>
    (props.shift ?? []).map((e) => ({
        ...e,
        start: new Date(e.start),
        end: new Date(e.end),
    }))
);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- stat -->
                <div class="flex flex-col md:flex-row w-full gap-4 mb-4">
                    <v-card class="mx-auto w-full">
                        <v-card-item title="Active Employee"></v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">
                                        {{ employeeCount }}
                                    </div>
                                </v-col>

                                <v-col class="text-right" cols="6">
                                    <v-icon
                                        color="primary"
                                        :size="
                                            $vuetify.display.smAndDown ? 48 : 88
                                        "
                                        icon="mdi-account-group"
                                    />
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                    <v-card class="mx-auto w-full">
                        <v-card-item title="Active Schedule"> </v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">
                                        {{ scheduleCount }}
                                    </div>
                                </v-col>

                                <v-col class="text-right" cols="6">
                                    <v-icon
                                        color="warning"
                                        :size="
                                            $vuetify.display.smAndDown ? 48 : 88
                                        "
                                        icon="mdi-calendar"
                                    />
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                    <v-card class="mx-auto w-full">
                        <v-card-item title="Today Shift"> </v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">
                                        {{ todayShiftCount }}
                                    </div>
                                </v-col>

                                <v-col class="text-right" cols="6">
                                    <v-icon
                                        color="grey"
                                        :size="
                                            $vuetify.display.smAndDown ? 48 : 88
                                        "
                                        icon="mdi-swap-horizontal-bold"
                                    />
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </div>

                <!-- latest schedule table -->
                <v-card>
                    <v-card-item>
                        <template #default>
                            <div
                                class="d-flex justify-space-between align-center w-100"
                            >
                                <span class="text-h6">Latest Schedule</span>
                                <div class="text-semibold text-grey">
                                    Periode: {{ props.periode }}
                                </div>
                            </div>
                        </template>
                    </v-card-item>
                    <div class="ma-4">
                        <template v-if="events.length">
                            <v-calendar
                                ref="calendar"
                                v-model="value"
                                :events="events"
                                :view-mode="type"
                                :weekdays="weekday"
                                :hide-header="true"
                                :hide-week-number="true"
                                color="calendarteal"
                            />
                        </template>
                        <template v-else>
                            <v-sheet
                                class="pa-10 text-center"
                                color="grey-lighten-4"
                                rounded
                                elevation="0"
                            >
                                <v-icon size="48" color="grey"
                                    >mdi-calendar-remove</v-icon
                                >
                                <div class="text-subtitle-1 mt-2">
                                    No schedule available
                                </div>
                            </v-sheet>
                        </template>
                    </div>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
