<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { renderTicketDescription } from '../utils/ticketDescription'

const props = defineProps<{
  id: string
  modelValue: string
  rows?: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const activeMode = ref<'edit' | 'preview'>('edit')
const textarea = ref<HTMLTextAreaElement | null>(null)

const previewHtml = computed(() => renderTicketDescription(props.modelValue))

const tools = [
  { label: 'B', symbol: 'B', openMarker: '**', closeMarker: '**', title: '太字' },
  { label: 'I', symbol: 'I', openMarker: '*', closeMarker: '*', title: '斜体' },
  { label: 'U', symbol: 'U', openMarker: '<u>', closeMarker: '</u>', title: '下線' },
  { label: 'S', symbol: 'S', openMarker: '~~', closeMarker: '~~', title: '打消し線' },
  { label: 'Code', symbol: '</>', openMarker: '`', closeMarker: '`', title: 'コード' },
]

const setMode = (mode: 'edit' | 'preview') => {
  activeMode.value = mode
}

const updateValue = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value)
}

const insertFormatting = async (openMarker: string, closeMarker: string) => {
  activeMode.value = 'edit'
  await nextTick()

  const element = textarea.value

  if (!element) {
    emit('update:modelValue', `${props.modelValue}${openMarker}${closeMarker}`)
    return
  }

  const start = element.selectionStart
  const end = element.selectionEnd
  const before = props.modelValue.slice(0, start)
  const selected = props.modelValue.slice(start, end)
  const after = props.modelValue.slice(end)
  const nextValue = `${before}${openMarker}${selected}${closeMarker}${after}`
  const cursorStart = start + openMarker.length
  const cursorEnd = cursorStart + selected.length

  emit('update:modelValue', nextValue)

  await nextTick()
  element.focus()
  element.setSelectionRange(cursorStart, cursorEnd)
}
</script>

<template>
  <div class="description-editor">
    <div class="editor-header">
      <div class="mode-tabs" role="tablist" aria-label="説明の表示切替">
        <button
          type="button"
          :class="{ active: activeMode === 'edit' }"
          role="tab"
          :aria-selected="activeMode === 'edit'"
          @click="setMode('edit')"
        >
          編集
        </button>
        <button
          type="button"
          :class="{ active: activeMode === 'preview' }"
          role="tab"
          :aria-selected="activeMode === 'preview'"
          @click="setMode('preview')"
        >
          プレビュー
        </button>
      </div>

      <div v-if="activeMode === 'edit'" class="format-toolbar" aria-label="説明の装飾">
        <button
          v-for="tool in tools"
          :key="tool.title"
          type="button"
          class="format-button"
          :title="tool.title"
          :aria-label="tool.title"
          @click="insertFormatting(tool.openMarker, tool.closeMarker)"
        >
          <span :class="[`tool-${tool.label.toLowerCase()}`]">{{ tool.symbol }}</span>
        </button>
      </div>
    </div>

    <textarea
      v-if="activeMode === 'edit'"
      :id="id"
      ref="textarea"
      :value="modelValue"
      :rows="rows ?? 7"
      @input="updateValue"
    ></textarea>

    <div v-else class="description-preview">
      <div v-if="previewHtml" class="preview-body" v-html="previewHtml"></div>
      <p v-else class="preview-empty">説明は未入力です。</p>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use '../../scss/abstracts/variables' as v;
@use '../../scss/abstracts/mixins' as m;

.description-editor {
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.42);
  border-radius: 11px;
  background: #fff;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.editor-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 46px;
  padding: 8px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.mode-tabs,
.format-toolbar {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.mode-tabs button,
.format-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  min-height: 30px;
  border: 1px solid transparent;
  border-radius: 6px;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
}

.mode-tabs button {
  min-width: 74px;
  padding: 0 12px;
}

.mode-tabs button.active,
.format-button:hover,
.format-button:focus-visible {
  border-color: #bfdbfe;
  background: #eff6ff;
  color: #1d4ed8;
}

.format-button:focus-visible,
.mode-tabs button:focus-visible {
  outline: 2px solid #93c5fd;
  outline-offset: 2px;
}

.tool-i {
  font-style: italic;
}

.tool-u {
  text-decoration: underline;
}

.tool-s {
  text-decoration: line-through;
}

.tool-code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 12px;
}

textarea {
  @include m.field-control(0, 0);

  display: block;
  width: 100%;
  min-height: 170px;
  border: 0;
  border-radius: 0;
  box-shadow: none;
  resize: vertical;
}

textarea:focus {
  box-shadow: inset 0 0 0 2px rgba(37, 99, 235, 0.18);
}

.description-preview {
  min-height: 170px;
  padding: 12px;
  color: v.$color-text;
}

.preview-body {
  line-height: 1.7;
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

.preview-body :deep(.ticket-inline-code) {
  display: inline-block;
  border-radius: 4px;
  padding: 1px 6px;
  background: #e5e7eb;
  color: #0891b2;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.92em;
  font-weight: 400;
}

.preview-empty {
  margin: 0;
  color: #94a3b8;
}

@media (max-width: 720px) {
  .editor-header {
    align-items: stretch;
    flex-direction: column;
  }

  .mode-tabs,
  .format-toolbar {
    width: 100%;
  }

  .mode-tabs button {
    flex: 1;
  }

  .format-toolbar {
    justify-content: flex-end;
  }
}
</style>
