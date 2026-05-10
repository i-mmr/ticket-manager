<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps<{
  status?: string | null
}>()

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

  <div class="auth-page">
    <div class="auth-card auth-card--md">
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

<style scoped lang="scss">
@use '../../../scss/abstracts/variables' as v;
@use '../../../scss/abstracts/mixins' as m;

.auth-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 28px 16px;
  background: v.$color-page;
  color: v.$color-text;
}

.auth-card {
  width: 100%;
  max-width: 460px;
  box-sizing: border-box;
  padding: 32px;
  border-radius: v.$radius-md;
  background: v.$color-surface;
  box-shadow: v.$shadow-card;

  &--sm {
    max-width: 420px;
  }

  &--md {
    max-width: 520px;
    padding: 34px;
  }

  &--lg {
    max-width: 560px;
    padding: 34px;
  }

  h1 {
    margin: 0 0 10px;
    font-size: 28px;
  }

  .eyebrow {
    @include m.eyebrow;
  }

  .lead {
    margin: 0 0 22px;
    color: v.$color-muted;
    line-height: 1.7;
  }

  .form-group {
    margin-bottom: 16px;
  }

  label:not(.remember-row) {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
  }

  input:not([type='checkbox']) {
    @include m.field-control;
  }

  button {
    width: 100%;
    border: 0;
    border-radius: v.$radius-md;
    padding: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    background: v.$color-primary;
    color: #fff;

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  .secondary-button {
    background: v.$color-subtle;
    color: v.$color-text-strong;
  }

  .status-message {
    margin: 0 0 18px;
    padding: 12px;
    border-radius: v.$radius-md;
    background: v.$color-info-bg;
    color: v.$color-primary-dark;
    font-size: 14px;
    font-weight: 700;
  }

  .actions {
    display: grid;
    gap: 12px;
  }
}

.auth-card--sm h1 {
  margin-bottom: 24px;
}

.auth-card--md,
.auth-card--lg {
  h1 {
    margin-bottom: 12px;
    font-size: 30px;
  }

  .lead {
    line-height: 1.8;
  }
}

.remember-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 16px 0 20px;
  font-weight: 400;
}

.auth-switch {
  margin: 22px 0 0;
  color: v.$color-muted;
  font-size: 14px;
  text-align: center;

  a {
    color: v.$color-primary;
    font-weight: 700;
  }
}
</style>
