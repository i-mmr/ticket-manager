<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  pendingRegistration: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  token: props.pendingRegistration.token,
  name: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(`/register/complete/${props.pendingRegistration.id}`, {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <Head title="登録を完了" />

  <div class="auth-page">
    <div class="auth-card">
      <Link href="/" class="brand-link">Ticket Manager</Link>
      <h1>登録を完了する</h1>
      <p class="lead">
        <strong>{{ pendingRegistration.email }}</strong>
        で利用する名前とパスワードを設定してください。
      </p>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="name">名前</label>
          <input id="name" v-model="form.name" type="text" autocomplete="name" />
          <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input id="password" v-model="form.password" type="password" autocomplete="new-password" />
          <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
        </div>

        <div class="form-group">
          <label for="password_confirmation">パスワード確認</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
          />
        </div>

        <button type="submit" :disabled="form.processing">
          {{ form.processing ? '送信中...' : '登録する' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 28px 16px;
  background: #f5f7fb;
  color: #172554;
}

.auth-card {
  width: 100%;
  max-width: 460px;
  box-sizing: border-box;
  background: #fff;
  padding: 32px;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.brand-link {
  display: inline-block;
  margin-bottom: 18px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
}

h1 {
  margin: 0 0 10px;
  font-size: 28px;
}

.lead {
  margin: 0 0 22px;
  color: #64748b;
  line-height: 1.7;
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 12px;
  border: 1px solid #cfd6e4;
  border-radius: 8px;
  font-size: 14px;
}

button {
  width: 100%;
  border: 0;
  border-radius: 8px;
  padding: 12px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  background: #2563eb;
  color: white;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  margin: 6px 0 0;
  color: #dc2626;
  font-size: 13px;
}
</style>
