<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '../../Components/AppLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
import type { TicketPriority, TicketProject, TicketStatus } from '../../types'
import type { WorkspaceProject, WorkspaceTree } from '../../types/workspace'

type TicketViewMode = 'card' | 'list'

interface ChartItem {
  key: string
  label: string
  count: number
  percent: number
}

interface Ticket {
  id: number
  project: TicketProject | null
  title: string
  category: string | null
  description: string | null
  status: TicketStatus
  priority: TicketPriority
  created_at: string | null
}

interface DashboardProps {
  status?: string | null
  tickets?: Ticket[]
}

interface SharedProps extends Record<string, unknown> {
  workspaces?: WorkspaceTree[]
}

const props = withDefaults(defineProps<DashboardProps>(), {
  status: null,
  tickets: () => [],
})

const page = usePage<SharedProps>()
const workspaces = computed<WorkspaceTree[]>(() => page.props.workspaces ?? [])
const selectedProjectId = ref<number | null>(null)
const ticketViewMode = ref<TicketViewMode>('card')
const currentPage = ref(1)
const ticketsPerPage = 50

const firstProject = computed<WorkspaceProject | null>(() => {
  for (const workspace of workspaces.value) {
    if (workspace.projects.length) {
      return workspace.projects[0]
    }
  }

  return null
})

const activeProjectId = computed(() => selectedProjectId.value ?? firstProject.value?.id ?? null)

