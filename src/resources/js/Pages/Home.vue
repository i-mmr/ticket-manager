<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '../Components/AppLayout.vue'
import PageHeader from '../Components/PageHeader.vue'
import type { TicketStatus } from '../types'

interface CurrentUser {
  name: string | null
  email: string | null
}

interface HomeTicket {
  id: number
  project_name: string | null
  title: string
  category: string | null
  status: TicketStatus
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

defineProps<{
  currentUser: CurrentUser
  status?: string | null
  workspaces: Workspace[]
  assignedTickets: HomeTicket[]
  createdTickets: HomeTicket[]
}>()

const statusLabels: Record<TicketStatus, string> = {
  open: '未対応',
  in_progress: '対応中',
  done: '完了',
}
</script>

<template>
  <Head title="ホーム" />

  <AppLayout :current-user="currentUser" active="home" :workspaces="workspaces">
    <div class="home-main">
      <section class="hero-section">
        <PageHeader title="ホーム" description="担当中のチケットと、自分が登録したチケットを確認できます。">
          <Link href="/tickets/create" class="primary-link">新規登録</Link>
        </PageHeader>
      </section>

      <section class="ticket-block">
        <div class="section-heading">
          <h2>自分が担当者のチケット</h2>
          <span>{{ assignedTickets.length }}件</span>
        </div>

        <div v-if="assignedTickets.length" class="ticket-table">
          <div class="ticket-row header-row">
            <span>プロジェクト名</span>
            <span>ID</span>
            <span>タイトル</span>
            <span>カテゴリ</span>
            <span>ステータス</span>
          </div>
          <Link
            v-for="ticket in assignedTickets"
            :key="ticket.id"
            :href="`/tickets/${ticket.id}`"
            class="ticket-row"
          >
            <span>{{ ticket.project_name || '未設定' }}</span>
            <span>#{{ ticket.id }}</span>
            <strong>{{ ticket.title }}</strong>
            <span>{{ ticket.category || '未設定' }}</span>
            <span class="status-pill">{{ statusLabels[ticket.status] ?? ticket.status }}</span>
          </Link>
        </div>

        <p v-else class="empty-state">担当者になっているチケットはありません。</p>
      </section>

      <section class="ticket-block">
        <div class="section-heading">
          <h2>自分が登録したチケット</h2>
          <span>{{ createdTickets.length }}件</span>
        </div>

        <div v-if="createdTickets.length" class="ticket-table">
          <div class="ticket-row header-row">
            <span>プロジェクト名</span>
            <span>ID</span>
            <span>タイトル</span>
            <span>カテゴリ</span>
            <span>ステータス</span>
          </div>
          <Link
            v-for="ticket in createdTickets"
            :key="ticket.id"
            :href="`/tickets/${ticket.id}`"
            class="ticket-row"
          >
            <span>{{ ticket.project_name || '未設定' }}</span>
            <span>#{{ ticket.id }}</span>
            <strong>{{ ticket.title }}</strong>
            <span>{{ ticket.category || '未設定' }}</span>
            <span class="status-pill">{{ statusLabels[ticket.status] ?? ticket.status }}</span>
          </Link>
        </div>

        <p v-else class="empty-state">自分が登録したチケットはありません。</p>
      </section>
    </div>
  </AppLayout>
</template>

<style scoped lang="scss">
.home-main {
  display: grid;
  gap: 20px;
}

.hero-section,
.ticket-block {
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
}

.hero-section {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
}

h2,
p {
  margin: 0;
}

.primary-link {
  align-self: start;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  border-radius: 8px;
  padding: 0 16px;
  background: #2563eb;
  color: #fff;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

.ticket-block {
  padding: 22px;
}

.section-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.section-heading h2 {
  font-size: 20px;
}

.section-heading span {
  border-radius: 999px;
  padding: 6px 10px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 800;
}

.ticket-table {
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

.ticket-row {
  display: grid;
  grid-template-columns: 1.3fr 80px 1.7fr 1fr 110px;
  gap: 12px;
  align-items: center;
  min-height: 48px;
  padding: 0 14px;
  border-top: 1px solid #e2e8f0;
  color: #334155;
  font-size: 14px;
  text-decoration: none;
}

.ticket-row:first-child {
  border-top: 0;
}

.ticket-row:not(.header-row):hover {
  background: #f8fafc;
}

.header-row {
  min-height: 42px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.status-pill {
  display: inline-flex;
  justify-content: center;
  border-radius: 999px;
  padding: 6px 10px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 12px;
  font-weight: 800;
}

.empty-state {
  padding: 18px;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
}

@media (max-width: 860px) {
  .hero-section {
    flex-direction: column;
  }

  .ticket-table {
    overflow-x: auto;
  }

  .ticket-row {
    min-width: 760px;
  }
}
</style>
