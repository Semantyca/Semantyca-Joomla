<template>
  <n-divider title-placement="left">Parameters</n-divider>
  <div class="row">
    <div class="col-8">
      <n-form-item
          label-placement="left"
          require-mark-placement="right-hanging"
          label-width="auto"
          :style="{
                    maxWidth: '800px'
                }"
          v-for="(field, index) in availableCustomFields"
          :key="field.id"
          :label="field.caption"
      >
        <div v-for="(colorObject, index) in editableColors" :key="index">
          <n-color-picker v-model:value="colorObject.value"
                          :show-alpha="false"
                          @update:value="handleColorChange"
                          style="margin-right: 5px; width: 80px"/>
        </div>
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

<script>import {computed, onMounted, ref, watch} from 'vue';
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
  NSelect,
  NSkeleton,
  NTag,
  useMessage
} from "naive-ui";
import {useTemplateStore} from "../stores/templateStore";
import draggable from 'vuedraggable';
import {useArticleStore} from "../stores/articleStore";
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
    const articleStore = useArticleStore();
    const store = useGlobalStore();
    const templateStore = useTemplateStore();
    const newsLetterStore = useNewsletterStore();
    const message = useMessage();
    const availableCustomFields = computed(() => {
      return templateStore.doc.customFields.filter(field => field.isAvailable === 1);
    });
    const editableColors = ref([]);

    const knownIndex = ref(0); //
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
        await articleStore.fetchArticles('');
        await templateStore.getTemplate('classic', message);
        parseColors();
      } catch (error) {
        console.error("Error in mounted hook:", error);
      }
    });


    const onDragEnd = () => {
      const colors = editableColors.value.map(colorObject => colorObject.value);
      console.log('colors', colors)
      dynamicBuilder.addVariable("articles", articleStore.selectedArticles);
      dynamicBuilder.addVariable("bannerUrl", 'http://localhost/joomla/images/powered_by.png#joomlaImage://local-images/powered_by.png?width=294&height=44');
      dynamicBuilder.addVariable("maxContentDisplay", 5);
      dynamicBuilder.addVariable("colorList", colors);
      articleStore.editorCont = dynamicBuilder.buildContent();
    };


    const resetFunction = async () => {
      articleStore.selectedArticles = [];
      articleStore.editorCont = '';
      await articleStore.fetchArticles('');
    };

    const copyContentToClipboard = () => {
      const completeHTML = dynamicBuilder.getWrappedContent(articleStore.editorCont);
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
      const messageContent = dynamicBuilder.getWrappedContent(articleStore.editorCont);
      newsLetterStore.currentNewsletterId = 0;
      emit('content-changed', messageContent);
      emit('change-tab', 'Newsletter');
    };


    const parseColors = () => {
      if (availableCustomFields.value.length > 0 && availableCustomFields.value[knownIndex.value]) {
        try {
          const colorArray = JSON.parse(availableCustomFields.value[knownIndex.value].defaultValue);
          editableColors.value = colorArray.map(color => ({value: color}));
        } catch (error) {
          console.error("Error parsing color JSON", error);
          editableColors.value = [];
        }
      } else {
        console.log("Data not ready or index out of bounds");
      }
    };


    const handleColorChange = () => {
      const colors = editableColors.value.map(colorObject => colorObject.value);
      dynamicBuilder.addVariable("colorList", colors);
      dynamicBuilder.addVariable("articles", articleStore.selectedArticles);
      dynamicBuilder.addVariable("bannerUrl", 'http://localhost/joomla/images/powered_by.png#joomlaImage://local-images/powered_by.png?width=294&height=44');
      dynamicBuilder.addVariable("maxContentDisplay", 5);
      articleStore.editorCont = dynamicBuilder.buildContent();
    };


    watch([() => availableCustomFields, knownIndex], () => {
      parseColors();
    });

    const fetchArticlesDebounced = debounce(articleStore.fetchArticles, 300);
    const debouncedFetchArticles = (event) => {
      fetchArticlesDebounced(event.target.value);
    };

    return {
      articleStore,
      articles,
      onDragEnd,
      isLoading,
      store,
      availableCustomFields,
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      composerEditorConfig,
      activeTabName,
      debouncedFetchArticles,
      resetFunction,
      copyContentToClipboard,
      next,
      editableColors,
      handleColorChange
    };
  }
};
</script>
