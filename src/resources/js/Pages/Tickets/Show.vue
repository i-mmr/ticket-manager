<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  ticket: {
    type: Object,
    required: true,
  },
})

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
  <Head :title="ticket.title" />

  <div class="ticket-show-page">
    <div class="ticket-show-shell">
      <Link href="/dashboard" class="back-link">
        一覧へ戻る
      </Link>

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
            <dd>{{ ticket.description || '説明は未登録です。' }}</dd>
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

<style scoped>
.ticket-show-page {
  min-height: 100vh;
  padding: 40px 24px;
  background:
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 34%),
    linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
  color: #172554;
}

.ticket-show-shell {
  max-width: 960px;
  margin: 0 auto;
}

.back-link {
  display: inline-flex;
  align-items: center;
  margin-bottom: 18px;
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.ticket-panel {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 28px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
  backdrop-filter: blur(12px);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 16px;
  margin-bottom: 24px;
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
  font-size: clamp(30px, 5vw, 44px);
}

.ticket-badges {
  display: flex;
  flex-wrap: wrap;
  justify-content: end;
  gap: 10px;
}

.status,
.priority {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: 700;
}

.status {
  background: #dbeafe;
  color: #1d4ed8;
}

.priority {
  background: #e2e8f0;
  color: #334155;
}

.ticket-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin: 0;
}

.detail-card {
  margin: 0;
  padding: 20px;
  border-radius: 20px;
  background: #fff;
  border: 1px solid #dbeafe;
}

.detail-card:first-child {
  grid-column: 1 / -1;
}

dt {
  margin: 0 0 10px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #64748b;
}

dd {
  margin: 0;
  color: #334155;
  line-height: 1.7;
  white-space: pre-wrap;
}

.ticket-related-section {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.ticket-related-section article {
  padding: 18px;
  border: 1px solid #dbeafe;
  border-radius: 8px;
  background: #fff;
}

.ticket-related-section h2 {
  margin: 0 0 12px;
  font-size: 18px;
}

.related-row {
  padding: 10px 0;
  border-top: 1px solid #e2e8f0;
}

.related-row strong {
  display: block;
  color: #0f172a;
  font-size: 13px;
}

.related-row p,
.muted {
  margin: 4px 0 0;
  color: #64748b;
  line-height: 1.6;
}

.related-ticket-link {
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

  .ticket-panel {
    padding: 22px;
    border-radius: 22px;
  }

  .ticket-header {
    flex-direction: column;
  }

  .ticket-badges {
    justify-content: start;
  }

  .ticket-detail-grid {
    grid-template-columns: 1fr;
  }

  .detail-card:first-child {
    grid-column: auto;
  }

  .ticket-related-section {
    grid-template-columns: 1fr;
  }
}
</style>
