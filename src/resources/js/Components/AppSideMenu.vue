<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import WorkspaceTree from './WorkspaceTree.vue'
import type { NavigationItem, NavigationKey } from '../navigation'
import type { WorkspaceTree as Workspace } from '../types/workspace'

defineProps<{
  active: NavigationKey
  items: NavigationItem[]
  workspaces?: Workspace[]
  activeProjectId?: number | null
  selectableProjects?: boolean
}>()

const emit = defineEmits<{
  selectProject: [projectId: number]
}>()
</script>

<template>
  <aside class="side-nav" aria-label="左メニュー">
    <nav class="primary-nav" aria-label="主要メニュー">
      <Link
        v-for="item in items"
        :key="item.key"
        :href="item.href"
        :class="{ active: active === item.key }"
      >
        {{ item.label }}
      </Link>
    </nav>

    <WorkspaceTree
      v-if="workspaces"
      :workspaces="workspaces"
      :active-project-id="activeProjectId"
      :selectable-projects="selectableProjects"
      @select-project="emit('selectProject', $event)"
    />
  </aside>
</template>

<style scoped lang="scss">
.side-nav {
  position: sticky;
  top: 24px;
  display: grid;
  gap: 8px;
  align-self: start;
  padding: 14px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
}

.primary-nav {
  display: grid;
  gap: 8px;
}

.primary-nav a {
  min-height: 40px;
  display: flex;
  align-items: center;
  border-radius: 8px;
  padding: 0 12px;
  color: #334155;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

.primary-nav a.active {
  background: #eff6ff;
  color: #1d4ed8;
}

@media (max-width: 860px) {
  .side-nav {
    position: static;
  }

  .primary-nav {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .primary-nav a {
    justify-content: center;
  }
}
</style>
