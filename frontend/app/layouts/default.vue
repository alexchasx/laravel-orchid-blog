<script setup lang="ts">
const route = useRoute();

useHead({
  title: route.meta.title as string | undefined,
});
</script>

<template>
  <VLayout class="app-shell">
    <AppHeader />

    <VMain class="app-main">
      <slot />
    </VMain>

    <AppFooter />
  </VLayout>
</template>

<style scoped>
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.app-main {
  min-height: calc(100vh - 64px);
  flex: 1 0 auto;
}
</style>
