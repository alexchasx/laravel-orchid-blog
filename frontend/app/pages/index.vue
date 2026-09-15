<script setup lang="ts">
import type { ArticleSummary, Paginated, Rubric, Tag } from '~/types/article'

const route = useRoute()

const page = computed(() => Math.max(1, Number(route.query.page) || 1))
const search = computed(() =>
  typeof route.query.search === 'string' ? route.query.search.trim() : '',
)
const rubricId = computed(() => Number(route.query.rubric) || undefined)
const tagId = computed(() => Number(route.query.tag) || undefined)

const { articles, rubrics, tags } = useBlogApi()

const { data: articlesData } = await useAsyncData(
  'articles-list',
  () =>
    articles({
      page: page.value,
      search: search.value || undefined,
      rubric: rubricId.value,
      tag: tagId.value,
    }),
  { watch: [page, search, rubricId, tagId] },
)

const { data: rubricsData } = await useAsyncData('sidebar-rubrics', () => rubrics())
const { data: tagsData } = await useAsyncData('sidebar-tags', () => tags())

const list = computed<Paginated<ArticleSummary> | null>(() => articlesData.value ?? null)
const isSearching = computed(() => Boolean(search.value))

useHead(() => ({
  title: isSearching.value ? `Поиск: ${search.value}` : 'Последние статьи',
  meta: [
    {
      name: 'description',
      content: isSearching.value
        ? ''
        : 'IT-блог о веб-разработке: PHP, Laravel, JavaScript, Nginx и смежные технологии.',
    },
    ...(isSearching.value ? [{ name: 'robots', content: 'noindex, nofollow' }] : []),
  ],
}))
</script>

<template>
  <VContainer class="py-6">
    <h1
      v-if="isSearching"
      class="text-h4 font-weight-bold mb-4"
    >
      Результаты поиска: «{{ search }}»
    </h1>
    <section v-else class="mb-8">
      <h1 class="text-h3 text-md-h2 font-weight-bold mb-2">
        Блог о веб-разработке
      </h1>
      <p class="text-body-1 text-medium-emphasis" style="max-width: 640px">
        Заметки о PHP, Laravel, JavaScript, Nginx и смежных технологиях —
        практический опыт и разборы.
      </p>
    </section>

    <VRow>
      <VCol cols="12" lg="8">
        <template v-if="list?.data.length">
          <VRow dense>
            <VCol
              v-for="article in list!.data"
              :key="article.id"
              cols="12"
              sm="6"
              class="mb-4"
            >
              <ArticleCard :article="article" />
            </VCol>
          </VRow>

          <div class="mt-6">
            <PaginationBar
              :page="list!.meta.current_page"
              :last-page="list!.meta.last_page"
            />
          </div>
        </template>

        <VAlert v-else type="info" variant="tonal" class="mt-4" icon="mdi-text-search">
          Ничего не нашлось. Попробуйте изменить запрос или выбрать другую рубрику.
        </VAlert>
      </VCol>

      <VCol cols="12" lg="4">
        <BlogSidebar
          :rubrics="rubricsData?.data ?? []"
          :tags="tagsData?.data ?? []"
        />
      </VCol>
    </VRow>
  </VContainer>
</template>
