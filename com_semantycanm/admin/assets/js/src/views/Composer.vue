<template>
  <n-grid :cols="1" x-gap="12" y-gap="12" class="mt-1">
    <n-gi>
      <n-divider title-placement="left">Parameters</n-divider>
    </n-gi>
    <n-gi>
      <n-grid :cols="8">
        <n-gi span="8">
          <n-form-item
              label-placement="left"
              require-mark-placement="right-hanging"
              label-width="180px"
              :style="{ maxWidth: '800px' }"
              v-for="(field, fieldName) in customFields"
              :key="field.id"
              :label="field.caption"
          >
            <template v-if="field.type === 503">
              <div v-for="(color, i) in field.defaultValue" :key="i">
                <n-color-picker
                    :value="color"
                    :show-alpha="false"
                    :actions="['confirm', 'clear']"
                    @update:value="newValue => handleColorChange(field.name, i, newValue)"
                    style="margin-right: 5px; width: 80px"
                />
              </div>
            </template>
            <template v-else-if="field.type === 501">
              <n-input-number
                  v-model:value="field.defaultValue"
                  @update:value="newValue => handleFieldChange(field.name, newValue)"
                  style="width: 150px;"
              />
            </template>
            <template v-else>
              <n-input
                  v-model:value="field.defaultValue"
                  @update:value="newValue => handleFieldChange(field.name, newValue)"
                  style="width: 100%;"
              />
            </template>
          </n-form-item>
        </n-gi>
      </n-grid>
    </n-gi>
    <n-gi>
      <n-divider class="custom-divider" title-placement="left">{{ store.translations.AVAILABLE_ARTICLES }}</n-divider>
    </n-gi>
    <n-gi>
      <n-grid :cols="2" :x-gap="24">
        <n-gi span="1">
          <n-input
              type="text"
              id="articleSearchInput"
              class="form-control mb-2"
              placeholder="Search articles..."
              @input="debouncedFetchArticles"
          />
          <div v-if="loading" class="col-md-12">
            <n-skeleton text :repeat="5" height="40px" />
          </div>
          <div v-else class="col-md-12">
            <draggable
                class="list-group dragdrop-list-short"
                :list="composerStore.listPage.docs"
                group="articles"
                itemKey="id"
                @end="onDragEnd"
            >
              <template #item="{ element }">
                <div
                    class="list-group-item"
                    :key="element.id"
                    :id="element.id"
                    :title="element.title"
                    :data-url="element.url"
                    :data-category="element.category"
                    :data-intro="element.intro"
                >
                  <strong>{{ element.category }}</strong><br />{{ element.title }}
                </div>
              </template>
            </draggable>
          </div>
        </n-gi>
        <n-gi span="1">
          <div class="header-container"></div>
          <draggable
              class="list-group dragdrop-list"
              :list="composerStore.selectedArticles"
              group="articles"
              itemKey="id"
              @end="onDragEnd"
          >
            <template #item="{ element }">
              <div
                  class="list-group-item"
                  :key="element.id"
                  :id="element.id"
                  :title="element.title"
                  :data-url="element.url"
                  :data-category="element.category"
                  :data-intro="element.intro"
              >
                <strong>{{ element.category }}</strong><br />{{ element.title }}
              </div>
            </template>
          </draggable>
        </n-gi>
      </n-grid>
    </n-gi>

    <n-gi>
      <n-divider class="custom-divider" title-placement="left">Review</n-divider>
    </n-gi>
    <n-gi>
      <n-grid :cols="1" class="mt-3">
        <n-gi>
          <n-space>
            <n-button
                size="large"
                strong
                error
                secondary
                @click="resetFunction"
            >{{ store.translations.RESET }}</n-button>
            <n-button
                size="large"
                type="primary"
                @click="copyContentToClipboard"
            >{{ store.translations.COPY_CODE }}</n-button>
            <n-button
                size="large"
                type="primary"
                @click="preview"
            >{{ store.translations.PREVIEW }}</n-button>
          </n-space>
        </n-gi>
        <n-gi class="mt-4">
          <n-button-group>
            <n-button @click="formatText('bold')" secondary>
              <template #icon>
                <n-icon>
                  <Bold />
                </n-icon>
              </template>
              Bold
            </n-button>
            <n-button @click="formatText('italic')" secondary>
              <template #icon>
                <n-icon>
                  <Italic />
                </n-icon>
              </template>
              Italic
            </n-button>
            <n-button @click="formatText('underline')" secondary>
              <template #icon>
                <n-icon>
                  <Underline />
                </n-icon>
              </template>
              Underline
            </n-button>
            <n-button @click="formatText('strikethrough')" secondary>
              <template #icon>
                <n-icon>
                  <Strikethrough />
                </n-icon>
              </template>
              Strikethrough
            </n-button>
            <n-button @click="previewHtml" secondary disabled>
              <template #icon>
                <n-icon>
                  <Code />
                </n-icon>
              </template>
              HTML Preview
            </n-button>
          </n-button-group>
        </n-gi>
        <n-gi>
          <div
              id="squire-editor"
              style="height: 400px; overflow-y: auto; border: 1px solid #a1bce0; min-height: 200px;"
          ></div>
        </n-gi>
      </n-grid>
    </n-gi>
  </n-grid>
