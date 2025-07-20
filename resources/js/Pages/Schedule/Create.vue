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

const months = ref([
    { title: "January", value: 1 },
    { title: "February", value: 2 },
    { title: "March", value: 3 },
    { title: "April", value: 4 },
    { title: "May", value: 5 },
    { title: "June", value: 6 },
    { title: "July", value: 7 },
    { title: "August", value: 8 },
    { title: "September", value: 9 },
    { title: "October", value: 10 },
    { title: "November", value: 11 },
    { title: "Desember", value: 12 },
]);

const form = useForm({
    year: "",
    month: "",
    status: "",
    max_shift_per_employee: "",
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
        // onSuccess: () => form.reset(),
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
                <v-card elevation="4" rounded="lg">
                    <v-card-title class="d-flex justify-start items-center">
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
                        <h6 class="text-h6 ms-4">Schedule Detail</h6>
                    </v-card-title>
                    <form @submit.prevent="submit">
                        <v-card-text>
                            <v-row>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        label="Tahun"
                                        variant="outlined"
                                        v-model="form.year"
                                        type="number"
                                        required
                                        autofocus
                                        autocomplete="year"
                                        density="compact"
                                        :error-messages="form.errors.year"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        label="Month"
                                        variant="outlined"
                                        :items="months"
                                        v-model="form.month"
                                        required
                                        density="compact"
                                        :error-messages="form.errors.month"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" sm="6">
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
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        label="Max Shift"
                                        variant="outlined"
                                        v-model="form.max_shift_per_employee"
                                        type="number"
                                        required
                                        autofocus
                                        autocomplete="max_shift_per_employee"
                                        density="compact"
                                        :error-messages="
                                            form.errors.max_shift_per_employee
                                        "
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
                                                <!-- Custom cell for checkbox -->
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
                                color="teal"
                                variant="tonal"
                                type="submit"
                                :loading="form.processing"
                                >generate</v-btn
                            >
                        </v-card-actions>
                    </form>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
