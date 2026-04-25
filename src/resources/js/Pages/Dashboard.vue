<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import type { TicketPriority, TicketProject, TicketStatus } from '../types'

type TicketViewMode = 'card' | 'list'

interface CurrentUser {
  name: string | null
  email: string | null
}

interface WorkspaceUser {
  id: number
  name: string
  email: string
}

interface Team {
  id: number
  name: string
  users: WorkspaceUser[]
}

interface Project {
  id: number
  name: string
  status: string
  tickets_count: number
}

interface Workspace {
  id: number
  name: string
  teams: Team[]
  projects: Project[]
}

interface Ticket {
  id: number
  project: TicketProject | null
  title: string
  description: string | null
  status: TicketStatus
  priority: TicketPriority
  created_at: string | null
}

interface DashboardProps {
  currentUser: CurrentUser
  status?: string | null
  workspaces?: Workspace[]
  tickets?: Ticket[]
}

const props = withDefaults(defineProps<DashboardProps>(), {
  status: null,
  workspaces: () => [],
  tickets: () => [],
})

const isMenuOpen = ref(false)
const isPasswordModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedProjectId = ref<number | null>(null)
const ticketViewMode = ref<TicketViewMode>('card')
const currentPage = ref(1)
const ticketsPerPage = 50

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

const firstProject = computed(() => {
  for (const workspace of props.workspaces) {
    if (workspace.projects.length) {
      return workspace.projects[0]
    }
  }

  return null
})

const activeProjectId = computed(() => selectedProjectId.value ?? firstProject.value?.id ?? null)

const selectedProject = computed(() => {
  for (const workspace of props.workspaces) {
    const project = workspace.projects.find((item) => item.id === activeProjectId.value)

    if (project) {
      return project
    }
  }

  return null
})

const visibleTickets = computed(() => {
  if (!activeProjectId.value) {
    return props.tickets
  }

  return props.tickets.filter((ticket) => ticket.project?.id === activeProjectId.value)
})

const totalPages = computed(() => Math.max(1, Math.ceil(visibleTickets.value.length / ticketsPerPage)))

const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * ticketsPerPage

  return visibleTickets.value.slice(start, start + ticketsPerPage)
})

const currentRangeStart = computed(() => {
  if (!visibleTickets.value.length) {
    return 0
  }

  return (currentPage.value - 1) * ticketsPerPage + 1
})

const currentRangeEnd = computed(() => Math.min(currentPage.value * ticketsPerPage, visibleTickets.value.length))

const selectProject = (projectId: number) => {
  selectedProjectId.value = projectId
  currentPage.value = 1
}

const setTicketViewMode = (mode: TicketViewMode) => {
  ticketViewMode.value = mode
}

