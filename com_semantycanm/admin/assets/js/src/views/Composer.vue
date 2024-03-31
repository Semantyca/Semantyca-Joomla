<template>
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
  <n-divider class="custom-divider" title-placement="left">Articles</n-divider>
  <div class="container mt-3">
    <div class="row">
      <div class="col-md-6">
        <div class="header-container">
          <!--          <h3>{{ store.translations.AVAILABLE_ARTICLES }}</h3>-->
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
                    @click="next">{{ store.translations.NEXT }}
          </n-button>
          <n-button size="large"
                    type="primary"
                    @click="preview">{{ store.translations.PREVIEW }}
          </n-button>
        </n-space>
      </div>
      <div class="row mt-3">
        <editor
            :api-key="store.tinyMceLic"
            :init="composerEditorConfig"
            v-model="articleStore.editorCont"></editor>
      </div>
    </div>
  </div>
</template>

<script>
import {computed, h, onMounted, ref} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import {useNewsletterStore} from "../stores/newsletterStore";
import HtmlWrapper from '../components/HtmlWrapper.vue';
import {
  NButton,
  NColorPicker,
  NDivider,
  NFormItem,
  NInput,
  NInputNumber,
  NSelect,
  NSkeleton,
  NSpace,
  NTag,
  useDialog,
  useMessage
} from "naive-ui";
import {useTemplateStore} from "../stores/templateStore";
import draggable from 'vuedraggable';
import {useComposerStore} from "../stores/composerStore";
import DynamicBuilder from "../utils/DynamicBuilder"

export default {
  name: 'Composer',
  components: {
    Editor,
    NSkeleton,
    NButton,
    NSpace,
    NDivider,
    NFormItem,
    NInput,
    NInputNumber,
    NSelect,
    NTag,
    NColorPicker,
    draggable
  },

  setup(props, {emit}) {
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

    const composerEditorConfig = {
      apiKey: store.tinymceLic,
      model_url: 'components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: 'components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: 'components/com_semantycanm/assets/bundle/skins/content/default/content.css',
      menubar: false,
      statusbar: false,
      relative_urls: false,
      remove_script_host: false,
      selector: 'textarea',
      plugins: 'fullscreen table code',
      toolbar: 'fullscreen code paste removeformat bold italic underline indent outdent tablecellbackgroundcolor ',
      table_advtab: false,
      table_cell_advtab: false,
      table_row_advtab: false,
      table_resize_bars: false,
      table_background_color_map: [
        {title: 'Red', value: 'FF0000'},
        {title: 'White', value: 'FFFFFF'},
        {title: 'Yellow', value: 'F1C40F'}
      ],
    };

    onMounted(async () => {
      try {
        await composerStore.fetchArticles('', message);
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
      const completeHTML = dynamicBuilder.getWrappedContent(composerStore.editorCont);
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

    const next = () => {
      const messageContent = dynamicBuilder.getWrappedContent(composerStore.editorCont);
      newsLetterStore.currentNewsletterId = 0;
      emit('content-changed', messageContent);
      emit('change-tab', 'Newsletter');
    };

    const preview = () => {
      dialog.create({
        title: 'Preview',
        style: 'width: 800px',
        bordered: true,
        content: () => h(HtmlWrapper, {
          html: composerStore.editorCont
        }),
      });
    };

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
      composerStore.editorCont = dynamicBuilder.buildContent();
    };


    const fetchArticlesDebounced = debounce(composerStore.fetchArticles, 300);
    const debouncedFetchArticles = (event) => {
      fetchArticlesDebounced(event.target.value);
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
      composerEditorConfig,
      activeTabName,
      debouncedFetchArticles,
      resetFunction,
      copyContentToClipboard,
      next,
      preview,
      handleColorChange,
      handleFieldChange
    };
  }
};
</script>
