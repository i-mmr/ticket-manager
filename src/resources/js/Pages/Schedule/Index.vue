<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '../../Components/AppLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'

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

defineProps<{
  currentUser: CurrentUser
  workspaces: Workspace[]
}>()
</script>

<template>
  <Head title="スケジュール" />

  <AppLayout :current-user="currentUser" active="schedule" :workspaces="workspaces">
    <section class="placeholder-panel">
      <PageHeader title="スケジュール" description="スケジュール情報を確認できます。" />
    </section>
  </AppLayout>
</template>
