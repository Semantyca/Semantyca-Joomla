<template>
  <n-button-group>
    <n-button v-for="button in buttons" :key="button.action" @click="handleClick(button.action)" secondary>
      <template #icon>
        <n-icon>
          <component :is="button.icon" />
        </n-icon>
      </template>
      {{ button.label }}
    </n-button>
  </n-button-group>
</template>

<script>
import { defineComponent, inject, h } from 'vue'
import {NButtonGroup, NButton, NIcon, useDialog} from 'naive-ui'
import { Bold, Italic, Underline, Strikethrough, Photo, ClearFormatting, Code } from '@vicons/tabler'
import CodeMirror from 'vue-codemirror6'
import { html } from '@codemirror/lang-html'

export default defineComponent({
  name: 'FormattingButtons',
  components: {
    NButtonGroup,
    NButton,
    NIcon,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Photo,
    ClearFormatting,
    Code
  },
  setup() {
    const squireEditor = inject('squireEditor')
    const dialog = useDialog();

    const buttons = [
      { action: 'bold', icon: Bold, label: 'Bold' },
      { action: 'italic', icon: Italic, label: 'Italic' },
      { action: 'underline', icon: Underline, label: 'Underline' },
      { action: 'strikethrough', icon: Strikethrough, label: 'Strikethrough' },
      { action: 'insertImage', icon: Photo, label: 'Insert Image' },
      { action: 'removeFormat', icon: ClearFormatting, label: 'Remove Formatting' },
      { action: 'previewHtml', icon: Code, label: 'HTML Preview' }
    ]

    const formatText = (action) => {
      if (squireEditor.value) {
        squireEditor.value.focus();
        switch (action) {
          case 'bold':
            squireEditor.value.bold();
            break;
          case 'italic':
            squireEditor.value.italic();
            break;
          case 'underline':
            squireEditor.value.underline();
            break;
          case 'strikethrough':
            squireEditor.value.strikethrough();
            break;
          case 'removeFormat':
            squireEditor.value.removeAllFormatting();
            break;
          default:
            break;
        }
      }
    }

    const insertImage = () => {
      const imageUrl = prompt("Enter image URL:");
      if (imageUrl && squireEditor.value) {
        squireEditor.value.insertImage(imageUrl);
      }
    }

    const previewHtml = () => {
      if (squireEditor.value) {
        const htmlContent = squireEditor.value.getHTML();
        dialog.create({
          title: 'HTML Preview',
          style: 'width: 1024px',
          bordered: true,
          content: () => h('div', {
            style: {overflow: 'auto', maxHeight: '600px', marginBottom: '40px'}
          }, [
            h(CodeMirror, {
              modelValue: htmlContent,
              basic: true,
              lang: html(),
              dark: false,
              style: {width: '100%'},
              readOnly: true,
              extensions: [
                html()
              ],
            }),
          ]),
        });
      }
    }

    const handleClick = (action) => {
      switch (action) {
        case 'insertImage':
          insertImage();
          break;
        case 'previewHtml':
          previewHtml();
          break;
        default:
          formatText(action);
      }
    }

    return {
      buttons,
      handleClick
    }
  }
})
</script>