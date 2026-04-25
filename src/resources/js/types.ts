export type TicketStatus = 'open' | 'in_progress' | 'done'
export type TicketPriority = 'high' | 'medium' | 'low'

export interface TicketProject {
  id: number
  name: string
  workspace: string | null
}

