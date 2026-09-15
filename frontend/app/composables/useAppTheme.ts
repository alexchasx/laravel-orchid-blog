type ThemeName = 'light' | 'dark'

/**
 * Светлая/тёмная тема Vuetify с сохранением выбора в cookie.
 * Cookie читается на сервере, поэтому SSR отдаёт HTML сразу в нужной
 * теме и «вспышка» неправильной темы при загрузке отсутствует.
 */
export function useAppTheme() {
  const vuetifyTheme = useTheme()
  const cookie = useCookie<ThemeName>('blog-theme', {
    default: () => 'light',
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
  })

  function apply(name: ThemeName): void {
    const themeApi = vuetifyTheme as unknown as {
      change?: (name: string) => void
      global: { name: { value: string } }
    }
    if (typeof themeApi.change === 'function') {
      // Vuetify >= 3.8: theme.change(name)
      themeApi.change(name)
    } else {
      themeApi.global.name.value = name
    }
  }

  // Тема из cookie известна и на сервере, и при гидрации на клиенте —
  // SSR отдаёт HTML сразу в нужной теме (без «вспышки»).
  if (cookie.value === 'dark' || cookie.value === 'light') {
    apply(cookie.value)
  }

  const theme = computed<ThemeName>(() =>
    vuetifyTheme.global.name.value === 'dark' ? 'dark' : 'light',
  )

  const toggle = () => {
    const next: ThemeName = theme.value === 'light' ? 'dark' : 'light'
    apply(next)
    cookie.value = next
  }

  return { theme, toggle }
}
