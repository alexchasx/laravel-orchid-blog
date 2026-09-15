<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{
  error: NuxtError
}>()

const handleError = () => clearError({ redirect: '/' })
</script>

<template>
  <VApp>
    <VContainer class="text-center py-16" style="max-width: 640px">
      <p class="text-h1 font-weight-black text-primary">{{ error.statusCode }}</p>
      <p class="text-h6 mb-6 text-medium-emphasis">
        {{ error.statusMessage || error.message || 'Что-то пошло не так' }}
      </p>
      <VBtn color="primary" prepend-icon="mdi-home-outline" @click="handleError">
        На главную
      </VBtn>
    </VContainer>
  </VApp>
</template>
