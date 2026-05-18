<script setup lang="ts">
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
  workspaces: Workspace[]
  activeProjectId?: number | null
  selectableProjects?: boolean
}>()

const emit = defineEmits<{
  selectProject: [projectId: number]
}>()
</script>

<template>
  <section class="workspace-tree" aria-label="ワークスペース情報">
    <div v-if="workspaces.length" class="workspace-list">
      <section v-for="workspace in workspaces" :key="workspace.id" class="workspace-group">
        <div class="workspace-row">
          <span class="workspace-icon">W</span>
          <div>
            <h3>{{ workspace.name }}</h3>
            <p>{{ workspace.projects.length }}プロジェクト / {{ workspace.teams.length }}チーム</p>
          </div>
        </div>

        <div class="tree-block">
          <p class="tree-label">チーム</p>
          <div v-for="team in workspace.teams" :key="team.id" class="tree-item">
            <span class="tree-dot team-dot"></span>
            <div>
              <strong>{{ team.name }}</strong>
              <p>{{ team.users.length }}ユーザー</p>
              <ul v-if="team.users.length" class="user-list">
                <li v-for="user in team.users" :key="user.id">
                  {{ user.name }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="tree-block">
          <p class="tree-label">プロジェクト</p>
          <button
            v-for="project in workspace.projects"
            :key="project.id"
            type="button"
            class="tree-item project-item"
            :class="{ active: selectableProjects && project.id === activeProjectId }"
            :disabled="!selectableProjects"
            @click="selectableProjects && emit('selectProject', project.id)"
          >
            <span class="tree-dot project-dot"></span>
            <div>
              <strong>{{ project.name }}</strong>
              <p>{{ project.tickets_count }}チケット</p>
            </div>
          </button>
        </div>
      </section>
    </div>

    <div v-else class="pane-empty">
      ワークスペースはまだありません。
    </div>
  </section>
</template>

<style scoped lang="scss">
.workspace-tree {
  border-top: 1px solid #e2e8f0;
  padding-top: 14px;
}

.workspace-list {
  display: grid;
  gap: 16px;
}

.workspace-group {
  padding-top: 2px;
}

.workspace-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.workspace-icon {
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

.workspace-row h3 {
  margin: 0;
  font-size: 16px;
}

.workspace-row p,
.tree-item p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
}

.tree-block {
  margin-top: 16px;
}

.tree-label {
  margin: 0 0 8px;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.tree-item {
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

.project-item {
  border-radius: 8px;
}

.project-item:not(:disabled) {
  cursor: pointer;
}

.project-item:disabled {
  cursor: default;
}

.project-item:not(:disabled):hover,
.project-item.active {
  border-left-color: #2563eb;
  background: #eff6ff;
}

.tree-item strong {
  font-size: 14px;
}

.user-list {
  display: grid;
  gap: 4px;
  margin: 8px 0 0;
  padding: 0;
  list-style: none;
}

.user-list li {
  overflow: hidden;
  color: #334155;
  font-size: 12px;
  line-height: 1.5;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tree-dot {
  width: 9px;
  height: 9px;
  margin-top: 5px;
  border-radius: 999px;
}

.team-dot {
  background: #14b8a6;
}

.project-dot {
  background: #2563eb;
}

.pane-empty {
  padding: 8px 0 4px;
  color: #64748b;
  font-size: 14px;
}
</style>
