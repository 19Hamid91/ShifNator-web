<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    id: {
        type: Number,
    },
    name: {
        type: String,
    },
    status: {
        type: String,
    },
    unavailable_shift: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

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

const days = [
    { key: "monday", label: "Monday" },
    { key: "tuesday", label: "Tuesday" },
    { key: "wednesday", label: "Wednesday" },
    { key: "thursday", label: "Thursday" },
    { key: "friday", label: "Friday" },
    { key: "saturday", label: "Saturday" },
    { key: "sunday", label: "Sunday" },
];

const shiftTypes = ["morning", "night"];

const form = useForm({
    name: props?.name,
    status: props?.status,
    unavailable_shift: {
        monday: { morning: false, night: false },
        tuesday: { morning: false, night: false },
        wednesday: { morning: false, night: false },
        thursday: { morning: false, night: false },
        friday: { morning: false, night: false },
        saturday: { morning: false, night: false },
        sunday: { morning: false, night: false },
        ...props.unavailable_shift, // overwrite default if any
    },
});

console.log(props);

const submit = () => {
    form.patch(route("employee.update", props.id));
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
        <Head title="Employee" />

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
                        <Link :href="route('employee.index')">
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
                        <h6 class="text-h6 ms-4">Employee List</h6>
                    </v-card-title>
                    <form @submit.prevent="submit">
                        <v-card-text>
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field
                                        label="Name"
                                        variant="outlined"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        density="compact"
                                        :error-messages="form.errors.name"
                                        hide-details="auto"
                                        color="teal-lighten-2"
                                    />
                                </v-col>
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
                            </v-row>

                            <v-row>
                                <v-col cols="12">
                                    <v-card>
                                        <v-card-title
                                            >Unavailable Shift</v-card-title
                                        >
                                        <v-card-text class="overflow-auto">
                                            <table
                                                class="w-full border border-gray-300 text-center"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th class="p-2 border">
                                                            Shift
                                                        </th>
                                                        <th
                                                            v-for="day in days"
                                                            :key="day.key"
                                                            class="p-2 border"
                                                        >
                                                            {{ day.label }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        v-for="shift in shiftTypes"
                                                        :key="shift"
                                                    >
                                                        <td
                                                            class="p-2 border font-bold"
                                                        >
                                                            {{
                                                                capitalize(
                                                                    shift
                                                                )
                                                            }}
                                                        </td>
                                                        <td
                                                            v-for="day in days"
                                                            :key="day.key"
                                                            class="p-2 border text-center align-middle"
                                                        >
                                                            <div
                                                                class="flex justify-center items-center h-full"
                                                            >
                                                                <v-checkbox
                                                                    density="compact"
                                                                    hide-details
                                                                    color="teal-lighten-2"
                                                                    v-model="
                                                                        form
                                                                            .unavailable_shift[
                                                                            day
                                                                                .key
                                                                        ][shift]
                                                                    "
                                                                    class="ma-0 pa-0"
                                                                />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
                                >update</v-btn
                            >
                        </v-card-actions>
                    </form>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
