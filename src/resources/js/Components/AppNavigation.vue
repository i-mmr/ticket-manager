<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import type { NavigationItem, NavigationKey } from '../navigation'

interface CurrentUser {
  name: string | null
  email: string | null
}

const props = defineProps<{
  currentUser: CurrentUser
  active: NavigationKey
  items: NavigationItem[]
}>()

const isMenuOpen = ref(false)
const isPasswordModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const logoutForm = useForm({})
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const deleteAccountForm = useForm({
  reason: '',
  comment: '',
})

const avatarLabel = computed(() => {
  if (!props.currentUser?.name) {
    return '?'
  }

  return props.currentUser.name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
})

const logout = () => {
  isMenuOpen.value = false
  logoutForm.post('/logout')
}

const openPasswordModal = () => {
  isMenuOpen.value = false
  isPasswordModalOpen.value = true
}

const openDeleteModal = () => {
  isMenuOpen.value = false
  isDeleteModalOpen.value = true
}

const closePasswordModal = () => {
  isPasswordModalOpen.value = false
  passwordForm.reset('current_password', 'password', 'password_confirmation')
  passwordForm.clearErrors()
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  deleteAccountForm.reset('reason', 'comment')
  deleteAccountForm.clearErrors()
}

const updatePassword = () => {
  passwordForm.patch('/account/password', {
    preserveScroll: true,
    onSuccess: () => {
      closePasswordModal()
    },
    onFinish: () => {
      passwordForm.reset('current_password', 'password', 'password_confirmation')
    },
  })
}

const deleteAccount = () => {
  deleteAccountForm.delete('/account', {
    preserveScroll: true,
  })
}
</script>

