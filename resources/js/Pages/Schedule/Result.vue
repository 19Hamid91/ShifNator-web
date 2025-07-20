<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const page = usePage();

const props = defineProps({
    shift: {
        type: Array,
        required: true,
    },
    schedule: {
        type: Object,
        required: true,
    },
    periode: {
        type: String,
        required: true,
    },
});

const type = "month";
const weekday = [0, 1, 2, 3, 4, 5, 6];
const value = ref([new Date(props.schedule.year, props.schedule.month - 1, 1)]);

const events = computed(() =>
    props.shift.map((e) => ({
        ...e,
        start: new Date(e.start),
        end: new Date(e.end),
    }))
);

const snackbar = ref({
    show: false,
    color: "",
    message: "",
    timeout: 4000,
});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            snackbar.value = {
                show: true,
                color: "green",
                message: flash.success,
                timeout: 3000,
            };
        } else if (flash.error) {
            snackbar.value = {
                show: true,
                color: "red",
                message: flash.error,
                timeout: 5000,
            };
        }
    },
    { immediate: true, deep: true }
);
</script>
<template>
    <AuthenticatedLayout>
        <Head title="Schedule" />

        <!-- snackbar -->
        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            :timeout="snackbar.timeout"
            location="bottom right"
            timer="true"
            variant="elevated"
        >
            {{ snackbar.message }}
        </v-snackbar>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <v-card elevation="4" rounded="lg" class="mb-4">
                    <v-card-title class="px-4">
                        <v-row
                            align="center"
                            justify="space-between"
                            class="w-full"
                        >
                            <v-col cols="auto">
                                <Link :href="route('schedule.index')">
                                    <v-btn
                                        icon
                                        variant="text"
                                        color="grey-darken-2"
                                        class="hover:bg-grey-lighten-3"
                                    >
                                        <v-icon size="x-large"
                                            >mdi-arrow-left-bold</v-icon
                                        >
                                    </v-btn>
                                </Link>
                            </v-col>

                            <v-col class="text-center">
                                <div>
                                    <h6 class="text-h6">Result Schedule</h6>
                                    <div class="text-semibold text-grey">
                                        {{ props.periode }}
                                    </div>
                                </div>
                            </v-col>

                            <v-col cols="auto" />
                        </v-row>
                    </v-card-title>
                    <v-card-text>
                        <v-calendar
                            ref="calendar"
                            v-model="value"
                            :events="events"
                            :view-mode="type"
                            :weekdays="weekday"
                            :hide-header="true"
                            :hide-week-number="true"
                            color="teal"
                            class="mt-4"
                        ></v-calendar>
                    </v-card-text>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
