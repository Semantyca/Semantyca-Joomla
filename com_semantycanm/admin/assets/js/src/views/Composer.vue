<template>
  <div class="container mt-3">
    <n-divider title-placement="left">Parameters</n-divider>
    <div class="row">
      <div class="col-8">
        <n-form-item
            label-placement="left"
            require-mark-placement="right-hanging"
            label-width="180px"
            :style="{ maxWidth: '800px' }"
            v-for="(field, fieldName) in fields"
            :key="field.id"
            :label="field.caption"
        >
          <template v-if="field.type === 503">
            <div v-for="(color, i) in field.defaultValue" :key="i">
              <n-color-picker :value="color"
                              size="large"
                              :show-alpha="false"
                              :actions="['confirm','clear']"
                              @update:value="newValue => handleColorChange(field.name, i, newValue)"
                              style="margin-right: 5px; width: 80px"/>
            </div>
          </template>
          <template v-else-if="field.type === 501">
            <n-input-number v-model:value="field.defaultValue"
                            size="large"
                            style="width: 100px"
                            @update:value="newValue => handleFieldChange(field.name, newValue)"/>

          </template>
          <template v-else>
            <n-input v-model:value="field.defaultValue"
                     size="large"
                     @update:value="newValue => handleFieldChange(field.name, newValue)"/>

          </template>
        </n-form-item>
      </div>
    </div>
    <n-divider class="custom-divider" title-placement="left">{{ store.translations.AVAILABLE_ARTICLES }}</n-divider>
    <div class="row">
      <div class="col-md-6">
        <div class="header-container">
          <div id="composerSpinner"
               class="spinner-border text-info spinner-grow-sm mb-2"
               role="status"
               style="display: none;">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <input type="text" id="articleSearchInput" class="form-control mb-2" placeholder="Search articles..."
               @input="debouncedFetchArticles">
        <draggable class="list-group dragdrop-list-short"
                   :list="articleStore.listPage.docs"
                   group="articles"
                   itemKey="id"
                   @end="onDragEnd">
          <template #item="{ element }">
            <div class="list-group-item" :key="element.id" :id="element.id" :title="element.title"
                 :data-url="element.url" :data-category="element.category" :data-intro="element.intro">
              <strong>{{ element.category }}</strong><br>{{ element.title }}
            </div>
          </template>
        </draggable>
      </div>
      <div class="col-md-6">
        <div class="header-container">
          <!--          <h3>{{ store.translations.SELECTED_ARTICLES }}</h3>-->
        </div>
        <draggable
            class="list-group dragdrop-list"
            :list="articleStore.selectedArticles"
            group="articles" itemKey="id"
            @end="onDragEnd">
          <template #item="{ element }">
            <div class="list-group-item" :key="element.id" :id="element.id" :title="element.title"
                 :data-url="element.url" :data-category="element.category" :data-intro="element.intro">
              <strong>{{ element.category }}</strong><br>{{ element.title }}
            </div>
          </template>
        </draggable>
      </div>
    </div>
    <n-divider class="custom-divider" title-placement="left">Review</n-divider>
    <div class="row mt-3">
      <div class="col  d-flex align-items-center">
        <n-space>
          <n-button size="large"
                    strong
                    error
                    seconadry
                    @click="resetFunction">{{ store.translations.RESET }}
          </n-button>
          <n-button size="large"
                    type="primary"
                    @click="copyContentToClipboard">{{ store.translations.COPY_CODE }}
          </n-button>
          <n-button size="large"
                    type="primary"
                    @click="preview">{{ store.translations.PREVIEW }}
          </n-button>
        </n-space>
      </div>
      <div class="row mt-4">
        <div class="col">
          <n-button-group>
            <n-button @click="formatText('bold')" secondary>
              <template #icon>
                <n-icon>
                  <BandageOutline/>
                </n-icon>
              </template>
              Bold
            </n-button>
            <n-button @click="formatText('italic')" secondary>
              <template #icon>
                <n-icon>
                  <BeerOutline/>
                </n-icon>
              </template>
              Italic
            </n-button>
            <n-button @click="formatText('underline')" secondary>
              <template #icon>
                <n-icon>
                  <BoatOutline/>
                </n-icon>
              </template>
              Underline
            </n-button>
            <n-button @click="formatText('strikethrough')" secondary>
              <template #icon>
                <n-icon>
                  <Car/>
                </n-icon>
              </template>
              Strikethrough
            </n-button>
            <n-button @click="previewHtml" secondary disabled>
              <template #icon>
                <n-icon>
                  <CodeOutline/>
                </n-icon>
              </template>
              HTML Preview
            </n-button>
          </n-button-group>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <div id="squire-editor"
               style="height: 400px; overflow-y: auto; border: 1px solid #a1bce0; min-height: 200px;"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {computed, h, onMounted, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import {useNewsletterStore} from "../stores/newsletterStore";
import HtmlWrapper from '../components/HtmlWrapper.vue';
import {
  NButton,
  NButtonGroup,
  NColorPicker,
  NDivider,
  NFormItem,
  NIcon,
  NInput,
  NInputNumber,
  NSelect,
  NSkeleton,
  NSpace,
  NTag,
  useDialog,
  useMessage
} from "naive-ui";
import {BandageOutline, BeerOutline, BoatOutline, Car, CodeOutline} from '@vicons/ionicons5';
import {useTemplateStore} from "../stores/templateStore";
import draggable from 'vuedraggable';
import {useComposerStore} from "../stores/composerStore";
import DynamicBuilder from "../utils/DynamicBuilder"
import Squire from 'squire-rte';
import DOMPurify from 'dompurify';
import CodeMirror from 'vue-codemirror6';
import {html} from '@codemirror/lang-html';

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
    NSelect,
    NTag,
    NColorPicker,
    draggable,
    NIcon,
    BandageOutline, BeerOutline, BoatOutline, Car, CodeOutline
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
    const newsLetterStore = useNewsletterStore();
    const message = useMessage();
    const dialog = useDialog();
    const fields = computed(() => composerStore.formCustomFields);
    const dynamicBuilder = new DynamicBuilder(templateStore.doc);
    const squireEditor = ref(null);


    onMounted(async () => {
      try {
        await composerStore.fetchArticles('', message);
        window.DOMPurify = DOMPurify;
        squireEditor.value = new Squire(document.getElementById('squire-editor'));
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

    const resetFunction = async () => {
      composerStore.selectedArticles = [];
      composerStore.editorCont = '';
      await composerStore.fetchArticles('');
    };

    const copyContentToClipboard = () => {
      //const completeHTML = dynamicBuilder.getWrappedContent(composerStore.editorCont);
      const completeHTML = dynamicBuilder.getWrappedContent(squireEditor.value.getHTML());
      const tempTextArea = document.createElement('textarea');
      tempTextArea.value = completeHTML;
      document.body.appendChild(tempTextArea);
      tempTextArea.select();
      const successful = document.execCommand('copy');
      if (successful) {
        message.info('HTML code copied to clipboard!');
      } else {
        message.warning('Failed to copy. Please try again.');
      }
      document.body.removeChild(tempTextArea);
    };

    const preview = () => {
      dialog.create({
        title: 'Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h(HtmlWrapper, {
          //html: composerStore.editorCont
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
            style: {width: '100%', height: '400px'},
            readOnly: true,
            extensions: [
              html()
            ],
          }),
        ]),
      });
    };

    /*const previewHtml = () => {
      const htmlSource = squireEditor.value.getHTML();
      dialog.create({
        title: 'HTML Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h('div', [
          h(CodeMirror, {
            modelValue: htmlSource,
            basic: true,
            lang: html(),
            dark: false,
            style: { width: '100%', height: '400px' },
            readOnly: true,
            extensions: [
              html(),
              //EditorView.lineWrapping,
            ],
          }),
        ]),
      });
    };*/

    const handleColorChange = (fieldName, index, newValue) => {
      fields.value[fieldName].defaultValue[index] = newValue;
      updateEditorContent();
    };

    const handleFieldChange = (fieldName, newValue) => {
      console.log(fieldName + ' ' + newValue)
      fields.value[fieldName].defaultValue = newValue;
      updateEditorContent();
    };


    const updateEditorContent = () => {
      dynamicBuilder.addVariable("articles", composerStore.selectedArticles);
      Object.keys(fields.value).forEach((key) => {
        const fieldValue = fields.value[key].defaultValue;
        dynamicBuilder.addVariable(key, fieldValue);
      });
      //composerStore.editorCont = dynamicBuilder.buildContent();
      squireEditor.value.setHTML(dynamicBuilder.buildContent());
    };


    const fetchArticlesDebounced = debounce(composerStore.fetchArticles, 300);
    const debouncedFetchArticles = (event) => {
      fetchArticlesDebounced(event.target.value);
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
      articleStore: composerStore,
      articles,
      onDragEnd,
      store,
      fields,
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
    };
  }
};
</script>