<template>
  <header class="app-header">
    <div class="brand-block">
      <p class="eyebrow">Ticket Manager</p>
      <nav class="top-nav" aria-label="上部メニュー">
        <template v-for="(item, index) in items" :key="item.key">
          <Link :href="item.href" :class="{ active: active === item.key }">
            {{ item.label }}
          </Link>
          <span v-if="index === 0" class="menu-brace">{</span>
          <span v-else-if="index < items.length - 1" class="menu-separator">|</span>
        </template>
        <span class="menu-brace">}</span>
      </nav>
    </div>

    <div class="account-shell">
      <button
        type="button"
        class="account-trigger"
        @click="isMenuOpen = !isMenuOpen"
        :aria-expanded="isMenuOpen"
        aria-haspopup="menu"
      >
        <span class="account-avatar">{{ avatarLabel }}</span>
      </button>

      <div v-if="isMenuOpen" class="account-menu" role="menu">
        <div class="account-summary">
          <div class="account-avatar large">{{ avatarLabel }}</div>
          <div>
            <p class="account-name">{{ currentUser.name }}</p>
            <p class="account-email">{{ currentUser.email }}</p>
          </div>
        </div>

        <button type="button" class="menu-item" @click="openPasswordModal">
          パスワード変更
        </button>
        <button type="button" class="menu-item danger" @click="openDeleteModal">
          退会
        </button>
        <button type="button" class="menu-item subtle" @click="logout" :disabled="logoutForm.processing">
          {{ logoutForm.processing ? '送信中...' : 'ログアウト' }}
        </button>
      </div>
    </div>
  </header>

  <div v-if="isMenuOpen" class="scrim" @click="isMenuOpen = false"></div>

  <div v-if="isPasswordModalOpen" class="modal-layer">
    <div class="modal-backdrop" @click="closePasswordModal"></div>
    <div class="modal-panel">
      <div class="modal-header">
        <div>
          <p class="modal-eyebrow">Security</p>
          <h2>パスワード変更</h2>
        </div>
        <button type="button" class="icon-button" @click="closePasswordModal">×</button>
      </div>

      <form class="modal-form" @submit.prevent="updatePassword">
        <div class="form-group">
          <label for="shared_current_password">現在のパスワード</label>
          <input
            id="shared_current_password"
            v-model="passwordForm.current_password"
            type="password"
            autocomplete="current-password"
          />
          <p v-if="passwordForm.errors.current_password" class="error">
            {{ passwordForm.errors.current_password }}
          </p>
        </div>

        <div class="form-group">
          <label for="shared_password">新しいパスワード</label>
          <input
            id="shared_password"
            v-model="passwordForm.password"
            type="password"
            autocomplete="new-password"
          />
          <p v-if="passwordForm.errors.password" class="error">
            {{ passwordForm.errors.password }}
          </p>
        </div>

        <div class="form-group">
          <label for="shared_password_confirmation">新しいパスワード確認</label>
          <input
            id="shared_password_confirmation"
            v-model="passwordForm.password_confirmation"
            type="password"
            autocomplete="new-password"
          />
        </div>

        <div class="modal-actions">
          <button type="button" class="secondary-button" @click="closePasswordModal">
            キャンセル
          </button>
          <button type="submit" class="primary-button" :disabled="passwordForm.processing">
            {{ passwordForm.processing ? '更新中...' : '変更する' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <div v-if="isDeleteModalOpen" class="modal-layer">
    <div class="modal-backdrop" @click="closeDeleteModal"></div>
    <div class="modal-panel">
      <div class="modal-header">
        <div>
          <p class="modal-eyebrow danger-text">Exit survey</p>
          <h2>退会前アンケート</h2>
        </div>
        <button type="button" class="icon-button" @click="closeDeleteModal">×</button>
      </div>

      <form class="modal-form" @submit.prevent="deleteAccount">
        <div class="form-group">
          <label for="shared_reason">退会理由</label>
          <select id="shared_reason" v-model="deleteAccountForm.reason">
            <option value="" disabled>選択してください</option>
            <option value="業務で使わなくなった">業務で使わなくなった</option>
            <option value="使い方が合わなかった">使い方が合わなかった</option>
            <option value="必要な機能が足りなかった">必要な機能が足りなかった</option>
            <option value="料金や導入コストが見合わなかった">料金や導入コストが見合わなかった</option>
            <option value="その他">その他</option>
          </select>
          <p v-if="deleteAccountForm.errors.reason" class="error">
            {{ deleteAccountForm.errors.reason }}
          </p>
        </div>

        <div class="form-group">
          <label for="shared_comment">コメント</label>
          <textarea
            id="shared_comment"
            v-model="deleteAccountForm.comment"
            rows="4"
            placeholder="差し支えない範囲で教えてください"
          ></textarea>
          <p v-if="deleteAccountForm.errors.comment" class="error">
            {{ deleteAccountForm.errors.comment }}
          </p>
        </div>

        <p class="danger-note">
          退会すると、このアカウントではログインできなくなります。
        </p>

        <div class="modal-actions">
          <button type="button" class="secondary-button" @click="closeDeleteModal">
            戻る
          </button>
          <button type="submit" class="danger-button" :disabled="deleteAccountForm.processing">
            {{ deleteAccountForm.processing ? '処理中...' : '退会する' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped lang="scss">
.app-header {
  position: relative;
  z-index: 4;
  max-width: 1180px;
  margin: 0 auto 18px;
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.top-nav {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-weight: 800;
}

.top-nav a,
.side-nav a {
  color: #334155;
  text-decoration: none;
}

.top-nav a.active,
.side-nav a.active {
  color: #1d4ed8;
}

.menu-brace,
.menu-separator {
  color: #94a3b8;
}

.account-shell {
  position: relative;
  z-index: 5;
}

.account-trigger {
  padding: 0;
  border: 0;
  background: transparent;
  cursor: pointer;
}

.account-avatar {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: #0f172a;
  color: #fff;
  font-size: 13px;
  font-weight: 800;
}

.account-avatar.large {
  width: 52px;
  height: 52px;
  font-size: 16px;
}

.account-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: min(320px, calc(100vw - 32px));
  padding: 14px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
}

.account-summary {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 6px 6px 14px;
  border-bottom: 1px solid #e2e8f0;
}

.account-name {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
}

.account-email {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.menu-item {
  width: 100%;
  min-height: 42px;
  margin-top: 10px;
  padding: 0 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
  text-align: left;
  cursor: pointer;
}

.menu-item.danger {
  color: #b91c1c;
  border-color: #fecaca;
  background: #fff5f5;
}

.side-nav {
  position: sticky;
  top: 24px;
  display: grid;
  gap: 8px;
  align-self: start;
  padding: 14px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
}

.side-nav a {
  min-height: 40px;
  display: flex;
  align-items: center;
  border-radius: 8px;
  padding: 0 12px;
  font-size: 14px;
  font-weight: 800;
}

.side-nav a.active {
  background: #eff6ff;
}

.scrim {
  position: fixed;
  inset: 0;
  z-index: 3;
}

.modal-layer {
  position: fixed;
  inset: 0;
  z-index: 20;
  display: grid;
  place-items: start center;
  overflow-y: auto;
  padding: 64px 20px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.48);
}

.modal-panel {
  position: relative;
  z-index: 1;
  width: min(520px, 100%);
  border-radius: 8px;
  padding: 24px;
  background: #fff;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.modal-header h2,
.modal-eyebrow,
.danger-note {
  margin: 0;
}

.modal-eyebrow {
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.danger-text {
  color: #dc2626;
}

.icon-button {
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #0f172a;
  cursor: pointer;
}

.modal-form {
  display: grid;
  gap: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  color: #0f172a;
  font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 11px 12px;
  background: #fff;
  color: #0f172a;
  font: inherit;
}

.error {
  margin: 6px 0 0;
  color: #dc2626;
  font-size: 13px;
  font-weight: 700;
}

.danger-note {
  border-radius: 8px;
  padding: 12px;
  background: #fff5f5;
  color: #b91c1c;
  font-size: 13px;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  justify-content: end;
  gap: 10px;
}

.primary-button,
.secondary-button,
.danger-button {
  min-height: 40px;
  border: 0;
  border-radius: 8px;
  padding: 0 16px;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
}

.primary-button {
  background: #2563eb;
  color: #fff;
}

.secondary-button {
  background: #e2e8f0;
  color: #0f172a;
}

.danger-button {
  background: #dc2626;
  color: #fff;
}

.primary-button:disabled,
.secondary-button:disabled,
.danger-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 860px) {
  .app-header {
    align-items: center;
  }

  .side-nav {
    position: static;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .side-nav a {
    justify-content: center;
  }

  .modal-actions {
    flex-direction: column;
  }

  .primary-button,
  .secondary-button,
  .danger-button {
    width: 100%;
  }
}
</style>
