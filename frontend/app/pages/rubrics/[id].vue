<script setup lang="ts">
import type { Rubric } from '~/types/article'

const route = useRoute()
const { articles, rubrics } = useBlogApi()

const rubricId = computed(() => Number(route.params.id))
const page = computed(() => Math.max(1, Number(route.query.page) || 1))

const { data: articlesData } = await useAsyncData(
  `rubric-articles-${rubricId.value}`,
  () => articles({ rubric: rubricId.value, page: page.value }),
  { watch: [page] },
)

const { data: rubricsData } = await useAsyncData('all-rubrics', () => rubrics())

const rubric = computed<Rubric | null>(
  () => rubricsData.value?.data.find((r) => r.id === rubricId.value) ?? null,
)

if (!rubric.value) {
  throw createError({ statusCode: 404, statusMessage: 'Рубрика не найдена' })
}

useHead(() => ({
  title: rubric.value?.title ?? 'Рубрика',
  meta: [
    { name: 'description', content: rubric.value?.description || undefined },
  ],
}))
</script>

<template>
  <VContainer class="py-6" style="max-width: 1100px">
    <h1 class="text-h3 text-md-h2 font-weight-bold mb-1">
      {{ rubric?.title }}
    </h1>
    <p v-if="rubric?.description" class="text-body-1 text-medium-emphasis mb-6">
      {{ rubric.description }}
    </p>

    <template v-if="articlesData?.data.length">
      <VRow dense>
        <VCol
          v-for="article in articlesData.data"
          :key="article.id"
          cols="12"
          sm="6"
          md="4"
          class="mb-4"
        >
          <ArticleCard :article="article" />
        </VCol>
      </VRow>

      <div class="mt-6">
        <PaginationBar
          :page="articlesData.meta.current_page"
          :last-page="articlesData.meta.last_page"
        />
      </div>
    </template>

    <VAlert v-else type="info" variant="tonal" class="mt-4">
      В этой рубрике пока нет статей.
    </VAlert>
  </VContainer>
</template>
