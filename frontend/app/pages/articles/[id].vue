<script setup lang="ts">
import type { Article } from '~/types/article'

const route = useRoute()
const { article } = useBlogApi()

const id = computed(() => String(route.params.id))

const { data, error } = await useAsyncData(
  `article-${id.value}`,
  () => article(id.value),
  { watch: [id] },
)

if (error.value || !data.value) {
  throw createError({ statusCode: 404, statusMessage: 'Статья не найдена' })
}

const articleData = computed<Article | null>(() => data.value?.data ?? null)

useHead(() => ({
  title: articleData.value?.title,
  meta: [
    {
      name: 'description',
      content: articleData.value?.meta_desc || articleData.value?.excerpt || undefined,
    },
    { property: 'og:type', content: 'article' },
    { property: 'og:title', content: articleData.value?.title ?? undefined },
    {
      property: 'og:description',
      content: articleData.value?.meta_desc || articleData.value?.excerpt || undefined,
    },
    {
      property: 'og:image',
      content: articleData.value?.image ? mediaUrl(articleData.value.image) : undefined,
    },
  ],
}))
</script>

<template>
  <VContainer class="py-6">
    <ArticleLayout v-if="articleData" :article="articleData" />
  </VContainer>
</template>
