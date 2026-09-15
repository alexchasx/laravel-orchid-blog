<script setup lang="ts">
useHead({ title: 'Контакты' })

const { sendContact } = useBlogApi()

const form = reactive({ title: '', message: '' })
const formRef = ref()
const sending = ref(false)
const result = ref<{ type: 'success' | 'error'; text: string } | null>(null)

const rules = {
  messageRequired: (v: string) => !!v?.trim() || 'Напишите сообщение',
}

async function submit(): Promise<void> {
  result.value = null
  const { valid } = await formRef.value?.validate()
  if (!valid) return

  sending.value = true
  try {
    await sendContact({
      title: form.title.trim() || undefined,
      message: form.message,
    })
    result.value = {
      type: 'success',
      text: 'Сообщение отправлено! Спасибо за обратную связь.',
    }
    form.title = ''
    form.message = ''
    formRef.value?.resetValidation()
  } catch {
    result.value = {
      type: 'error',
      text: 'Не удалось отправить сообщение. Попробуйте ещё раз.',
    }
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <VContainer class="py-6" style="max-width: 760px">
    <h1 class="text-h3 text-md-h2 font-weight-bold mb-2">Контакты</h1>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Нашли ошибку или хотите обсудить тему? Напишите нам — ответим в ближайшее время.
    </p>

    <VAlert
      v-if="result"
      :type="result.type"
      variant="tonal"
      class="mb-4"
      closable
      @click:close="result = null"
    >
      {{ result.text }}
    </VAlert>

    <VCard class="pa-6 pa-md-8">
      <VForm ref="formRef" @submit.prevent="submit">
        <VTextField
          v-model="form.title"
          label="Тема (необязательно)"
          prepend-inner-icon="mdi-subject"
          class="mb-4"
          autocomplete="off"
        />
        <VTextarea
          v-model="form.message"
          label="Сообщение"
          prepend-inner-icon="mdi-message-text-outline"
          rows="6"
          counter
          :rules="[rules.messageRequired]"
          class="mb-4"
        />
        <VBtn
          type="submit"
          color="primary"
          size="large"
          :loading="sending"
          :disabled="sending"
          prepend-icon="mdi-send"
        >
          Отправить
        </VBtn>
      </VForm>
    </VCard>
  </VContainer>
</template>
