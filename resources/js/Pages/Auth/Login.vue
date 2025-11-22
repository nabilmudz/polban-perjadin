    <script setup>
    import Checkbox from '@/Components/Checkbox.vue'
    import GuestLayout from '@/Layouts/GuestLayout.vue'
    import InputError from '@/Components/InputError.vue'
    import InputLabel from '@/Components/InputLabel.vue'
    import PrimaryButton from '@/Components/PrimaryButton.vue'
    import TextInput from '@/Components/TextInput.vue'
    import { Head, useForm } from '@inertiajs/vue3'

    defineProps({
    canResetPassword: Boolean,
    status: String,
    })

    const form = useForm({
    email: '',
    password: '',
    remember: false,
    })

    const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
    }
    </script>

    <template>
    <GuestLayout>
        <Head title="Login" />
        <header class="text-center">
        <img
            src="/images/polban.png"
            alt="Polban Logo"
            class="mx-auto mb-4 w-28 h-auto"
        />
        <h1 class="text-2xl font-bold text-gray-800 max-w-xs" >Web Perjalanan Dinas Politeknik Negeri Bandung</h1>
        </header>

        <section class="max-w-[380px] w-full mx-auto p-6">
            <div>
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    </div>

                    <PrimaryButton
                        class="w-full justify-center"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        >
                        Log in
                    </PrimaryButton>
                </form>
            </div>
        </section>
    </GuestLayout>
    </template>