const goToPage = (page: number) => {
  currentPage.value = Math.min(Math.max(page, 1), totalPages.value)
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

const logout = () => {
  isMenuOpen.value = false
  logoutForm.post('/logout')
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

const statusLabels: Record<TicketStatus, string> = {
  open: '未対応',
  in_progress: '対応中',
  done: '完了',
}

const priorityLabels: Record<TicketPriority, string> = {
  high: '高',
  medium: '中',
  low: '低',
}
</script>

<template>
  <Head title="チケット一覧" />

  <div class="dashboard-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Ticket Manager</p>
        <h1>チケット一覧</h1>
        <p class="description">
          ログイン後に現在の対応チケットを確認できます。
        </p>
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

    <p v-if="status === 'password-updated'" class="status-banner">
      パスワードを更新しました。
    </p>

    <div class="dashboard-layout">
      <aside class="left-pane" aria-label="ワークスペースナビゲーション">
        <div v-if="workspaces.length" class="workspace-list">
          <section v-for="workspace in workspaces" :key="workspace.id" class="workspace-group">
            <div class="workspace-row">
              <span class="workspace-icon">W</span>
              <div>
                <h3>{{ workspace.name }}</h3>
                <p>{{ workspace.projects.length }}プロジェクト / {{ workspace.teams.length }}チーム</p>
              </div>
            </div>

            <div class="tree-block">
              <p class="tree-label">チーム</p>
              <div v-for="team in workspace.teams" :key="team.id" class="tree-item">
                <span class="tree-dot team-dot"></span>
                <div>
                  <strong>{{ team.name }}</strong>
                  <p>{{ team.users.length }}ユーザー</p>
                  <ul v-if="team.users.length" class="user-list">
                    <li v-for="user in team.users" :key="user.id">
                      {{ user.name }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="tree-block">
              <p class="tree-label">プロジェクト</p>
              <button
                v-for="project in workspace.projects"
                :key="project.id"
                type="button"
                class="tree-item project-item"
                :class="{ active: project.id === activeProjectId }"
                @click="selectProject(project.id)"
              >
                <span class="tree-dot project-dot"></span>
                <div>
                  <strong>{{ project.name }}</strong>
                  <p>{{ project.tickets_count }}チケット</p>
                </div>
              </button>
            </div>
          </section>
        </div>

        <div v-else class="pane-empty">
          ワークスペースはまだありません。
        </div>
      </aside>

      <section class="ticket-section">
        <div class="section-header">
          <div>
            <h2>{{ selectedProject?.name ?? 'チケット一覧' }}</h2>
            <p class="section-subtitle">
              {{ currentRangeStart }}-{{ currentRangeEnd }}件目 / {{ visibleTickets.length }}件
            </p>
          </div>

          <div class="ticket-toolbar">
            <div class="view-toggle" aria-label="表示形式">
              <button
                type="button"
                :class="{ active: ticketViewMode === 'list' }"
                @click="setTicketViewMode('list')"
              >
                リスト
              </button>
              <button
                type="button"
                :class="{ active: ticketViewMode === 'card' }"
                @click="setTicketViewMode('card')"
              >
                カード
              </button>
            </div>
            <span class="ticket-count">{{ visibleTickets.length }}件</span>
          </div>
        </div>

        <div v-if="visibleTickets.length" class="ticket-list" :class="`is-${ticketViewMode}`">
          <Link
            v-for="ticket in paginatedTickets"
            :key="ticket.id"
            :href="`/tickets/${ticket.id}`"
            class="ticket-card"
          >
            <div class="ticket-meta">
              <span class="status">{{ statusLabels[ticket.status] ?? ticket.status }}</span>
              <span class="priority">優先度: {{ priorityLabels[ticket.priority] ?? ticket.priority }}</span>
            </div>

            <h3>{{ ticket.title }}</h3>
            <p class="ticket-description">
              {{ ticket.description || '説明は未登録です。' }}
            </p>
            <p class="created-at">作成日時: {{ ticket.created_at }}</p>
          </Link>
        </div>

        <div v-else class="empty-state">
          チケットはまだ登録されていません。
        </div>

        <div v-if="visibleTickets.length > ticketsPerPage" class="pagination">
          <button type="button" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
            前へ
          </button>
          <span>{{ currentPage }} / {{ totalPages }}</span>
          <button type="button" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
            次へ
          </button>
        </div>
      </section>
    </div>

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
            <label for="current_password">現在のパスワード</label>
            <input
              id="current_password"
              v-model="passwordForm.current_password"
              type="password"
              autocomplete="current-password"
            />
            <p v-if="passwordForm.errors.current_password" class="error">
              {{ passwordForm.errors.current_password }}
            </p>
          </div>

          <div class="form-group">
            <label for="password">新しいパスワード</label>
            <input
              id="password"
              v-model="passwordForm.password"
              type="password"
              autocomplete="new-password"
            />
            <p v-if="passwordForm.errors.password" class="error">
              {{ passwordForm.errors.password }}
            </p>
          </div>

          <div class="form-group">
            <label for="password_confirmation">新しいパスワード確認</label>
            <input
              id="password_confirmation"
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
            <label for="reason">退会理由</label>
            <select id="reason" v-model="deleteAccountForm.reason">
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
            <label for="comment">コメント</label>
            <textarea
              id="comment"
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
  </div>
</template>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  padding: 40px 24px;
  background:
    radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 32%),
    linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
  color: #172554;
}

.page-header {
  position: relative;
  max-width: 1040px;
  margin: 0 auto 24px;
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2563eb;
}

h1 {
  margin: 0;
  font-size: clamp(32px, 5vw, 48px);
}

.description {
  margin: 12px 0 0;
  color: #475569;
}

.account-shell {
  position: relative;
  z-index: 3;
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
  backdrop-filter: blur(12px);
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
  color: #0f172a;
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

.menu-item.subtle {
  color: #475569;
}

.menu-item:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.status-banner {
  max-width: 1040px;
  margin: 0 auto 18px;
  padding: 12px 14px;
  border-radius: 8px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 14px;
  font-weight: 700;
}

.dashboard-layout {
  max-width: 1180px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  gap: 20px;
  align-items: start;
}

.left-pane {
  position: sticky;
  top: 24px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  padding: 18px;
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
}

.workspace-list {
  display: grid;
  gap: 16px;
}

.workspace-group {
  padding-top: 2px;
}

.workspace-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.workspace-icon {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #0f172a;
  color: #fff;
  font-size: 13px;
  font-weight: 900;
}

.workspace-row h3 {
  margin: 0;
  font-size: 16px;
}

.workspace-row p,
.tree-item p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
}

.tree-block {
  margin-top: 16px;
}

.tree-label {
  margin: 0 0 8px;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.tree-item {
  display: grid;
  grid-template-columns: 14px 1fr;
  gap: 8px;
  width: 100%;
  padding: 8px 0 8px 12px;
  border: 0;
  border-left: 1px solid #e2e8f0;
  background: transparent;
  color: inherit;
  text-align: left;
}

.project-item {
  border-radius: 8px;
  cursor: pointer;
}

.project-item:hover,
.project-item.active {
  border-left-color: #2563eb;
  background: #eff6ff;
}

.tree-item strong {
  font-size: 14px;
}

.user-list {
  display: grid;
  gap: 4px;
  margin: 8px 0 0;
  padding: 0;
  list-style: none;
}

.user-list li {
  overflow: hidden;
  color: #334155;
  font-size: 12px;
  line-height: 1.5;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tree-dot {
  width: 9px;
  height: 9px;
  margin-top: 5px;
  border-radius: 999px;
}

.team-dot {
  background: #14b8a6;
}

.project-dot {
  background: #2563eb;
}

.pane-empty {
  padding: 20px 0 4px;
  color: #64748b;
  font-size: 14px;
}

.ticket-section {
  background: rgba(255, 255, 255, 0.86);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  padding: 24px;
  backdrop-filter: blur(10px);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 20px;
}

.section-header h2 {
  margin: 0;
  font-size: 22px;
}

.section-subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.ticket-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.view-toggle {
  display: inline-flex;
  min-height: 38px;
  padding: 3px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #fff;
}

.view-toggle button {
  border: 0;
  border-radius: 6px;
  padding: 0 12px;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
}

.view-toggle button.active {
  background: #2563eb;
  color: #fff;
}

.ticket-count {
  border-radius: 999px;
  padding: 8px 12px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 700;
}

.ticket-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.ticket-list.is-list {
  grid-template-columns: 1fr;
}

.ticket-card {
  display: block;
  padding: 20px;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #dbeafe;
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
  color: inherit;
  text-decoration: none;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    border-color 0.18s ease;
}

.ticket-list.is-list .ticket-card {
  display: grid;
  grid-template-columns: 180px minmax(0, 1fr) 140px;
  gap: 16px;
  align-items: center;
  padding: 16px 18px;
}

.ticket-list.is-list .ticket-meta {
  margin-bottom: 0;
  justify-content: start;
}

.ticket-list.is-list .ticket-card h3 {
  margin-bottom: 4px;
}

.ticket-list.is-list .created-at {
  margin-top: 0;
  text-align: right;
}

.ticket-card:hover {
  transform: translateY(-4px);
  border-color: #93c5fd;
  box-shadow: 0 18px 36px rgba(37, 99, 235, 0.14);
}

.ticket-meta {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
  font-size: 12px;
  font-weight: 700;
}

.status {
  color: #2563eb;
}

.priority {
  color: #475569;
}

.ticket-card h3 {
  margin: 0 0 12px;
  font-size: 20px;
}

.ticket-description,
.created-at {
  margin: 0;
  color: #475569;
  line-height: 1.6;
}

.created-at {
  margin-top: 14px;
  font-size: 13px;
}

.empty-state {
  padding: 40px 20px;
  text-align: center;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
}

.pagination {
  display: flex;
  justify-content: end;
  align-items: center;
  gap: 12px;
  margin-top: 20px;
}

.pagination button {
  min-width: 76px;
  min-height: 38px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #fff;
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.pagination span {
  color: #475569;
  font-size: 13px;
  font-weight: 800;
}

.scrim,
.modal-layer {
  position: fixed;
  inset: 0;
}

.scrim {
  z-index: 2;
}

.modal-layer {
  z-index: 20;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
}

.modal-panel {
  position: relative;
  width: min(520px, calc(100vw - 32px));
  margin: 80px auto 0;
  padding: 24px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 18px;
}

.modal-eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.danger-text {
  color: #dc2626;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
}

.icon-button {
  width: 36px;
  height: 36px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #fff;
  color: #0f172a;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
}

.modal-form {
  display: grid;
  gap: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 700;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 12px;
  border: 1px solid #cfd6e4;
  border-radius: 8px;
  font-size: 14px;
}

.form-group textarea {
  resize: vertical;
}

.error {
  margin: 6px 0 0;
  color: #dc2626;
  font-size: 13px;
}

.danger-note {
  margin: 0;
  color: #b91c1c;
  font-size: 14px;
  line-height: 1.7;
}

.modal-actions {
  display: flex;
  justify-content: end;
  gap: 12px;
}

.primary-button,
.secondary-button,
.danger-button {
  min-width: 112px;
  min-height: 42px;
  box-sizing: border-box;
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

@media (max-width: 720px) {
  .dashboard-page {
    padding: 24px 16px;
  }

  .page-header {
    align-items: start;
  }

  .description {
    max-width: 240px;
  }

  .section-header,
  .ticket-toolbar {
    align-items: stretch;
    flex-direction: column;
  }

  .dashboard-layout {
    grid-template-columns: 1fr;
  }

  .left-pane {
    position: static;
  }

  .modal-panel {
    margin-top: 36px;
    padding: 20px;
  }

  .ticket-list.is-list .ticket-card {
    grid-template-columns: 1fr;
  }

  .ticket-list.is-list .created-at {
    text-align: left;
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
