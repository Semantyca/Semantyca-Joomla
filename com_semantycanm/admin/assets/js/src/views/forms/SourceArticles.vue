<template>
  <n-select
      :value="selectedArticleIds"
      multiple
      filterable
      placeholder="Search articles"
      :options="composerStore.articleOptions"
      :render-label="renderArticleOption"
      :render-tag="renderSelectedArticle"
      :clear-filter-after-select="true"
      style="width: 80%; max-width: 600px;"
      @update:value="updateSelectedArticles"
  />
</template>

<script>
import { defineComponent, h } from 'vue'
import { NSelect, NTag } from 'naive-ui'
import { useComposerStore } from "../../stores/composer/composerStore";

export default defineComponent({
  name: 'SourceArticles',
  components: { NSelect },
  props: {
    selectedArticleIds: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:selectedArticleIds'],
  setup(props, { emit }) {
    const composerStore = useComposerStore();

    const renderArticleOption = (option) => {
      return h('div', [
        h('div', {style: 'font-weight: bold;'}, option.category),
        h('div', option.title)
      ]);
    };

    const updateSelectedArticles = (selectedIds) => {
      const selectedArticles = selectedIds.map(id => {
        return composerStore.articleOptions.find(article => article.value === id);
      });
      emit('update:selectedArticleIds', selectedArticles);
    };

    const renderSelectedArticle = ({option, handleClose}) => {
      return h(NTag, {
        closable: true,
        onClose: handleClose,
      }, {
        default: () => option.title
      });
    };

    return {
      composerStore,
      renderArticleOption,
      updateSelectedArticles,
      renderSelectedArticle
    };
  }
})
</script>