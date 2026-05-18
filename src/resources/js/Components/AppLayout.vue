<script setup lang="ts">
import AppNavigation from './AppNavigation.vue'
import AppSideMenu from './AppSideMenu.vue'

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

withDefaults(defineProps<{
  currentUser: CurrentUser
  active: 'home' | 'projects' | 'tickets' | 'schedule'
  workspaces?: Workspace[]
  activeProjectId?: number | null
  selectableProjects?: boolean
}>(), {
  workspaces: undefined,
  activeProjectId: null,
  selectableProjects: false,
})

const emit = defineEmits<{
  selectProject: [projectId: number]
}>()
</script>

<template>
  <div class="app-page">
    <AppNavigation :current-user="currentUser" :active="active" />

    <div class="app-layout">
      <AppSideMenu
        :active="active"
        :workspaces="workspaces"
        :active-project-id="activeProjectId"
        :selectable-projects="selectableProjects"
        @select-project="emit('selectProject', $event)"
      />
      <main class="app-main">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped lang="scss">
.app-page {
  min-height: 100vh;
  padding: 40px 24px;
  background:
    radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 32%),
    linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
  color: #172554;
}

.app-layout {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  gap: 20px;
  align-items: start;
}

.app-main {
  min-width: 0;
}

@media (max-width: 860px) {
  .app-page {
    padding: 24px 16px;
  }

  .app-layout {
    display: block;
  }

  .app-layout > :deep(.side-nav) {
    margin-bottom: 18px;
  }
}
</style>
