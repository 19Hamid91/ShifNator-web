<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const passwordType = ref("password");

const togglePassword = () => {
    passwordType.value =
        passwordType.value === "password" ? "text" : "password";
};

const form = useForm({
    email: "admin@mail.com",
    password: "password",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4">
                <v-text-field
                    label="Email"
                    variant="solo-filled"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    density="compact"
                    :error-messages="form.errors.email"
                    hide-details
                >
                    <template #prepend-inner>
                        <v-icon class="me-3" color="teal">mdi-account</v-icon>
                    </template>
                </v-text-field>
            </div>

            <div class="mt-6">
                <v-text-field
                    label="Password"
                    variant="solo-filled"
                    v-model="form.password"
                    :type="passwordType"
                    required
                    density="compact"
                    :error-messages="form.errors.password"
                    hide-details
                >
                    <template #prepend-inner>
                        <v-icon class="me-3" color="teal">mdi-lock</v-icon>
                    </template>
                    <template #append-inner>
                        <v-icon
                            class="ms-3"
                            color="teal"
                            @click="togglePassword"
                            >{{
                                passwordType == "password"
                                    ? "mdi-eye"
                                    : "mdi-eye-off"
                            }}</v-icon
                        >
                    </template>
                </v-text-field>
            </div>

            <div class="mt-8 mb-2 flex items-center justify-end">
                <v-btn
                    type="submit"
                    color="teal"
                    block
                    class="ms-4"
                    :loading="form.processing"
                >
                    Log in
                </v-btn>
            </div>
        </form>
    </GuestLayout>
</template>
