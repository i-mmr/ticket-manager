<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
})

const submit = () => {
  form.post('/register')
}
</script>

<template>
  <Head title="会員登録" />

  <div class="auth-page">
    <div class="auth-card">
      <Link href="/" class="brand-link">Ticket Manager</Link>
      <h1>会員登録</h1>
      <p class="lead">まずはメールアドレスを登録してください。届いたURLを開くと、登録の仕上げに進めます。</p>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input id="email" v-model="form.email" type="email" autocomplete="username" />
          <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
        </div>

        <button type="submit" :disabled="form.processing">
          {{ form.processing ? '送信中...' : '登録用メールを送る' }}
        </button>
      </form>

      <p class="auth-switch">
        すでにアカウントをお持ちの方は
        <Link href="/login">ログイン</Link>
      </p>
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
