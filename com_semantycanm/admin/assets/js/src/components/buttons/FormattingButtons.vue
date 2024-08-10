<template>
  <n-button-group>
    <n-tooltip v-for="button in buttons" :delay="1000" :key="button.action" trigger="hover" :show-arrow="false">
      <template #trigger>
        <n-button @click="handleClick(button.action)" secondary>
          <template #icon>
            <n-icon>
              <component :is="button.icon" />
            </n-icon>
          </template>
          {{ button.label }}
        </n-button>
      </template>
    </n-tooltip>
  </n-button-group>
</template>

<script>
import { defineComponent, inject, h } from 'vue'
import { NButtonGroup, NButton, NIcon, useDialog, NTooltip } from 'naive-ui'
import { Bold, Italic, Underline, Strikethrough, Photo, ClearFormatting, Code } from '@vicons/tabler'
import CodeMirror from 'vue-codemirror6'
import { html } from '@codemirror/lang-html'
import { EditorView } from '@codemirror/view'
import DynamicBuilder from "../../utils/DynamicBuilder";

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
    Code,
    NTooltip
  },
  props: {
    templateStore: {
      type: Object,
      required: true
    },
    modelRef: {
      type: Object,
      required: true
    }
  },
  setup(props) {
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
        try {
          // Initialize DynamicBuilder with the applied template document
          const dynamicBuilder = new DynamicBuilder(props.templateStore.appliedTemplateDoc);

          // Add all custom fields as variables to the DynamicBuilder
          Object.keys(props.modelRef.customFields).forEach((key) => {
            const field = props.modelRef.customFields[key];
            dynamicBuilder.addVariable(field.name, field.defaultValue || '');
          });

          // Add any other necessary variables or data to the DynamicBuilder
          dynamicBuilder.addVariable('subject', props.modelRef.subject || '');
          dynamicBuilder.addVariable('testEmail', props.modelRef.testEmail || '');

          // Build the complete content using DynamicBuilder
          const wrappedContent = dynamicBuilder.getWrappedContent();

          // Display the wrapped content in the preview dialog
          dialog.create({
            title: 'HTML Preview',
            style: 'width: 1024px',
            bordered: true,
            content: () => h('div', {
              style: {overflow: 'auto', maxHeight: '600px', marginBottom: '40px'}
            }, [
              h(CodeMirror, {
                modelValue: wrappedContent,
                basic: true,
                lang: html(),
                dark: false,
                style: {width: '100%'},
                readOnly: true,
                extensions: [
                  html(),
                  EditorView.lineWrapping
                ],
                editorProps: {
                  attributes: {
                    class: 'cm-editor-readonly'
                  }
                }
              }),
            ]),
          });
        } catch (error) {
          // Handle any errors that occur during the content wrapping and preview process
          console.error("Error in previewHtml:", error);
          /* msgPopup.error(`Failed to preview HTML: ${error.message}`, {
            closable: true,
            duration: 10000,
          }); */
        }
      } else {
        console.error("squireEditor instance is not initialized.");
      }
    };

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

<style>
.cm-editor-readonly .cm-scroller {
  font-family: monospace;
}
</style>
