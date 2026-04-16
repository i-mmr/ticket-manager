<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  tickets: {
    type: Array,
    default: () => [],
  },
})

const logoutForm = useForm({})
const logout = () => {
  logoutForm.post('/logout')
}

const statusLabels = {
  open: '未対応',
  in_progress: '対応中',
  done: '完了',
}

const priorityLabels = {
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

      <button class="logout-button" @click="logout" :disabled="logoutForm.processing">
        {{ logoutForm.processing ? '送信中...' : 'ログアウト' }}
      </button>
    </header>

    <section class="ticket-section">
      <div class="section-header">
        <h2>登録済みチケット</h2>
        <span class="ticket-count">{{ tickets.length }}件</span>
      </div>

      <div v-if="tickets.length" class="ticket-list">
        <Link
          v-for="ticket in tickets"
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
    </section>
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
  max-width: 1040px;
  margin: 0 auto 24px;
  display: flex;
  justify-content: space-between;
  align-items: end;
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

.logout-button {
  border: 0;
  border-radius: 999px;
  padding: 12px 18px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  background: #0f172a;
  color: #fff;
}

.logout-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ticket-section {
  max-width: 1040px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.86);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 24px;
  padding: 24px;
  backdrop-filter: blur(10px);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.section-header h2 {
  margin: 0;
  font-size: 22px;
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

.ticket-card {
  display: block;
  padding: 20px;
  border-radius: 18px;
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
  border-radius: 18px;
  background: #f8fafc;
  color: #64748b;
}

@media (max-width: 720px) {
  .dashboard-page {
    padding: 24px 16px;
  }

  .page-header {
    align-items: start;
    flex-direction: column;
  }

  .logout-button {
    width: 100%;
  }
}
</style>
