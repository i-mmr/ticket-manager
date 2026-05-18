<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import TicketDescriptionEditor from '../../Components/TicketDescriptionEditor.vue'
import type { TicketPriority, TicketStatus } from '../../types'

interface ProjectOption {
  id: number
  name: string
  workspace: string | null
}

interface UserOption {
  id: number
  name: string
  email: string
}

interface EditableTicket {
  id: number
  project_id: number | null
  assignee_id: number | null
  title: string
  category: string | null
  description: string | null
  status: TicketStatus
  priority: TicketPriority
}

const props = defineProps<{
  projects: ProjectOption[]
  users: UserOption[]
  ticket: EditableTicket
}>()

const form = useForm({
  project_id: props.ticket.project_id ?? '',
  assignee_id: props.ticket.assignee_id ?? '',
  title: props.ticket.title,
  category: props.ticket.category ?? '',
  description: props.ticket.description ?? '',
  status: props.ticket.status,
  priority: props.ticket.priority,
})

const submit = () => {
  form.patch(`/tickets/${props.ticket.id}`)
}
</script>

<template>
  <Head :title="`${ticket.title}を編集`" />

  <div class="ticket-form-page">
    <main class="ticket-form-shell">
      <Link :href="`/tickets/${ticket.id}`" class="back-link">
        詳細へ戻る
      </Link>

      <section class="form-panel">
        <div class="form-header">
          <div>
            <p class="eyebrow">Edit Ticket</p>
            <h1>チケット修正</h1>
          </div>
        </div>

        <form class="ticket-form" @submit.prevent="submit">
          <div class="form-group">
            <label for="project_id">プロジェクト</label>
            <select id="project_id" v-model="form.project_id">
              <option value="">未選択</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.workspace ? `${project.workspace} / ` : '' }}{{ project.name }}
              </option>
            </select>
            <p v-if="form.errors.project_id" class="error">{{ form.errors.project_id }}</p>
          </div>

          <div class="form-group">
            <label for="title">タイトル</label>
            <input id="title" v-model="form.title" type="text" autocomplete="off" />
            <p v-if="form.errors.title" class="error">{{ form.errors.title }}</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="category">カテゴリ</label>
              <input id="category" v-model="form.category" type="text" autocomplete="off" />
              <p v-if="form.errors.category" class="error">{{ form.errors.category }}</p>
            </div>

            <div class="form-group">
              <label for="assignee_id">担当者</label>
              <select id="assignee_id" v-model="form.assignee_id">
                <option value="">未選択</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <p v-if="form.errors.assignee_id" class="error">{{ form.errors.assignee_id }}</p>
            </div>
          </div>

          <div class="form-group">
            <label for="description">説明</label>
            <TicketDescriptionEditor id="description" v-model="form.description" :rows="7" />
            <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="status">ステータス</label>
              <select id="status" v-model="form.status">
                <option value="open">未対応</option>
                <option value="in_progress">対応中</option>
                <option value="done">完了</option>
              </select>
              <p v-if="form.errors.status" class="error">{{ form.errors.status }}</p>
            </div>

            <div class="form-group">
              <label for="priority">優先度</label>
              <select id="priority" v-model="form.priority">
                <option value="high">高</option>
                <option value="medium">中</option>
                <option value="low">低</option>
              </select>
              <p v-if="form.errors.priority" class="error">{{ form.errors.priority }}</p>
            </div>
          </div>

          <div class="form-actions">
            <Link :href="`/tickets/${ticket.id}`" class="secondary-button">キャンセル</Link>
            <button type="submit" class="primary-button" :disabled="form.processing">
              {{ form.processing ? '更新中...' : '更新する' }}
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>

<style scoped lang="scss">
@use '../../../scss/abstracts/variables' as v;
@use '../../../scss/abstracts/mixins' as m;

.ticket-form-page {
  min-height: 100vh;
  padding: 40px 24px;
  background:
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 34%),
    linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
  color: v.$color-text;

  h1 {
    margin: 0;
    font-size: clamp(30px, 5vw, 44px);
  }

  .ticket-form-shell {
    max-width: 760px;
    margin: 0 auto;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    margin-bottom: 18px;
    color: v.$color-primary-dark;
    font-weight: 700;
    text-decoration: none;
  }

  .form-panel {
    padding: 28px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: v.$radius-md;
    background: rgba(255, 255, 255, 0.92);
    box-shadow: v.$shadow-panel;
  }

  .form-header {
    margin-bottom: 24px;
  }

  .eyebrow {
    @include m.eyebrow(0.12em);
  }

  .ticket-form {
    display: grid;
    gap: 18px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }

  .form-group {
    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 700;
    }

    input,
    select {
      @include m.field-control(11px, 12px);
    }
  }

  .form-actions {
    display: flex;
    justify-content: end;
    gap: 12px;
    margin-top: 6px;
  }
}

@media (max-width: 720px) {
  .ticket-form-page {
    padding: 24px 16px;

    .form-panel {
      padding: 22px;
    }

    .form-grid,
    .form-actions {
      grid-template-columns: 1fr;
      flex-direction: column;
    }

    .primary-button,
    .secondary-button {
      width: 100%;
    }
  }
}
</style>
