<template>
  <div id="loadingSpinner" class="loading-spinner"></div>
  <div class="container mt-1">
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
        <ul v-else ref="articlesListRef" id="articlesUlElement" class="list-group dragdrop-list-short">
          <li v-for="article in articles" :key="article.id" class="list-group-item"
              :id="article.id" :title="article.title" :data-url="article.url"
              :data-category="article.category" :data-intro="article.intro">
            <strong>{{ article.category }}</strong><br>{{ article.title }}
          </li>
        </ul>
      </div>
      <div class="col-md-6">
        <div class="header-container">
          <h3>{{ store.translations.SELECTED_ARTICLES }}</h3>
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
          <button @click="resetFunction" class="btn" style="background-color: #152E52; color: white;">
            {{ store.translations.RESET }}
          </button>
          <button @click="copyContentToClipboard" class="btn btn-info mb-2">{{
              store.translations.COPY_CODE
            }}
          </button>
          <button @click="next" class="btn btn-info mb-2">{{ store.translations.NEXT }}</button>
        </div>
        <editor
            :api-key="store.tinyMceLic"
            :init="composerEditorConfig"
            v-model="state.editorCont"></editor>
      </div>
    </div>
  </div>
</template>

<script>
import {nextTick, onMounted, reactive, ref} from 'vue';
import Editor from '@tinymce/tinymce-vue';
import {useGlobalStore} from "../stores/globalStore";
import {debounce} from 'lodash';
import {useNewsletterStore} from "../stores/newsletterStore";
import {NSkeleton} from "naive-ui";

export default {
  name: 'Composer',
  components: {
    Editor,
    NSkeleton
  },

  setup() {
    const articles = ref([]);
    const isLoading = ref(true);
    const articlesListRef = ref(null);
    const selectedArticlesListRef = ref(null);
    const composerRef = ref(null);
    const activeTabName = ref('Composer');
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleString('default', {
      month: 'long'
    }).toUpperCase();
    const currentDateFormatted = `${currentMonth} ${currentYear}`;
    const store = useGlobalStore();
    const newsletterStore = useNewsletterStore();
    const state = reactive({
      editorCont: '',
      selectedArticles: []
    });

    const composerEditorConfig = {
      apiKey: store.tinymceLic,
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
      newsletterStore.messageContent = getWrappedContent(state.editorCont);
      activeTabName.value = 'Newsletter';
    };


    const fetchArticles = async (searchTerm) => {
      startLoading('loadingSpinner');
      isLoading.value = true;
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
        isLoading.value = false;
      } catch (error) {
        console.error(`Problem fetching articles:`, error);
        stopLoading('loadingSpinner');
        isLoading.value = false;
      }
    };

    const fetchArticlesDebounced = debounce(fetchArticles, 300);
    const debouncedFetchArticles = (event) => {
      fetchArticlesDebounced(event.target.value);
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
          onEnd: () => {
            updateComposerContent();
          }
        });
      });
    };

    const updateComposerContent = () => {
      const v = buildContent(currentDateFormatted, currentYear);
      console.log(v);
      state.editorCont = v;
    };

    const getWrappedContent = (content) => {
      let template = Handlebars.compile(store.template.wrapper);
      let data = {
        content: content
      };
      return template(data);
    }

    const buildContent = (currentDateFormatted, currentYear) => {
      const selectedArticlesLi = document.querySelectorAll('#selectedArticles li');
      let articles = [];
      selectedArticlesLi.forEach((article) => {
        const articleId = article.id;
        const title = article.title;
        const url = normalizeUrl(article.dataset.url);
        let htmlContent = decodeURIComponent(article.dataset.intro);
        let intro = makeImageUrlsAbsolute(htmlContent);
        const category = article.dataset.category;

        let articleObj = {
          id: articleId,
          title: title,
          url: url,
          intro: intro,
          category: category,
          backgroundColor: getRandomWebSafeColor()
        };
        articles.push(articleObj);
      });

      Handlebars.registerHelper('lt', function (value1, value2) {
        return value1 < value2;
      });

      let template = Handlebars.compile(store.template.html);
      let data = {
        bannerUrl: store.template.banner,
        currentDateFormatted: currentDateFormatted,
        currentYear: currentYear,
        articles: articles,
        maxArticles: store.template.maxArticles,
        maxArticlesShort: store.template.maxArticlesShort
      };
      return template(data);
    }

    const getRandomWebSafeColor = () => {
      const safeValues = [0, 51, 102, 153, 204, 255];
      const red = safeValues[Math.floor(Math.random() * safeValues.length)];
      const green = safeValues[Math.floor(Math.random() * safeValues.length)];
      const blue = safeValues[Math.floor(Math.random() * safeValues.length)];
      return `#${red.toString(16).padStart(2, '0')}${green.toString(16).padStart(2, '0')}${blue.toString(16).padStart(2, '0')}`;
    }

    const makeImageUrlsAbsolute = (articleHtml) => {
      let parser = new DOMParser();
      let htmlDoc = parser.parseFromString(articleHtml, 'text/html');
      let images = htmlDoc.getElementsByTagName('img');

      for (let img of images) {
        let currentSrc = img.src;
        img.src = normalizeUrl(currentSrc);
        img.removeAttribute('loading');
        img.removeAttribute('data-path');
      }

      return htmlDoc.body.innerHTML;
    }

    const normalizeUrl = (url) => {
      if (url.includes('/administrator/')) {
        return url.replace('/administrator', '');
      }
      return url;
    }


    onMounted(async () => {
      await fetchArticles('');
      await store.getTemplate('classic');
      await nextTick(() => {
        applyAndDropSet([articlesListRef.value, selectedArticlesListRef.value]);
      });
    });

    return {
      articles,
      isLoading,
      state,
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
