<template>
  <n-divider title-placement="left">Parameters</n-divider>
  <div class="row">
    <div class="col-8">
      <n-form-item
          label-placement="left"
          require-mark-placement="right-hanging"
          label-width="180px"
          :style="{ maxWidth: '800px' }"
          v-for="(field, index) in formCustomFields"
          :key="field.id"
          :label="field.caption"
      >
        <template v-if="field.type === 503">
          <div v-for="(color, i) in field.defaultValue" :key="i">
            <n-color-picker :value="color"
                            size="large"
                            :show-alpha="false"
                            :actions="['confirm','clear']"
                            @update:value="newValue => handleFieldChange(i, newValue)"
                            style="margin-right: 5px; width: 80px"/>
          </div>
        </template>
        <template v-else-if="field.type === 501">
          <n-input-number v-model:value="field.defaultValue"
                          size="large"
                          style="width: 100px"/>
        </template>
        <template v-else>
          <n-input v-model:value="field.defaultValue"
                   size="large"/>
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
        <div v-if="isLoading">
          <n-skeleton text :repeat="4" size="medium"/>
          <n-skeleton text style="width: 60%" size="medium"/>
        </div>
        <draggable v-else
                   class="list-group dragdrop-list-short"
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
        <n-button-group>
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
        </n-button-group>
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

<script>import {onMounted, ref} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import {useNewsletterStore} from "../stores/newsletterStore";
import {
  NButton,
  NButtonGroup,
  NColorPicker,
  NDivider,
  NFormItem,
  NInput,
  NInputNumber,
  NSelect,
  NSkeleton,
  NTag,
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
    NButtonGroup,
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
    const isLoading = ref(false);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);
    const activeTabName = ref('Composer');
    const composerStore = useComposerStore();
    const store = useGlobalStore();
    const templateStore = useTemplateStore();
    const newsLetterStore = useNewsletterStore();
    const message = useMessage();
    const formCustomFields = ref([]);
    const colorIndex = 0;
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
        await composerStore.fetchArticles('');
        await templateStore.getTemplate('classic', message);
        formCustomFields.value = templateStore.doc.availableCustomFields.map(field => {
          if (field.type === 503) {
            return {
              ...field,
              defaultValue: [...field.defaultValue]
            };
          } else {
            return {
              ...field
            };
          }
        });

      } catch (error) {
        console.error("Error in mounted hook:", error);
      }
    });


    const onDragEnd = () => {
      if (composerStore.selectedArticles.length === 0) {
        composerStore.editorCont = '';
        return;
      }
      const colors = formCustomFields.value[colorIndex].defaultValue;
      dynamicBuilder.addVariable("articles", composerStore.selectedArticles);
      dynamicBuilder.addVariable("bannerUrl", 'http://localhost/joomla/images/powered_by.png#joomlaImage://local-images/powered_by.png?width=294&height=44');
      dynamicBuilder.addVariable("maxContentDisplay", 5);
      dynamicBuilder.addVariable("colorList", colors);
      composerStore.editorCont = dynamicBuilder.buildContent();
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


    const handleFieldChange = (fieldIndex, newValue) => {
      formCustomFields.value[colorIndex].defaultValue[fieldIndex] = newValue;
      const colors = formCustomFields.value[colorIndex].defaultValue;
      dynamicBuilder.addVariable("colorList", colors);
      dynamicBuilder.addVariable("articles", composerStore.selectedArticles);
      dynamicBuilder.addVariable("bannerUrl", 'http://localhost/joomla/images/powered_by.png#joomlaImage://local-images/powered_by.png?width=294&height=44');
      dynamicBuilder.addVariable("maxContentDisplay", 5);
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
      isLoading,
      store,
      formCustomFields,
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      composerEditorConfig,
      activeTabName,
      debouncedFetchArticles,
      resetFunction,
      copyContentToClipboard,
      next,
      handleFieldChange
    };
  }
};
</script>
