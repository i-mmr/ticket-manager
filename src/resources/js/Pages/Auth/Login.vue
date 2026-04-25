<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3'

defineProps<{
  status?: string | null
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password')
    },
  })
}
</script>

<template>
  <Head title="ログイン" />

  <div class="login-page">
    <div class="login-card">
      <Link href="/" class="brand-link">Ticket Manager</Link>
      <h1>ログイン</h1>

      <p v-if="status" class="status-message">
        {{ status }}
      </p>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="username"
          />
          <p v-if="form.errors.email" class="error">
            {{ form.errors.email }}
          </p>
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
          />
          <p v-if="form.errors.password" class="error">
            {{ form.errors.password }}
          </p>
        </div>

        <label class="remember-row">
          <input v-model="form.remember" type="checkbox" />
          ログイン状態を保持する
        </label>

        <button type="submit" :disabled="form.processing">
          {{ form.processing ? '送信中...' : 'ログイン' }}
        </button>
      </form>

      <p class="auth-switch">
        アカウントをお持ちでない方は
        <Link href="/register">会員登録</Link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: #f5f7fb;
}

.login-card {
  width: 100%;
  max-width: 420px;
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
  margin: 0 0 24px;
  font-size: 28px;
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

input[type="email"],
input[type="password"] {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 12px;
  border: 1px solid #cfd6e4;
  border-radius: 8px;
  font-size: 14px;
}

.remember-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 16px 0 20px;
  font-weight: 400;
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
  margin-top: 6px;
  color: #dc2626;
  font-size: 13px;
}

.status-message {
  margin-bottom: 16px;
  color: #2563eb;
}

.auth-switch {
  margin: 22px 0 0;
  color: #64748b;
  font-size: 14px;
  text-align: center;
}

.auth-switch a {
  color: #2563eb;
  font-weight: 700;
}
</style>