const selectedProject = computed(() => {
  for (const workspace of workspaces.value) {
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

const buildTicketDistribution = <T extends TicketStatus | TicketPriority>(
  values: readonly T[],
  labels: Record<T, string>,
  field: 'status' | 'priority',
): ChartItem[] => {
  const counts = new Map<T, number>(values.map((value) => [value, 0]))

  for (const ticket of visibleTickets.value) {
    const value = ticket[field] as T
    counts.set(value, (counts.get(value) ?? 0) + 1)
  }

  const totalCount = visibleTickets.value.length || 1

  return values.map((value) => {
    const count = counts.get(value) ?? 0

    return {
      key: value,
      label: labels[value] ?? value,
      count,
      percent: (count / totalCount) * 100,
    }
  })
}

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

const statusOrder = ['open', 'in_progress', 'done'] as const
const priorityOrder = ['high', 'medium', 'low'] as const

const statusChartItems = computed(() => buildTicketDistribution(statusOrder, statusLabels, 'status'))
const priorityChartItems = computed(() => buildTicketDistribution(priorityOrder, priorityLabels, 'priority'))
</script>

<template>
  <Head title="チケット一覧" />

  <AppLayout
    active="tickets"
    :active-project-id="activeProjectId"
    selectable-projects
    @select-project="selectProject"
  >
    <div class="dashboard-page">
      <PageHeader title="チケット一覧" description="ログイン後に現在の対応チケットを確認できます。" />

    <p v-if="status" class="status-banner">
      {{
        status === 'password-updated'
          ? 'パスワードを更新しました。'
          : status === 'ticket-deleted'
            ? 'チケットを削除しました。'
            : status
      }}
    </p>

      <section class="ticket-section">
        <div class="section-header">
          <div>
            <h2>{{ selectedProject?.name ?? 'チケット一覧' }}</h2>
            <p class="section-subtitle">
              {{ currentRangeStart }}-{{ currentRangeEnd }}件目 / {{ visibleTickets.length }}件
            </p>
          </div>

          <div class="ticket-toolbar">
            <Link href="/tickets/create" class="new-ticket-button">
              新規登録
            </Link>
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

        <div v-if="visibleTickets.length" class="ticket-insights" :key="activeProjectId ?? 'all'">
          <article class="insight-panel">
            <div class="insight-header">
              <h3>対応状況別</h3>
              <span>{{ visibleTickets.length }}件</span>
            </div>
            <div class="stacked-chart" aria-label="対応状況ごとのチケット件数">
              <div class="stacked-track">
                <div class="stacked-fill">
                  <span
                    v-for="item in statusChartItems"
                    :key="item.key"
                    class="stacked-segment"
                    :class="`is-${item.key}`"
                    :style="{ width: `${item.percent}%` }"
                    :title="`${item.label}: ${item.count}件`"
                  ></span>
                </div>
              </div>
              <div class="chart-legend">
                <div v-for="item in statusChartItems" :key="item.key" class="legend-item">
                  <span class="legend-dot" :class="`is-${item.key}`"></span>
                  <span>{{ item.label }}</span>
                  <strong>{{ item.count }}件</strong>
                </div>
              </div>
            </div>
          </article>

          <article class="insight-panel">
            <div class="insight-header">
              <h3>優先度別</h3>
              <span>{{ visibleTickets.length }}件</span>
            </div>
            <div class="stacked-chart" aria-label="優先度ごとのチケット件数">
              <div class="stacked-track">
                <div class="stacked-fill">
                  <span
                    v-for="item in priorityChartItems"
                    :key="item.key"
                    class="stacked-segment"
                    :class="`is-${item.key}`"
                    :style="{ width: `${item.percent}%` }"
                    :title="`${item.label}: ${item.count}件`"
                  ></span>
                </div>
              </div>
              <div class="chart-legend">
                <div v-for="item in priorityChartItems" :key="item.key" class="legend-item">
                  <span class="legend-dot" :class="`is-${item.key}`"></span>
                  <span>{{ item.label }}</span>
                  <strong>{{ item.count }}件</strong>
                </div>
              </div>
            </div>
          </article>
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
            <p class="ticket-category">カテゴリ: {{ ticket.category || '未設定' }}</p>
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
  </AppLayout>
</template>

<style scoped lang="scss">
@use '../../../scss/abstracts/variables' as v;
@use '../../../scss/abstracts/mixins' as m;

.dashboard-page {
  color: #172554;
}

.dashboard-page .eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2563eb;
}

.dashboard-page .account-shell {
  position: relative;
  z-index: 3;
}

.dashboard-page .account-trigger {
  padding: 0;
  border: 0;
  background: transparent;
  cursor: pointer;
}

.dashboard-page .account-avatar {
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

.dashboard-page .account-avatar.large {
  width: 52px;
  height: 52px;
  font-size: 16px;
}

.dashboard-page .account-menu {
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

.dashboard-page .account-summary {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 6px 6px 14px;
  border-bottom: 1px solid #e2e8f0;
}

.dashboard-page .account-name {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
}

.dashboard-page .account-email {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.dashboard-page .menu-item {
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

.dashboard-page .menu-item.danger {
  color: #b91c1c;
  border-color: #fecaca;
  background: #fff5f5;
}

.dashboard-page .menu-item.subtle {
  color: #475569;
}

.dashboard-page .menu-item:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.dashboard-page .status-banner {
  margin: 0 auto 18px;
  padding: 12px 14px;
  border-radius: 8px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 14px;
  font-weight: 700;
}

.dashboard-page .dashboard-layout {
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  gap: 20px;
  align-items: start;
}

.dashboard-page .left-pane {
  position: sticky;
  top: 24px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  padding: 18px;
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
}

.dashboard-page .workspace-list {
  display: grid;
  gap: 16px;
}

.dashboard-page .workspace-group {
  padding-top: 2px;
}

.dashboard-page .workspace-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dashboard-page .workspace-icon {
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

.dashboard-page .workspace-row h3 {
  margin: 0;
  font-size: 16px;
}

.dashboard-page .workspace-row p,
.dashboard-page .tree-item p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
}

.dashboard-page .tree-block {
  margin-top: 16px;
}

.dashboard-page .tree-label {
  margin: 0 0 8px;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.dashboard-page .tree-item {
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

.dashboard-page .project-item {
  border-radius: 8px;
  cursor: pointer;
}

.dashboard-page .project-item:hover,
.dashboard-page .project-item.active {
  border-left-color: #2563eb;
  background: #eff6ff;
}

.dashboard-page .tree-item strong {
  font-size: 14px;
}

.dashboard-page .user-list {
  display: grid;
  gap: 4px;
  margin: 8px 0 0;
  padding: 0;
  list-style: none;
}

.dashboard-page .user-list li {
  overflow: hidden;
  color: #334155;
  font-size: 12px;
  line-height: 1.5;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dashboard-page .tree-dot {
  width: 9px;
  height: 9px;
  margin-top: 5px;
  border-radius: 999px;
}

.dashboard-page .team-dot {
  background: #14b8a6;
}

.dashboard-page .project-dot {
  background: #2563eb;
}

.dashboard-page .pane-empty {
  padding: 20px 0 4px;
  color: #64748b;
  font-size: 14px;
}

.dashboard-page .ticket-section {
  background: rgba(255, 255, 255, 0.86);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  padding: 24px;
  backdrop-filter: blur(10px);
}

.dashboard-page .section-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 20px;
}

.dashboard-page .section-header h2 {
  margin: 0;
  font-size: 22px;
}

.dashboard-page .section-subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.dashboard-page .ticket-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.dashboard-page .new-ticket-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 38px;
  border-radius: 8px;
  padding: 0 14px;
  background: #2563eb;
  color: #fff;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
}

.dashboard-page .view-toggle {
  display: inline-flex;
  min-height: 38px;
  padding: 3px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #fff;
}

.dashboard-page .view-toggle button {
  border: 0;
  border-radius: 6px;
  padding: 0 12px;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
}

.dashboard-page .view-toggle button.active {
  background: #2563eb;
  color: #fff;
}

.dashboard-page .ticket-count {
  border-radius: 999px;
  padding: 8px 12px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 700;
}

.dashboard-page .ticket-insights {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.dashboard-page .insight-panel {
  padding: 18px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.dashboard-page .insight-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.dashboard-page .insight-header h3 {
  margin: 0;
  color: #0f172a;
  font-size: 16px;
}

.dashboard-page .insight-header span {
  flex: 0 0 auto;
  border-radius: 999px;
  padding: 5px 9px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.dashboard-page .stacked-chart {
  display: grid;
  gap: 14px;
}

.dashboard-page .stacked-track {
  overflow: hidden;
  height: 18px;
  border-radius: 999px;
  background: #e2e8f0;
}

.dashboard-page .stacked-fill {
  display: flex;
  height: 100%;
  transform-origin: left center;
  animation: grow-bar 0.72s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.dashboard-page .stacked-segment {
  min-width: 0;
  height: 100%;
}

.dashboard-page .chart-legend {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.dashboard-page .legend-item {
  display: flex;
  align-items: center;
  min-width: 0;
  gap: 7px;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
}

.dashboard-page .legend-item strong {
  flex: 0 0 auto;
  margin-left: auto;
  color: #0f172a;
}

.dashboard-page .legend-dot {
  flex: 0 0 auto;
  width: 9px;
  height: 9px;
  border-radius: 999px;
}

.dashboard-page .stacked-segment.is-open,
.dashboard-page .legend-dot.is-open {
  background: #3b82f6;
}

.dashboard-page .stacked-segment.is-in_progress,
.dashboard-page .legend-dot.is-in_progress {
  background: #14b8a6;
}

.dashboard-page .stacked-segment.is-done,
.dashboard-page .legend-dot.is-done {
  background: #64748b;
}

.dashboard-page .stacked-segment.is-high,
.dashboard-page .legend-dot.is-high {
  background: #ef4444;
}

.dashboard-page .stacked-segment.is-medium,
.dashboard-page .legend-dot.is-medium {
  background: #f59e0b;
}

.dashboard-page .stacked-segment.is-low,
.dashboard-page .legend-dot.is-low {
  background: #22c55e;
}

.dashboard-page .ticket-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.dashboard-page .ticket-list.is-list {
  grid-template-columns: 1fr;
}

.dashboard-page .ticket-card {
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

.dashboard-page .ticket-list.is-list .ticket-card {
  display: grid;
  grid-template-columns: 180px minmax(0, 1fr) 140px;
  gap: 16px;
  align-items: center;
  padding: 16px 18px;
}

.dashboard-page .ticket-list.is-list .ticket-meta {
  margin-bottom: 0;
  justify-content: start;
}

.dashboard-page .ticket-list.is-list .ticket-card h3 {
  margin-bottom: 4px;
}

.dashboard-page .ticket-list.is-list .created-at {
  margin-top: 0;
  text-align: right;
}

.dashboard-page .ticket-card:hover {
  transform: translateY(-4px);
  border-color: #93c5fd;
  box-shadow: 0 18px 36px rgba(37, 99, 235, 0.14);
}

.dashboard-page .ticket-meta {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
  font-size: 12px;
  font-weight: 700;
}

.dashboard-page .status {
  color: #2563eb;
}

.dashboard-page .priority {
  color: #475569;
}

.dashboard-page .ticket-card h3 {
  margin: 0 0 12px;
  font-size: 20px;
}

.dashboard-page .ticket-description,
.dashboard-page .ticket-category,
.dashboard-page .created-at {
  margin: 0;
  color: #475569;
  line-height: 1.6;
}

.dashboard-page .created-at {
  margin-top: 14px;
  font-size: 13px;
}

.dashboard-page .empty-state {
  padding: 40px 20px;
  text-align: center;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
}

.dashboard-page .pagination {
  display: flex;
  justify-content: end;
  align-items: center;
  gap: 12px;
  margin-top: 20px;
}

.dashboard-page .pagination button {
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

.dashboard-page .pagination button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.dashboard-page .pagination span {
  color: #475569;
  font-size: 13px;
  font-weight: 800;
}

@keyframes grow-bar {
  from {
    transform: scaleX(0);
  }

  to {
    transform: scaleX(1);
  }
}

@media (prefers-reduced-motion: reduce) {
.dashboard-page .stacked-fill {
    animation: none;
  }
}

.dashboard-page .scrim,
.dashboard-page .modal-layer {
  position: fixed;
  inset: 0;
}

.dashboard-page .scrim {
  z-index: 2;
}

.dashboard-page .modal-layer {
  z-index: 20;
}

.dashboard-page .modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
}

.dashboard-page .modal-panel {
  position: relative;
  width: min(520px, calc(100vw - 32px));
  margin: 80px auto 0;
  padding: 24px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
}

.dashboard-page .modal-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 18px;
}

.dashboard-page .modal-eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.dashboard-page .danger-text {
  color: #dc2626;
}

.dashboard-page .modal-header h2 {
  margin: 0;
  font-size: 24px;
}

.dashboard-page .icon-button {
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

.dashboard-page .modal-form {
  display: grid;
  gap: 16px;
}

.dashboard-page .form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 700;
}

.dashboard-page .form-group input,
.dashboard-page .form-group select,
.dashboard-page .form-group textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 12px;
  border: 1px solid #cfd6e4;
  border-radius: 8px;
  font-size: 14px;
}

.dashboard-page .form-group textarea {
  resize: vertical;
}

.dashboard-page .error {
  margin: 6px 0 0;
  color: #dc2626;
  font-size: 13px;
}

.dashboard-page .danger-note {
  margin: 0;
  color: #b91c1c;
  font-size: 14px;
  line-height: 1.7;
}

.dashboard-page .modal-actions {
  display: flex;
  justify-content: end;
  gap: 12px;
}

.dashboard-page .primary-button,
.dashboard-page .secondary-button,
.dashboard-page .danger-button {
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

.dashboard-page .primary-button {
  background: #2563eb;
  color: #fff;
}

.dashboard-page .secondary-button {
  background: #e2e8f0;
  color: #0f172a;
}

.dashboard-page .danger-button {
  background: #dc2626;
  color: #fff;
}

.dashboard-page .primary-button:disabled,
.dashboard-page .secondary-button:disabled,
.dashboard-page .danger-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 720px) {
  .dashboard-page .section-header,
.dashboard-page .ticket-toolbar {
    align-items: stretch;
    flex-direction: column;
  }

.dashboard-page .ticket-insights {
    grid-template-columns: 1fr;
  }

.dashboard-page .chart-legend {
    grid-template-columns: 1fr;
  }

  .dashboard-page .dashboard-layout {
    grid-template-columns: 1fr;
  }

.dashboard-page .left-pane {
    position: static;
  }

.dashboard-page .modal-panel {
    margin-top: 36px;
    padding: 20px;
  }

.dashboard-page .ticket-list.is-list .ticket-card {
    grid-template-columns: 1fr;
  }

.dashboard-page .ticket-list.is-list .created-at {
    text-align: left;
  }

.dashboard-page .modal-actions {
    flex-direction: column;
  }

  .dashboard-page .primary-button,
  .dashboard-page .secondary-button,
.dashboard-page .danger-button {
    width: 100%;
  }
}
</style>
