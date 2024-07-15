<template>
  <template v-if="field.type === 503">
    <n-space>
      <n-color-picker
          v-for="(color, i) in field.defaultValue"
          :key="i"
          :value="color"
          :show-alpha="false"
          :actions="['confirm', 'clear']"
          @update:value="newValue => handleColorChange(i, newValue)"
          style="width: 80px"
      />
    </n-space>
  </template>
  <template v-else-if="field.type === 501">
    <n-input-number
        v-model:value="field.defaultValue"
        @update:value="newValue => handleFieldChange(newValue)"
        style="width: 150px;"
    />
  </template>
  <template v-else-if="field.type === 520">
    <n-select
        :value="selectedArticleIds"
        multiple
        filterable
        placeholder="Search articles"
        :options="articleOptions"
        :render-label="renderArticleOption"
        :render-tag="renderSelectedArticle"
        :clear-filter-after-select="true"
        style="width: 80%; max-width: 600px;"
        @update:value="handleArticleIdsChange"
    />
  </template>
  <template v-else-if="field.type === 521">
      <n-card size="small" title="Available Articles" style="width: 40%;">
        <template #header-extra>
          <n-input placeholder="Search..."></n-input>
        </template>
        <draggable
            :list="availableArticles"
            group="articles"
            item-key="id"
            @change="handleDragChange"
            class="draggable-list"
        >
          <template #item="{ element }">
            <n-card size="small" :title="element.category" class="draggable-item">
              <n-space vertical>
                <n-text strong>{{ element.title }}</n-text>
                <n-ellipsis :line-clamp="2" expand-trigger="click">
                  {{ decodeURIComponent(element.intro) }}
                </n-ellipsis>
              </n-space>
            </n-card>
          </template>
        </draggable>
      </n-card>
      <n-card size="small" title="Selected Articles" style="width: 40%;">
        <template #header-extra>
           <n-button type="default" disabled>Clear</n-button>
        </template>
        <draggable
            :list="selectedArticles"
            group="articles"
            item-key="id"
            @change="handleDragChange"
            class="draggable-list"
        >
          <template #item="{ element }">
            <n-card size="small" class="draggable-item">
              <n-space vertical>
                <n-text strong>{{ element.title }}</n-text>
                <n-ellipsis :line-clamp="2" expand-trigger="click">
                  {{ decodeURIComponent(element.intro) }}
                </n-ellipsis>
              </n-space>
            </n-card>
          </template>
        </draggable>
      </n-card>

  </template>
  <template v-else>
    <n-input
        v-model:value="field.defaultValue"
        @update:value="newValue => handleFieldChange(newValue)"
        style="width: 80%; max-width: 600px;"
    />
  </template>
</template>

<script>
import {computed, defineComponent, h} from 'vue';
import {NColorPicker, NInputNumber, NInput, NTag, NSelect, NSpace, NCard, NText, NEllipsis,NButton} from 'naive-ui';
import draggable from "vuedraggable";

export default defineComponent({
  name: 'DynamicFormField',
  components: {NColorPicker, NInputNumber, NInput, NSelect, draggable, NSpace, NCard, NTag, NText, NEllipsis, NButton},
  props: {
    field: {
      type: Object,
      required: true
    },
    articleOptions: {
      type: Array,
      required: true
    }
  },
  emits: ['update:field'],
  setup(props, {emit}) {
    const computedArticleOptions = computed(() => {
      if (props.field.type === 520 || props.field.type === 521) {
        return props.articleOptions.map(article => ({
          ...article,
          value: article.id
        }));
      }
      return [];
    });

    const selectedArticleIds = computed(() => {
      if (props.field.type === 520 || props.field.type === 521) {
        return props.field.defaultValue.map(article => article.value);
      }
      return [];
    })

    const handleColorChange = (index, newValue) => {
      const updatedField = {
        ...props.field,
        defaultValue: [...props.field.defaultValue],
      };
      updatedField.defaultValue[index] = newValue;
      emit('update:field', updatedField);
    };

    const handleFieldChange = (newValue) => {
      emit('update:field', {...props.field, defaultValue: newValue});
    };

    const renderArticleOption = (option) => {
      return h('div', [
        h('div', {style: 'font-weight: bold;'}, option.category),
        h('div', option.title)
      ]);
    };

    const handleArticleIdsChange = (selectedIds) => {
      const articles = selectedIds.map(id =>
          props.articleOptions.find(article => article.id === id)
      ).filter(article => article !== undefined);
      emit('update:field', {...props.field, defaultValue: articles});
    };

    const renderSelectedArticle = ({option, handleClose}) => {
      return h(NTag, {
        closable: true,
        onClose: handleClose,
      }, {
        default: () => option.title
      });
    };

    const availableArticles = computed(() => {
      if (props.field.type === 521) {
        return props.articleOptions.filter(article =>
            !props.field.defaultValue.some(selected => selected.id === article.id)
        );
      }
      return [];
    });

    const selectedArticles = computed({
      get: () => props.field.defaultValue || [],
      set: (value) => emit('update:field', {...props.field, defaultValue: value})
    });

    const handleDragChange = () => {
      emit('update:field', {...props.field, defaultValue: selectedArticles.value});
    };


    return {
      articleOptions: computedArticleOptions,
      selectedArticleIds,
      handleColorChange,
      handleFieldChange,
      renderArticleOption,
      handleArticleIdsChange,
      renderSelectedArticle,
      availableArticles,
      selectedArticles,
      handleDragChange
    };
  }
})
</script>

<style scoped>
.draggable-list {
  height: 400px;
  overflow-y: auto;
}

.draggable-item {
  margin-bottom: 2px;
  cursor: move;
}
</style>