<template>
  <div id="loadingSpinner" class="loading-spinner"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div class="header-container">
          <h3>{{ AVAILABLE_ARTICLES }}</h3>
          <div id="composerSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status"
               style="display: none;">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <input type="text" id="articleSearchInput" class="form-control mb-2" placeholder="Search articles...">
        <ul ref="articlesListRef" id="articles" class="list-group dragdrop-list-short">
          <li v-for="article in articles" :key="article.id" class="list-group-item"
              :id="article.id" :title="article.title" :data-url="article.url"
              :data-category="article.category" :data-intro="article.intro">
            <strong>{{ article.category }}</strong><br>{{ article.title }}
          </li>
        </ul>
      </div>
      <div class="col-md-6">
        <div class="header-container">
          <h3>{{ SELECTED_ARTICLES }}</h3>
        </div>
        <ul ref="selectedArticlesListRef" id="selectedArticles" class="list-group dragdrop-list">
          <li v-for="selectedArticle in state.selectedArticles" :key="selectedArticle.id"
              class="list-group-item"
              :id="selectedArticle.id" :title="selectedArticle.title" :data-url="selectedArticle.url"
              :data-category="selectedArticle.category" :data-intro="selectedArticle.intro">
            <strong>{{ selectedArticle.category }}</strong><br>{{ selectedArticle.title }}
          </li>
        </ul>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="btn-group">
          <button @click="resetFunction" class="btn" style="background-color: #152E52; color: white;">RESET</button>
          <button @click="copyContentToClipboard" class="btn btn-info mb-2">COPY_CODE</button>
          <button @click="next" class="btn btn-info mb-2">NEXT</button>
        </div>
        <editor
            :init="composerEditorConfig"
            v-model="state.editorCont"></editor>
      </div>
    </div>
  </div>
</template>

<script>
import {nextTick, onMounted, reactive, ref} from 'vue';
import Editor from '@tinymce/tinymce-vue';


export default {
  components: {
    Editor
  },
  props: {
    AVAILABLE_ARTICLES: String,
    SELECTED_ARTICLES: String,
    RESET: String,
    COPY_CODE: String,
    NEXT: String,
  },
  setup() {
    const articles = ref([]);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);

    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleString('default', {
      month: 'long'
    }).toUpperCase();
    const currentDateFormatted = `${currentMonth} ${currentYear}`;

    const state = reactive({
      editorCont: '',
      selectedArticles: []
    });

    const composerEditorConfig = {
      apiKey: window.tinymceLic,
      model_url: 'components/com_semantycanm/assets/bundle/models/dom/model.js',
      skin_url: 'components/com_semantycanm/assets/bundle/skins/ui/oxide',
      content_css: 'components/com_semantycanm/assets/bundle/skins/content/default/content.css',
      menubar: false,
      statusbar: false,
      relative_urls: false,
      remove_script_host: false,
      plugins: 'table code',
      toolbar: 'code paste removeformat bold italic underline indent outdent tablecellbackgroundcolor ',
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

    const resetFunction = async () => {
      await fetchArticles('');
      state.editorCont = '';
      selectedArticlesListRef.value.innerHTML = '';
    };

    const copyContentToClipboard = () => {
      const completeHTML = getWrappedContent(state.editorCont);
      const tempTextArea = document.createElement('textarea');
      tempTextArea.value = completeHTML;
      document.body.appendChild(tempTextArea);
      tempTextArea.select();
      const successful = document.execCommand('copy');
      if (successful) {
        showAlertBar('HTML code copied to clipboard!', "info");
      } else {
        showAlertBar('Failed to copy. Please try again.', "warning");
      }
      document.body.removeChild(tempTextArea);
    };

    const next = () => {
      const messageContent = document.getElementById('messageContent');
      messageContent.value = getWrappedContent(state.editorCont);
      $('#nav-newsletters-tab').tab('show');
    };

    const fetchArticles = async (searchTerm) => {
      startLoading('loadingSpinner');
      try {
        const url = 'index.php?option=com_semantycanm&task=Article.search&q=' + encodeURIComponent(searchTerm);
        const response = await fetch(url);
        if (!response.ok) {
          throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
        }
        const data = await response.json();
        articles.value = data.data.map(a => ({
          id: a.id,
          title: a.title,
          url: a.url,
          category: a.category,
          intro: encodeURIComponent(a.introtext)
        }));
        stopLoading('loadingSpinner');
      } catch (error) {
        console.error(`Problem fetching articles:`, error);
        stopLoading('loadingSpinner');
      }
    };

    const applyAndDropSet = (lists) => {
      lists.forEach(list => {
        Sortable.create(list, {
          group: {
            name: 'shared',
            pull: true,
            put: true
          },
          animation: 150,
          sort: false,
          onEnd: (evt) => {
            updateComposerContent();
          }
        });
      });
    };

    const updateComposerContent = () => {
      state.editorCont = buildContent(currentDateFormatted, currentYear);
    };

    onMounted(async () => {
      await fetchArticles('');
      nextTick(() => {
        applyAndDropSet([articlesListRef.value, selectedArticlesListRef.value]);
      });
    });


    return {
      articles,
      state,
      articlesListRef,
      selectedArticlesListRef,
      composerRef,
      composerEditorConfig,

      resetFunction,
      copyContentToClipboard,
      next,
    };
  },

  mounted() {
    console.log('CSS Bundle URL:', window.cssBundleName);
  }

};
</script>
