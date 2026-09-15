<script setup lang="ts">
const { theme, toggle } = useAppTheme()
</script>

<template>
  <VBtn
    :icon="theme === 'dark' ? 'mdi-weather-sunny' : 'mdi-weather-night'"
    variant="text"
    :aria-label="theme === 'dark' ? 'Включить светлую тему' : 'Включить тёмную тему'"
    :title="theme === 'dark' ? 'Светлая тема' : 'Тёмная тема'"
    @click="toggle"
  />
</template>
