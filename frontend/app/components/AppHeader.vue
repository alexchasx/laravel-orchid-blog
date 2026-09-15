<script setup lang="ts">
const route = useRoute()

const navItems = [
  { label: 'Главная', to: '/', icon: 'mdi-home-outline' },
  { label: 'О блоге', to: '/about', icon: 'mdi-information-outline' },
  { label: 'Контакты', to: '/contact', icon: 'mdi-email-outline' },
]

function isActive(item: { to: string }): boolean {
  if (item.to === '/') return route.path === '/'
  return route.path.startsWith(item.to)
}
</script>

<template>
  <VAppBar flat class="border-b">
    <VContainer class="d-flex align-center">
      <VAppBarTitle class="flex-grow-0">
        <NuxtLink to="/" class="d-flex align-center ga-2 text-decoration-none">
          <VAvatar color="primary" variant="flat" size="38" rounded="lg">
            <VIcon icon="mdi-code-tags" size="22" color="white" />
          </VAvatar>
          <span class="text-h6 font-weight-bold text-primary">BlogDev</span>
        </NuxtLink>
      </VAppBarTitle>

      <VSpacer />

      <!-- Навигация: десктоп -->
      <div class="d-none d-sm-flex ga-1">
        <VBtn
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          :variant="isActive(item) ? 'flat' : 'text'"
          color="primary"
        >
          {{ item.label }}
        </VBtn>
      </div>

      <ThemeToggle class="ms-2" />

      <!-- Навигация: мобильное меню-бургер -->
      <VMenu location="bottom end" :close-on-content-click="false">
        <template #activator="{ props }">
          <VBtn
            v-bind="props"
            icon="mdi-menu"
            variant="text"
            class="d-sm-none"
            aria-label="Открыть меню"
          />
        </template>
        <VList min-width="220" class="pa-2">
          <VListItem
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            :active="isActive(item)"
            rounded="lg"
          >
            <template #prepend>
              <VIcon :icon="item.icon" />
            </template>
            <VListItemTitle>{{ item.label }}</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
    </VContainer>
  </VAppBar>
</template>
