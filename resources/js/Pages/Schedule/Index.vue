<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const page = usePage();

const snackbar = ref({
    show: false,
    color: "",
    message: "",
    timeout: 4000,
});

const loadingStatus = ref({});
const changingId = ref(null);

const fecthData = async ({ page, itemsPerPage, sortBy, search }) => {
    const params = new URLSearchParams({
        page,
        per_page: itemsPerPage,
        sort_by: sortBy?.[0]?.key || "year",
        order: sortBy?.[0]?.order || "desc",
        search: search || "",
    });

    try {
        const res = await fetch(`/schedule/table?${params.toString()}`);
        const data = await res.json();

        return {
            items: data.data.data,
            total: data.data.total,
        };
    } catch (error) {
        console.error("Fail to fetch data:", error);
        return { items: [], total: 0 };
    }
};
const lastOptions = ref({});
const itemsPerPage = ref(10);
const itemsPerPageOptions = [
    { value: 5, title: "5" },
    { value: 10, title: "10" },
    { value: 25, title: "25" },
    { value: 50, title: "50" },
    { value: 100, title: "100" },
];
const headers = ref([
    { title: "No", key: "no", align: "start", sortable: false },
    { title: "Periode", key: "periode", align: "start" },
    { title: "Status", key: "status", align: "center" },
    {
        title: "Max Shift per Employee",
        key: "max_shift_per_employee",
        align: "center",
    },
    { title: "Action", key: "action", align: "center", sortable: false },
]);
const dataItems = ref([]);
const loading = ref(true);
const totalItems = ref(0);
const search = ref("");
const currentPage = ref(1);
function loadItems(options) {
    lastOptions.value = options;
    currentPage.value = options.page;
    loading.value = true;

    fecthData(options).then(({ items, total }) => {
        dataItems.value = items;
        totalItems.value = total;
        loading.value = false;
    });
}

function toggleStatus(scheduleId) {
    changingId.value = scheduleId;
    loadingStatus.value[scheduleId] = true;
    router.patch(route("schedule.change", scheduleId));
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
        if (changingId.value !== null) {
            loadingStatus.value[changingId.value] = false;
            changingId.value = null;
        }
        loadItems(lastOptions.value);
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
                    <!-- Tabel -->
                    <v-data-table-server
                        v-model:items-per-page="itemsPerPage"
                        :headers="headers"
                        :items="dataItems"
                        :items-length="totalItems"
                        :loading="loading"
                        :search="search"
                        item-value="id"
                        :items-per-page-options="itemsPerPageOptions"
                        @update:options="loadItems"
                    >
                        <!-- Toolbar Atas -->
                        <template v-slot:top>
                            <div
                                class="d-flex justify-between align-center mt-4 mb-2 mx-4"
                            >
                                <h6 class="text-h6">Schedule List</h6>

                                <div class="d-flex align-center gap-2">
                                    <v-text-field
                                        v-model="search"
                                        density="compact"
                                        placeholder="Search..."
                                        hide-details
                                        style="
                                            max-width: 200px;
                                            min-width: 150px;
                                        "
                                        variant="outlined"
                                    ></v-text-field>

                                    <Link :href="route('schedule.create')">
                                        <v-btn
                                            color="teal"
                                            prepend-icon="mdi-plus"
                                            >Generate</v-btn
                                        >
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <!-- Kolom Nomor -->
                        <template v-slot:item.no="{ index }">
                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                        </template>

                        <!-- Kolom Status -->
                        <template #item.status="{ item }">
                            <div class="flex justify-center">
                                <v-chip
                                    :color="
                                        item.status == 'active'
                                            ? 'success'
                                            : 'error'
                                    "
                                    class="w-full justify-center"
                                    >{{
                                        item.status == "active"
                                            ? "Active"
                                            : "Inactive"
                                    }}</v-chip
                                >
                            </div>
                        </template>

                        <!-- Kolom action -->
                        <template #item.action="{ item }">
                            <div class="flex justify-center gap-2">
                                <Link :href="route('schedule.result', item.id)">
                                    <v-btn
                                        color="warning"
                                        size="small"
                                        prepend-icon="mdi-calendar"
                                        >Show</v-btn
                                    >
                                </Link>
                                <v-btn
                                    v-if="item.status === 'active'"
                                    color="pink"
                                    size="small"
                                    :loading="loadingStatus[item.id] === true"
                                    @click="toggleStatus(item.id)"
                                >
                                    Deactivate
                                </v-btn>

                                <v-btn
                                    v-else
                                    color="green"
                                    size="small"
                                    :loading="loadingStatus[item.id] === true"
                                    @click="toggleStatus(item.id)"
                                >
                                    Activate
                                </v-btn>
                            </div>
                        </template>
                    </v-data-table-server>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
