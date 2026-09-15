import type { Article, ArticleSummary, Paginated, Rubric, Tag } from '~/types/article'

/**
 * Обёртка над публичным JSON API Laravel.
 * Базовый URL задаётся через runtimeConfig (NUXT_PUBLIC_API_BASE).
 */
export function useBlogApi() {
  const base = useRuntimeConfig().public.apiBase as string

  return {
    /** Пагинированный список опубликованных статей с фильтрами. */
    articles(params?: {
      page?: number
      search?: string
      rubric?: number
      tag?: number
    }): Promise<Paginated<ArticleSummary>> {
      return $fetch('/api/articles', {
        baseURL: base,
        params,
        headers: { Accept: 'application/json' },
      })
    },

    /** Полная статья (404 выбрасывается для неопубликованных). */
    article(id: string | number): Promise<{ data: Article }> {
      return $fetch(`/api/articles/${id}`, {
        baseURL: base,
        headers: { Accept: 'application/json' },
      })
    },

    rubrics(): Promise<{ data: Rubric[] }> {
      return $fetch('/api/rubrics', {
        baseURL: base,
        headers: { Accept: 'application/json' },
      })
    },

    tags(): Promise<{ data: Tag[] }> {
      return $fetch('/api/tags', {
        baseURL: base,
        headers: { Accept: 'application/json' },
      })
    },

    /** Отправка формы обратной связи (422 — ошибки валидации). */
    sendContact(payload: { title?: string; message: string }): Promise<{ message: string }> {
      return $fetch('/api/contact', {
        baseURL: base,
        method: 'POST',
        body: payload,
        headers: { Accept: 'application/json' },
      })
    },
  }
}
