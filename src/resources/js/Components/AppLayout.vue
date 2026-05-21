<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppNavigation from './AppNavigation.vue'
import AppSideMenu from './AppSideMenu.vue'
import { headerNavigationItems, sideNavigationItems } from '../navigation'
import type { NavigationKey } from '../navigation'
import type { WorkspaceTree as Workspace } from '../types/workspace'

interface CurrentUser {
  name: string | null
  email: string | null
}

interface SharedProps extends Record<string, unknown> {
  currentUser?: CurrentUser | null
  workspaces?: Workspace[]
}

withDefaults(defineProps<{
  active: NavigationKey
  activeProjectId?: number | null
  selectableProjects?: boolean
}>(), {
  activeProjectId: null,
  selectableProjects: false,
})

const page = usePage<SharedProps>()

const currentUser = computed<CurrentUser>(() => page.props.currentUser ?? {
  name: null,
  email: null,
})

const workspaces = computed<Workspace[]>(() => page.props.workspaces ?? [])

const emit = defineEmits<{
  selectProject: [projectId: number]
}>()
</script>

<template>
  <div class="app-page">
    <AppNavigation :current-user="currentUser" :active="active" :items="headerNavigationItems" />

    <div class="app-layout">
      <AppSideMenu
        :active="active"
        :items="sideNavigationItems"
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
