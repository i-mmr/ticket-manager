<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  status: String,
})

const resendForm = useForm({})
const logoutForm = useForm({})

const resend = () => {
  resendForm.post('/email/verification-notification')
}

const logout = () => {
  logoutForm.post('/logout')
}
</script>

<template>
  <Head title="メール認証" />

  <div class="verify-page">
    <div class="verify-card">
      <Link href="/" class="brand-link">Ticket Manager</Link>
      <p class="eyebrow">Email verification</p>
      <h1>確認メールを送信しました</h1>
      <p class="lead">
        登録したメールアドレス宛に届いたリンクを開くと、チケット管理画面を利用できます。
      </p>

      <p v-if="status === 'verification-link-sent'" class="status-message">
        確認メールを再送しました。メールボックスをご確認ください。
      </p>

      <div class="actions">
        <button type="button" :disabled="resendForm.processing" @click="resend">
          {{ resendForm.processing ? '送信中...' : '確認メールを再送' }}
        </button>

        <button
          type="button"
          class="secondary-button"
          :disabled="logoutForm.processing"
          @click="logout"
        >
          ログアウト
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.verify-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 28px 16px;
  background: #f5f7fb;
  color: #172554;
}

.verify-card {
  width: 100%;
  max-width: 520px;
  box-sizing: border-box;
  background: #fff;
  padding: 34px;
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

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

h1 {
  margin: 0 0 12px;
  font-size: 30px;
}

.lead {
  margin: 0 0 22px;
  color: #64748b;
  line-height: 1.8;
}

.status-message {
  margin: 0 0 18px;
  padding: 12px;
  border-radius: 8px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 14px;
  font-weight: 700;
}

.actions {
  display: grid;
  gap: 12px;
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

.secondary-button {
  background: #e2e8f0;
  color: #0f172a;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
