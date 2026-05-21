export type NavigationKey = 'home' | 'projects' | 'tickets' | 'schedule'

export interface NavigationItem {
  key: NavigationKey
  label: string
  href: string
}

export const headerNavigationItems: NavigationItem[] = [
  { key: 'home', label: 'ホーム', href: '/home' },
  { key: 'projects', label: 'プロジェクト', href: '/projects' },
  { key: 'tickets', label: 'チケット', href: '/tickets' },
  { key: 'schedule', label: 'スケジュール', href: '/schedule' },
]

export const sideNavigationItems: NavigationItem[] = headerNavigationItems.filter(
  (item) => item.key !== 'projects',
)
