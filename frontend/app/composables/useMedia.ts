/**
 * Форматирование дат для русской локали.
 */
export function formatDate(value?: string | null): string {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

/**
 * Превращает путь к файлу в storage/ Laravel в абсолютный URL
 * (или возвращает внешний URL как есть).
 */
export function mediaUrl(path?: string | null): string {
  if (!path) return ''
  if (/^https?:\/\//.test(path)) return path
  const base = useRuntimeConfig().public.apiBase as string
  return `${base}${path.startsWith('/') ? path : `/${path}`}`
}
