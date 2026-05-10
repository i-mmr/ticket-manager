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

  <div class="auth-page">
    <div class="auth-card auth-card--sm">
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