</template>

<script>
import {computed, h, onMounted, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import HtmlWrapper from '../components/HtmlWrapper.vue';
import {
  NButton,
  NButtonGroup,
  NColorPicker,
  NDivider,
  NFormItem,
  NGi,
  NGrid,
  NIcon,
  NInput,
  NInputNumber,
  NSkeleton,
  NSpace,
  useDialog,
  useMessage
} from "naive-ui";
import {useTemplateStore} from "../stores/templateStore";
import draggable from 'vuedraggable';
import {useComposerStore} from "../stores/composerStore";
import DynamicBuilder from "../utils/DynamicBuilder";
import Squire from 'squire-rte';
import DOMPurify from 'dompurify';
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';
import {Bold, Code, Italic, Strikethrough, Underline} from '@vicons/tabler';

export default {
  name: 'Composer',
  components: {
    NSkeleton,
    NButtonGroup,
    NButton,
    NSpace,
    NDivider,
    NFormItem,
    NInput,
    NInputNumber,
    NColorPicker,
    NIcon,
    NGrid,
    NGi,
    draggable,
    Bold, Italic, Underline, Strikethrough, Code
  },

  setup() {
    const articles = ref([]);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);
    const activeTabName = ref('Composer');
    const composerStore = useComposerStore();
    const store = useGlobalStore();
    const templateStore = useTemplateStore();
    const msgPopup = useMessage();
    const dialog = useDialog();
    const customFields = computed(() => templateStore.availableCustomFields);
    const squireEditor = ref(null);
    const loading = ref(true);

    onMounted(async () => {
      try {
        loading.value = true;
        await composerStore.fetchEverything('', msgPopup);
        loading.value = false;
        window.DOMPurify = DOMPurify;
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
        updateEditorContent();
      } catch (error) {
        console.error("Error in mounted hook:", error);
      }
    });

    const onDragEnd = () => {
      if (composerStore.selectedArticles.length === 0) {
        composerStore.editorCont = '';
        return;
      }
      updateEditorContent();
    };

    const updateEditorContent = () => {
      const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
      dynamicBuilder.addVariable("articles", composerStore.selectedArticles);
      Object.keys(customFields.value).forEach((key) => {
        const fieldValue = customFields.value[key].defaultValue;
        dynamicBuilder.addVariable(key, fieldValue);
      });
      try {
        const cont = dynamicBuilder.buildContent();
        squireEditor.value.setHTML(cont);
      } catch (e) {
        msgPopup.error(e.message, {
          closable: true,
          duration: 100000
        });
      }
    };

    const resetFunction = async () => {
      composerStore.selectedArticles = [];
      composerStore.editorCont = '';
      await composerStore.fetchArticles('', true);
      updateEditorContent();
    };

    const copyContentToClipboard = () => {
      const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
      const completeHTML = dynamicBuilder.getWrappedContent(squireEditor.value.getHTML());
      const tempTextArea = document.createElement('textarea');
      tempTextArea.value = completeHTML;
      document.body.appendChild(tempTextArea);
      tempTextArea.select();
      const successful = document.execCommand('copy');
      if (successful) {
        msgPopup.info('HTML code copied to clipboard!');
      } else {
        msgPopup.warning('Failed to copy. Please try again.');
      }
      document.body.removeChild(tempTextArea);
    };

    const preview = () => {
      dialog.create({
        title: 'Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h(HtmlWrapper, {
          html: squireEditor.value.getHTML()
        }),
      });
    };

    const previewHtml = () => {
      dialog.create({
        title: 'HTML Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h('div', [
          h(CodeMirror, {
            modelValue: squireEditor.value.getHTML(),
            basic: true,
            lang: html(),
            dark: false,
            style: { width: '100%', height: '400px' },
            readOnly: true,
            extensions: [
              html()
            ],
          }),
        ]),
      });
    };

    const handleColorChange = (fieldName, index, newValue) => {
      customFields.value[fieldName].defaultValue[index] = newValue;
      updateEditorContent();
    };

    const handleFieldChange = (fieldName, newValue) => {
      customFields.value[fieldName].defaultValue = newValue;
      updateEditorContent();
    };

    const fetchArticlesDebounced = debounce(composerStore.fetchEverything, 300);

    const debouncedFetchArticles = (val) => {
      fetchArticlesDebounced(val);
    };

    const formatText = (format) => {
      if (squireEditor.value) {
        squireEditor.value.focus();
        switch (format) {
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
          default:
            break;
        }
      }
    };

    return {
      composerStore,
      articles,
      onDragEnd,
      store,
      customFields,
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      activeTabName,
      debouncedFetchArticles,
      resetFunction,
      copyContentToClipboard,
      preview,
      handleColorChange,
      handleFieldChange,
      formatText,
      previewHtml,
      loading
    };
  }
};
</script>
