<script setup lang="ts">
import type { Article } from '~/types/article'

defineProps<{
  article: Article
}>()
</script>

<template>
  <article class="article-layout mx-auto" style="max-width: 820px">
    <header class="mb-8">
      <div class="d-flex flex-wrap align-center ga-2 mb-4">
        <VChip
          v-if="article.rubric"
          :to="`/rubrics/${article.rubric.id}`"
          prepend-icon="mdi-folder-outline"
          size="small"
          color="primary"
          variant="tonal"
        >
          {{ article.rubric.title }}
        </VChip>
        <VChip
          v-for="tag in article.tags"
          :key="tag.id"
          :to="`/tags/${tag.id}`"
          size="small"
          variant="outlined"
        >
          #{{ tag.title }}
        </VChip>
      </div>

      <h1 class="text-h3 text-md-h2 font-weight-bold mb-3">
        {{ article.title }}
      </h1>

      <div class="d-flex align-center ga-1 text-body-2 text-medium-emphasis">
        <VIcon icon="mdi-calendar-month-outline" size="18" />
        <time :datetime="article.published_at ?? undefined">
          {{ formatDate(article.published_at) }}
        </time>
        <template v-if="article.viewed">
          <span class="mx-1">•</span>
          <VIcon icon="mdi-eye-outline" size="18" />
          <span>{{ article.viewed }} {{ article.viewed === 1 ? 'просмотр' : 'просмотров' }}</span>
        </template>
      </div>
    </header>

    <VImg
      v-if="article.image"
      :src="mediaUrl(article.image)"
      :alt="article.title"
      cover
      rounded="xl"
      max-height="420"
      loading="lazy"
      class="mb-8"
    />

    <div class="article-content" v-html="article.content_html" />
  </article>
</template>
