/** Рубрика статьи (ответ GET /api/rubrics). */
export interface Rubric {
  id: number
  title: string
  slug?: string | null
  description?: string | null
}

/** Тег статьи (ответ GET /api/tags). */
export interface Tag {
  id: number
  title: string
  slug?: string | null
  popular?: number | null
}

/** Краткое превью статьи (элемент списка GET /api/articles). */
export interface ArticleSummary {
  id: number
  title: string
  excerpt?: string | null
  image?: string | null
  slug?: string | null
  published_at: string | null
  rubric?: Rubric | null
  tags?: Tag[]
}

/** Полная статья (ответ GET /api/articles/{id}). */
export interface Article extends ArticleSummary {
  viewed?: number | null
  content_html?: string | null
  keywords?: string | null
  meta_desc?: string | null
}

/** Стандартный ответ Laravel-пагинации. */
export interface Paginated<T> {
  data: T[]
  links?: {
    first?: string | null
    last?: string | null
    prev?: string | null
    next?: string | null
  }
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}
