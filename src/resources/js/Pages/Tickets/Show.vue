<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { renderTicketDescription } from '../../utils/ticketDescription'
import type { TicketPriority, TicketProject, TicketStatus } from '../../types'

interface TicketComment {
  id: number
  body: string
  user: string | null
  created_at: string | null
}

interface TicketAttachment {
  id: number
  file_name: string
  mime_type: string | null
  size_bytes: number | null
}

interface TicketActivity {
  id: number
  action: string
  description: string | null
  created_at: string | null
}

interface TicketSubtask {
  id: number
  title: string
  is_done: boolean
}

interface RelatedTicket {
  id: number
  title: string
}

interface TicketDetail {
  id: number
  project: TicketProject | null
  title: string
  category: string | null
  description: string | null
  status: TicketStatus
  priority: TicketPriority
  assignee: string | null
  creator: string | null
  comments: TicketComment[]
  attachments: TicketAttachment[]
  activities: TicketActivity[]
  subtasks: TicketSubtask[]
  related_tickets: RelatedTicket[]
  created_at: string | null
  updated_at: string | null
}

const props = defineProps<{
  status?: string | null
  ticket: TicketDetail
}>()

const deleteForm = useForm({
  _method: 'delete',
})

const renderedDescription = computed(() => renderTicketDescription(props.ticket.description))

const deleteTicket = () => {
  if (!window.confirm('このチケットを削除しますか？')) {
    return
  }

  deleteForm.post(`/tickets/${props.ticket.id}`)
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
  <Head :title="ticket.title" />

  <div class="ticket-show-page">
    <div class="ticket-show-shell">
      <div class="top-actions">
        <Link href="/tickets" class="back-link">
          一覧へ戻る
        </Link>
        <div class="ticket-actions">
          <Link :href="`/tickets/${ticket.id}/edit`" class="edit-link">
            修正
          </Link>
          <button type="button" class="delete-button" :disabled="deleteForm.processing" @click="deleteTicket">
            {{ deleteForm.processing ? '削除中...' : '削除' }}
          </button>
        </div>
      </div>

      <p v-if="status === 'ticket-created'" class="status-banner">
        チケットを登録しました。
      </p>
      <p v-if="status === 'ticket-updated'" class="status-banner">
        チケットを更新しました。
      </p>

      <section class="ticket-panel">
        <div class="ticket-header">
          <div>
            <p class="eyebrow">Ticket Detail</p>
            <h1>{{ ticket.title }}</h1>
          </div>

          <div class="ticket-badges">
            <span class="status">{{ statusLabels[ticket.status] ?? ticket.status }}</span>
            <span class="priority">優先度: {{ priorityLabels[ticket.priority] ?? ticket.priority }}</span>
          </div>
        </div>

        <dl class="ticket-detail-grid">
          <div v-if="ticket.project" class="detail-card">
            <dt>プロジェクト</dt>
            <dd>{{ ticket.project.workspace }} / {{ ticket.project.name }}</dd>
          </div>

          <div class="detail-card">
            <dt>説明</dt>
            <dd v-if="renderedDescription" class="description-body" v-html="renderedDescription"></dd>
            <dd v-else>説明は未登録です。</dd>
          </div>

          <div class="detail-card">
            <dt>カテゴリ</dt>
            <dd>{{ ticket.category || '未設定' }}</dd>
          </div>

          <div class="detail-card">
            <dt>担当者</dt>
            <dd>{{ ticket.assignee || '未設定' }}</dd>
          </div>

          <div class="detail-card">
            <dt>登録者</dt>
            <dd>{{ ticket.creator || '未設定' }}</dd>
          </div>

          <div class="detail-card">
            <dt>作成日時</dt>
            <dd>{{ ticket.created_at }}</dd>
          </div>

          <div class="detail-card">
            <dt>更新日時</dt>
            <dd>{{ ticket.updated_at }}</dd>
          </div>

          <div class="detail-card">
            <dt>ID</dt>
            <dd>#{{ ticket.id }}</dd>
          </div>
        </dl>

        <section class="ticket-related-section">
          <article>
            <h2>コメント</h2>
            <p v-if="!ticket.comments.length" class="muted">コメントはまだありません。</p>
            <div v-for="comment in ticket.comments" :key="comment.id" class="related-row">
              <strong>{{ comment.user || 'Unknown' }}</strong>
              <p>{{ comment.body }}</p>
            </div>
          </article>

          <article>
            <h2>サブタスク</h2>
            <p v-if="!ticket.subtasks.length" class="muted">サブタスクはまだありません。</p>
            <div v-for="subtask in ticket.subtasks" :key="subtask.id" class="related-row">
              <strong>{{ subtask.is_done ? '完了' : '未完了' }}</strong>
              <p>{{ subtask.title }}</p>
            </div>
          </article>

          <article>
            <h2>添付ファイル</h2>
            <p v-if="!ticket.attachments.length" class="muted">添付ファイルはまだありません。</p>
            <div v-for="attachment in ticket.attachments" :key="attachment.id" class="related-row">
              <strong>{{ attachment.file_name }}</strong>
              <p>{{ attachment.mime_type || '形式未登録' }}</p>
            </div>
          </article>

          <article>
            <h2>活動履歴</h2>
            <p v-if="!ticket.activities.length" class="muted">活動履歴はまだありません。</p>
            <div v-for="activity in ticket.activities" :key="activity.id" class="related-row">
              <strong>{{ activity.action }}</strong>
              <p>{{ activity.description || activity.created_at }}</p>
            </div>
          </article>

          <article>
            <h2>関連チケット</h2>
            <p v-if="!ticket.related_tickets.length" class="muted">関連チケットはまだありません。</p>
            <Link
              v-for="relatedTicket in ticket.related_tickets"
              :key="relatedTicket.id"
              :href="`/tickets/${relatedTicket.id}`"
              class="related-ticket-link"
            >
              #{{ relatedTicket.id }} {{ relatedTicket.title }}
            </Link>
          </article>
        </section>
      </section>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use '../../../scss/abstracts/variables' as v;
@use '../../../scss/abstracts/mixins' as m;

.ticket-show-page {
  min-height: 100vh;
  padding: 40px 24px;
  background:
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 34%),
    linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
  color: #172554;
}

