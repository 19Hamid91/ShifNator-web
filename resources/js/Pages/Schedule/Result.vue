<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const page = usePage();

const props = defineProps({
    employees: {
        type: Array,
        required: true,
    },
});

const snackbar = ref({
    show: false,
    color: "",
    message: "",
    timeout: 4000,
});

const statuses = ref([
    { title: "Active", value: "active" },
    { title: "Inactive", value: "inactive" },
]);

const form = useForm({
    start_date: "20-06-2025",
    end_date: "26-06-2025",
    status: "",
    selected_employees: [],
});

const headers = ref([
    { title: "No", key: "no", align: "start", sortable: false },
    { title: "Name", key: "name", align: "start" },
    {
        title: "Unavailable Shift",
        key: "unavailable_shift",
        align: "start",
    },
    { title: "", key: "check", align: "center", sortable: false },
]);

const dataItems = ref(
    props.employees.map((emp, index) => {
        return {
            ...emp,
            no: index + 1,
            status: emp.status ?? "unknown",
            unavailable_shift: Object.entries(emp.unavailable_shift || {})
                .map(([day, shifts]) => {
                    const activeShifts = Object.entries(shifts)
                        .filter(([, val]) => val)
                        .map(([shift]) => shift);
                    return activeShifts.length
                        ? `${capitalize(day)} (${activeShifts.join(", ")})`
                        : null;
                })
                .filter(Boolean)
                .join(", "),
        };
    })
);

const submit = () => {
    form.post(route("schedule.store"), {
        onSuccess: () => form.reset(),
    });
};

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

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
                                    <div class="text-caption text-grey">
                                        {{ form.start_date }} -
                                        {{ form.end_date }}
                                    </div>
                                </div>
                            </v-col>

                            <v-col cols="auto" />
                        </v-row>
                    </v-card-title>
                    <v-card-text>
                        <v-data-table-virtual> </v-data-table-virtual>
                    </v-card-text>
                    <v-card-actions class="flex justify-end mx-2 my-2">
                        <v-btn
                            color="teal"
                            variant="tonal"
                            type="submit"
                            :loading="form.processing"
                            >use this schedule</v-btn
                        >
                    </v-card-actions>
                </v-card>

                <v-card elevation="4" rounded="lg">
                    <v-card-title class="d-flex justify-center items-center">
                        <h6 class="text-h6 ms-4">Remake Schedule</h6>
                    </v-card-title>
                    <form @submit.prevent="submit">
                        <v-card-text>
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        label="From"
                                        variant="outlined"
                                        v-model="form.start_date"
                                        type="date"
                                        required
                                        autofocus
                                        autocomplete="start_date"
                                        density="compact"
                                        :error-messages="form.errors.start_date"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        label="To"
                                        variant="outlined"
                                        v-model="form.end_date"
                                        type="date"
                                        required
                                        autofocus
                                        autocomplete="end_date"
                                        density="compact"
                                        :error-messages="form.errors.end_date"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12">
                                    <v-select
                                        label="Status"
                                        variant="outlined"
                                        :items="statuses"
                                        v-model="form.status"
                                        required
                                        density="compact"
                                        :error-messages="form.errors.status"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12">
                                    <v-card>
                                        <v-card-title
                                            >Employee List</v-card-title
                                        >
                                        <v-card-text class="overflow-auto">
                                            <v-data-table-virtual
                                                :headers="headers"
                                                :items="dataItems"
                                                class="elevation-1"
                                                item-value="id"
                                                density="compact"
                                                height="400"
                                                fixed-header
                                            >
                                                <template
                                                    #item.check="{ item }"
                                                >
                                                    <v-checkbox
                                                        hide-details
                                                        density="compact"
                                                        class="mx-auto"
                                                        color="teal-lighten-2"
                                                        :value="item.id"
                                                        v-model="
                                                            form.selected_employees
                                                        "
                                                    />
                                                </template>
                                            </v-data-table-virtual>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>
                        </v-card-text>
                        <v-card-actions class="flex justify-end mx-2 my-2">
                            <v-btn
                                color="deep-orange"
                                variant="tonal"
                                type="submit"
                                :loading="form.processing"
                                >regenerate</v-btn
                            >
                        </v-card-actions>
                    </form>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
