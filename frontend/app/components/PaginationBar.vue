<script setup lang="ts">
const props = defineProps<{
  page: number
  lastPage: number
}>()

const route = useRoute()
const router = useRouter()

function go(next: number | null): void {
  if (!next || next === props.page) return
  const query = { ...route.query }
  if (next > 1) query.page = String(next)
  else delete query.page
  router.push({ query })
}
</script>

<template>
  <VPagination
    v-if="lastPage > 1"
    :model-value="page"
    :length="lastPage"
    :total-visible="5"
    color="primary"
    class="justify-center"
    aria-label="Пагинация статей"
    @update:model-value="go"
  />
</template>
