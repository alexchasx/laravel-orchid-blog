<script setup lang="ts">
// Тема читается из cookie и на сервере, и на клиенте —
// SSR отдаёт HTML сразу в нужной теме (без «вспышки»).
useAppTheme()

useHead({
  htmlAttrs: { lang: 'ru' },
})
</script>

<template>
  <VApp>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </VApp>
</template>
