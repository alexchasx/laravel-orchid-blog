<script setup lang="ts">
import type { Tag } from '~/types/article'

const route = useRoute()
const { articles, tags } = useBlogApi()

const tagId = computed(() => Number(route.params.id))
const page = computed(() => Math.max(1, Number(route.query.page) || 1))

const { data: articlesData } = await useAsyncData(
  `tag-articles-${tagId.value}`,
  () => articles({ tag: tagId.value, page: page.value }),
  { watch: [page] },
)

const { data: tagsData } = await useAsyncData('all-tags', () => tags())

const tag = computed<Tag | null>(
  () => tagsData.value?.data.find((t) => t.id === tagId.value) ?? null,
)

if (!tag.value) {
  throw createError({ statusCode: 404, statusMessage: 'Метка не найдена' })
}

useHead(() => ({
  title: tag.value ? `Записи с меткой «${tag.value.title}»` : 'Метка',
}))
</script>

<template>
  <VContainer class="py-6" style="max-width: 1100px">
    <h1 class="text-h3 text-md-h2 font-weight-bold mb-6">
      Записи с меткой «{{ tag?.title }}»
    </h1>

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
      По этой метке пока нет статей.
    </VAlert>
  </VContainer>
</template>
