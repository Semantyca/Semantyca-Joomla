<template>
  <div class="container mt-3">
    <div class="row">
      <div class="col-md-6">
        <div class="header-container">
          <h3>{{ store.translations.AVAILABLE_ARTICLES }}</h3>
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
          <h3>{{ store.translations.SELECTED_ARTICLES }}</h3>
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

<script>
import {onMounted, ref} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import {useNewsletterStore} from "../stores/newsletterStore";
import {NButton, NButtonGroup, NSkeleton, useMessage} from "naive-ui";
import {useTemplateStore} from "../stores/templateStore";
import draggable from 'vuedraggable';
import {useArticleStore} from "../stores/articleStore";
import {buildContent, getWrappedContent} from '../utils/msgGen';

export default {
  name: 'Composer',
  components: {
    Editor,
    NSkeleton,
    NButton,
    NButtonGroup,
    draggable
  },

  setup(props, {emit}) {
    const articles = ref([]);
    const isLoading = ref(false);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);
    const activeTabName = ref('Composer');
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleString('default', {
      month: 'long'
    }).toUpperCase();
    const currentDateFormatted = `${currentMonth} ${currentYear}`;
    const articleStore = useArticleStore();
    const store = useGlobalStore();
    const templateStore = useTemplateStore();
    const newsLetterStore = useNewsletterStore();
    const message = useMessage();

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
      } catch (error) {
        console.error("Error in mounted hook:", error);
      }
    });

    const onDragEnd = () => {
      articleStore.editorCont = buildContent(currentDateFormatted, currentYear, articleStore.selectedArticles);
    };

    const resetFunction = async () => {
      articleStore.selectedArticles = [];
      articleStore.editorCont = '';
      // resetColorIndex();
      await articleStore.fetchArticles('');
    };

    const copyContentToClipboard = () => {
      const completeHTML = getWrappedContent(articleStore.editorCont);
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
      const messageContent = getWrappedContent(articleStore.editorCont);
      newsLetterStore.currentNewsletterId = 0;
      emit('content-changed', messageContent);
      emit('change-tab', 'Newsletter');
    };

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
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      composerEditorConfig,
      activeTabName,
      debouncedFetchArticles,
      getWrappedContent,
      resetFunction,
      copyContentToClipboard,
      next,
    };
  }
};
</script>