.ticket-show-page .ticket-show-shell {
  max-width: 960px;
  margin: 0 auto;
}

.ticket-show-page .top-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
}

.ticket-show-page .back-link {
  display: inline-flex;
  align-items: center;
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.ticket-show-page .ticket-actions {
  display: flex;
  gap: 10px;
}

.ticket-show-page .edit-link,
.ticket-show-page .delete-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 76px;
  min-height: 38px;
  box-sizing: border-box;
  border-radius: 8px;
  padding: 0 14px;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
  cursor: pointer;
}

.ticket-show-page .edit-link {
  border: 0;
  background: #2563eb;
  color: #fff;
}

.ticket-show-page .delete-button {
  border: 1px solid #fecaca;
  background: #fff5f5;
  color: #b91c1c;
}

.ticket-show-page .delete-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ticket-show-page .status-banner {
  margin: 0 0 18px;
  padding: 12px 14px;
  border-radius: 8px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 14px;
  font-weight: 700;
}

.ticket-show-page .ticket-panel {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 28px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
  backdrop-filter: blur(12px);
}

.ticket-show-page .ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 16px;
  margin-bottom: 24px;
}

.ticket-show-page .eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #2563eb;
}

.ticket-show-page h1 {
  margin: 0;
  font-size: clamp(30px, 5vw, 44px);
}

.ticket-show-page .ticket-badges {
  display: flex;
  flex-wrap: wrap;
  justify-content: end;
  gap: 10px;
}

.ticket-show-page .status,
.ticket-show-page .priority {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: 700;
}

.ticket-show-page .status {
  background: #dbeafe;
  color: #1d4ed8;
}

.ticket-show-page .priority {
  background: #e2e8f0;
  color: #334155;
}

.ticket-show-page .ticket-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin: 0;
}

.ticket-show-page .detail-card {
  margin: 0;
  padding: 20px;
  border-radius: 20px;
  background: #fff;
  border: 1px solid #dbeafe;
}

.ticket-show-page .detail-card:first-child {
  grid-column: 1 / -1;
}

.ticket-show-page dt {
  margin: 0 0 10px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #64748b;
}

.ticket-show-page dd {
  margin: 0;
  color: #334155;
  line-height: 1.7;
  white-space: pre-wrap;
}

.ticket-show-page .description-body {
  overflow-wrap: anywhere;
}

.ticket-show-page .description-body :deep(.ticket-inline-code) {
  display: inline-block;
  border-radius: 4px;
  padding: 1px 6px;
  background: #e5e7eb;
  color: #0891b2;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.92em;
  font-weight: 400;
}

.ticket-show-page .ticket-related-section {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.ticket-show-page .ticket-related-section article {
  padding: 18px;
  border: 1px solid #dbeafe;
  border-radius: 8px;
  background: #fff;
}

.ticket-show-page .ticket-related-section h2 {
  margin: 0 0 12px;
  font-size: 18px;
}

.ticket-show-page .related-row {
  padding: 10px 0;
  border-top: 1px solid #e2e8f0;
}

.ticket-show-page .related-row strong {
  display: block;
  color: #0f172a;
  font-size: 13px;
}

.ticket-show-page .related-row p,
.ticket-show-page .muted {
  margin: 4px 0 0;
  color: #64748b;
  line-height: 1.6;
}

.ticket-show-page .related-ticket-link {
  display: block;
  padding: 10px 0;
  border-top: 1px solid #e2e8f0;
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 720px) {
.ticket-show-page {
    padding: 24px 16px;
  }

.ticket-show-page .ticket-panel {
    padding: 22px;
    border-radius: 22px;
  }

.ticket-show-page .ticket-header {
    flex-direction: column;
  }

.ticket-show-page .top-actions {
    align-items: stretch;
    flex-direction: column;
  }

.ticket-show-page .ticket-actions {
    width: 100%;
  }

  .ticket-show-page .edit-link,
.ticket-show-page .delete-button {
    flex: 1;
  }

.ticket-show-page .ticket-badges {
    justify-content: start;
  }

.ticket-show-page .ticket-detail-grid {
    grid-template-columns: 1fr;
  }

.ticket-show-page .detail-card:first-child {
    grid-column: auto;
  }

.ticket-show-page .ticket-related-section {
    grid-template-columns: 1fr;
  }
}
</style>
