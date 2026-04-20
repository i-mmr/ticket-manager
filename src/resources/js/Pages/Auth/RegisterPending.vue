<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  email: String,
  status: String,
})
</script>

<template>
  <Head title="メールを確認" />

  <div class="pending-page">
    <div class="pending-card">
      <Link href="/" class="brand-link">Ticket Manager</Link>
      <p class="eyebrow">Check your email</p>
      <h1>会員登録用のURLを送信しました</h1>
      <p class="lead">
        <strong>{{ email }}</strong>
        宛に登録用メールを送りました。メールにあるURLを開くと、名前とパスワードの設定画面が表示されます。
      </p>

      <p v-if="status === 'registration-link-sent'" class="status-message">
        送信が完了しました。受信トレイをご確認ください。
      </p>

      <div class="actions">
        <Link href="/register" class="secondary-link">メールアドレスを入力し直す</Link>
        <Link href="/login" class="primary-link">ログインへ</Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pending-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 28px 16px;
  background: #f5f7fb;
  color: #172554;
}

.pending-card {
  width: 100%;
  max-width: 560px;
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
  margin: 0 0 20px;
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

.primary-link,
.secondary-link {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  min-height: 44px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
}

.primary-link {
  background: #2563eb;
  color: #fff;
}

.secondary-link {
  border: 1px solid #cbd5e1;
  color: #0f172a;
  background: #fff;
}
</style>
