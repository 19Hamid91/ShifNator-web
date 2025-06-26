<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- stat -->
                <div class="flex flex-col md:flex-row w-full gap-4 mb-4">
                    <v-card class="mx-auto w-full">
                        <v-card-item
                            title="Employee"
                            bg-color="red"
                        ></v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">25</div>
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
                        <v-card-item title="Schedule"> </v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">5</div>
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
                        <v-card-item title="Shift"> </v-card-item>

                        <v-card-text class="py-0">
                            <v-row align="center" no-gutters>
                                <v-col cols="6">
                                    <div class="text-h5 text-md-h3">25</div>
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
                                <div>
                                    <v-icon size="x-large" color="success"
                                        >mdi-file-excel</v-icon
                                    >
                                    <v-icon size="x-large" color="red-darken-2"
                                        >mdi-file-pdf-box</v-icon
                                    >
                                </div>
                            </div>
                        </template>
                    </v-card-item>
                    <v-data-table-virtual> </v-data-table-virtual>
                </v-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
