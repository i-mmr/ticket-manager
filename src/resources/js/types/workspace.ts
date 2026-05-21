export interface WorkspaceUser {
  id: number
  name: string
  email: string
}

export interface WorkspaceTeam {
  id: number
  name: string
  users_count: number
  users: WorkspaceUser[]
}

export interface WorkspaceProject {
  id: number
  name: string
  status: string
  tickets_count: number
}

export interface WorkspaceTree {
  id: number
  name: string
  teams_count: number
  projects_count: number
  teams: WorkspaceTeam[]
  projects: WorkspaceProject[]
}

export const workspaceSummary = (workspace: WorkspaceTree) =>
  `${workspace.projects_count}プロジェクト / ${workspace.teams_count}チーム`

export const teamUserSummary = (team: WorkspaceTeam) =>
  `${team.users_count}ユーザー`

export const projectTicketSummary = (project: WorkspaceProject) =>
  `${project.tickets_count}チケット`
