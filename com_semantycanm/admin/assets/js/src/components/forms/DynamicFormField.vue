<template>
  <template v-if="field.type === 503">
    <div v-for="(color, i) in field.defaultValue" :key="i">
      <n-color-picker
          :value="color"
          :show-alpha="false"
          :actions="['confirm', 'clear']"
          @update:value="newValue => handleColorChange(i, newValue)"
          style="margin-right: 5px; width: 80px"
      />
    </div>
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
        :options="composerStore.articleOptions"
        :render-label="renderArticleOption"
        :render-tag="renderSelectedArticle"
        :clear-filter-after-select="true"
        style="width: 80%; max-width: 600px;"
        @update:value="handleArticleIdsChange"
    />
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
import {defineComponent, h, ref} from 'vue';
import { NColorPicker, NInputNumber, NInput, NTag, NSelect } from 'naive-ui';
import { useComposerStore } from "../../stores/composer/composerStore";

export default defineComponent({
  name: 'DynamicFormField',
  components: { NColorPicker, NInputNumber, NInput, NSelect },
  props: {
    field: {
      type: Object,
      required: true
    }
  },
  emits: ['update:field'],
  setup(props, { emit }) {
    const composerStore = useComposerStore();
    const selectedArticleIds = ref([]);

    const handleColorChange = (index, newValue) => {
      const updatedField = {
        ...props.field,
        defaultValue: [...props.field.defaultValue],
      };
      updatedField.defaultValue[index] = newValue;
      emit('update:field', updatedField);
    };

    const handleFieldChange = (newValue) => {
      emit('update:field', { ...props.field, defaultValue: newValue });
    };

    const renderArticleOption = (option) => {
      return h('div', [
        h('div', { style: 'font-weight: bold;' }, option.category),
        h('div', option.title)
      ]);
    };

    const handleArticleIdsChange = (selectedIds) => {
      selectedArticleIds.value = selectedIds;
      const articles = selectedIds.map(id => {
        return composerStore.articleOptions.find(article => article.value === id);
      });
      emit('update:field', { ...props.field, defaultValue: articles });
    };

    const renderSelectedArticle = ({ option, handleClose }) => {
      return h(NTag, {
        closable: true,
        onClose: handleClose,
      }, {
        default: () => option.title
      });
    };

    return {
      composerStore,
      selectedArticleIds,
      handleColorChange,
      handleFieldChange,
      renderArticleOption,
      handleArticleIdsChange,
      renderSelectedArticle
    };
  }
})
</script>