<script setup lang="ts">
import type { Rubric, Tag } from '~/types/article'

defineProps<{
  rubrics: Rubric[]
  tags: Tag[]
}>()

const route = useRoute()
const router = useRouter()

const searchQuery = ref(typeof route.query.search === 'string' ? route.query.search : '')

function submitSearch(): void {
  router.push({ path: '/', query: { search: searchQuery.value || undefined } })
}
</script>

<template>
  <div class="blog-sidebar">
    <!-- Поиск -->
    <VCard class="mb-4">
      <VCardText>
        <VForm @submit.prevent="submitSearch">
          <VTextField
            v-model="searchQuery"
            label="Поиск по статьям"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="comfortable"
            hide-details
            clearable
            @click:prepend-inner="submitSearch"
          />
          <VBtn type="submit" color="primary" block class="mt-3">Найти</VBtn>
        </VForm>
      </VCardText>
    </VCard>

    <!-- Рубрики -->
    <VCard v-if="rubrics.length" class="mb-4">
      <VCardItem>
        <VCardTitle class="text-subtitle-1 font-weight-bold">Рубрики</VCardTitle>
      </VCardItem>
      <VList density="compact" nav>
        <VListItem
          v-for="rubric in rubrics"
          :key="rubric.id"
          :to="`/rubrics/${rubric.id}`"
          :active="Number(route.query.rubric) === rubric.id"
          rounded="lg"
          class="mx-2"
        >
          <template #prepend>
            <VIcon icon="mdi-folder-outline" size="20" />
          </template>
          <VListItemTitle>{{ rubric.title }}</VListItemTitle>
        </VListItem>
      </VList>
    </VCard>

    <!-- Теги -->
    <VCard v-if="tags.length">
      <VCardItem>
        <VCardTitle class="text-subtitle-1 font-weight-bold">Метки</VCardTitle>
      </VCardItem>
      <VCardText>
        <div class="d-flex flex-wrap ga-2">
          <VChip
            v-for="tag in tags"
            :key="tag.id"
            size="small"
            variant="outlined"
            :to="`/tags/${tag.id}`"
            :class="{ 'text-primary': Number(route.query.tag) === tag.id }"
          >
            #{{ tag.title }}
          </VChip>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>
