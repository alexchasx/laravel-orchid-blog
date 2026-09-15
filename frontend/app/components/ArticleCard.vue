<script setup lang="ts">
import type { ArticleSummary } from '~/types/article'

defineProps<{
  article: ArticleSummary
}>()
</script>

<template>
  <VCard class="h-100 d-flex flex-column overflow-hidden" hover>
    <VImg
      v-if="article.image"
      :src="mediaUrl(article.image)"
      :alt="article.title"
      cover
      loading="lazy"
      aspect-ratio="16/9"
    />
    <div
      v-else
      class="article-card-placeholder d-flex align-center justify-center text-medium-emphasis"
    >
      <VIcon icon="mdi-image-outline" size="44" />
    </div>

    <VCardItem class="pt-4">
      <VCardTitle class="text-h6 font-weight-bold" style="line-height: 1.4">
        <NuxtLink
          :to="`/articles/${article.id}`"
          class="text-decoration-none text-inherit"
        >
          {{ article.title }}
        </NuxtLink>
      </VCardTitle>
      <VCardSubtitle class="mt-2 d-flex align-center ga-1 text-body-2">
        <VIcon icon="mdi-calendar-month-outline" size="16" />
        <time :datetime="article.published_at ?? undefined">
          {{ formatDate(article.published_at) }}
        </time>
      </VCardSubtitle>
    </VCardItem>

    <VCardText
      v-if="article.excerpt"
      class="article-card-excerpt text-body-2 text-medium-emphasis"
    >
      {{ article.excerpt }}
    </VCardText>

    <VSpacer />

    <VCardActions
      v-if="article.rubric || article.tags?.length"
      class="pa-4 pt-0 flex-wrap"
    >
      <VChip
        v-if="article.rubric"
        size="small"
        color="primary"
        variant="tonal"
        :to="`/rubrics/${article.rubric.id}`"
        prepend-icon="mdi-folder-outline"
      >
        {{ article.rubric.title }}
      </VChip>
      <VChip
        v-for="tag in article.tags?.slice(0, 3)"
        :key="tag.id"
        size="small"
        variant="outlined"
        :to="`/tags/${tag.id}`"
      >
        #{{ tag.title }}
      </VChip>
    </VCardActions>
  </VCard>
</template>
