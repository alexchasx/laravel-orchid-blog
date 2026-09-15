<script setup lang="ts">
const year = new Date().getFullYear()
</script>

<template>
  <footer class="border-t">
    <VContainer class="d-flex flex-column flex-sm-row align-center justify-space-between ga-2 py-4">
      <div class="text-body-2 text-medium-emphasis text-center text-sm-start">
        © {{ year }} BlogDev · Сделано на
        <a href="https://nuxt.com/" target="_blank" rel="noopener" class="text-primary">Nuxt</a>
        +
        <a href="https://vuetifyjs.com/" target="_blank" rel="noopener" class="text-primary">Vuetify</a>
      </div>
      <div class="d-flex ga-2">
        <a
          href="https://github.com/alexchasx"
          target="_blank"
          rel="noopener"
          class="footer-github-link d-inline-flex align-center justify-center rounded-circle text-medium-emphasis"
          aria-label="GitHub"
        >
          <VIcon icon="mdi-github" size="20" />
        </a>
      </div>
    </VContainer>
  </footer>
</template>
