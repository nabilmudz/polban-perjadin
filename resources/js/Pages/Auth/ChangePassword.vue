<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(route('password.update'), {
    onSuccess: () => {
      window.location.href = route('login')
    },
  })
}
</script>

<template>
  <Head title="Change Password" />

  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
      <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
        Ganti Password
      </h1>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <InputLabel for="current_password" value="Password lama" />
          <TextInput
            id="current_password"
            type="password"
            class="mt-1 block w-full"
            v-model="form.current_password"
            required
            autocomplete="current-password"
          />
          <InputError class="mt-2" :message="form.errors.current_password" />
        </div>

        <div>
          <InputLabel for="password" value="Password Baru" />
          <TextInput
            id="password"
            type="password"
            class="mt-1 block w-full"
            v-model="form.password"
            required
            autocomplete="new-password"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div>
          <InputLabel for="password_confirmation" value="Konfirmasi Password" />
          <TextInput
            id="password_confirmation"
            type="password"
            class="mt-1 block w-full"
            v-model="form.password_confirmation"
            required
            autocomplete="new-password"
          />
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>

        <div class="flex justify-center">
          <PrimaryButton
            class="w-full justify-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 rounded-md transition"
            :disabled="form.processing"
          >
            Save
          </PrimaryButton>
        </div>
      </form>
    </div>
  </div>
</template>